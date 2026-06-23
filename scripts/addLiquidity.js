const { ethers } = require("hardhat");
require("dotenv").config();

// ── Addresses ─────────────────────────────────────────────────────────────────
const PANCAKE_ROUTER  = "0x10ED43C718714eb63d5aA57B78B54704E256024E";
const PANCAKE_FACTORY = "0xcA143Ce32Fe78f1f7019d7d551a6402fC5350c73";
const INIT_HASH       = "0x00fb7f630766e6a796048ea87d01acd3068e8ff67d078148a3fa3f4a84f69bd5";
const USDT_ADDR       = "0x55d398326f99059fF775485246999027B3197955";

// ── Liquidity config ──────────────────────────────────────────────────────────
const TARGET_PRICE  = 1.00;    // USD per token — change to set pool price
const USDT_AMOUNT   = 250_000; // USDT side
const TOKEN_AMOUNT  = Math.round(USDT_AMOUNT / TARGET_PRICE); // auto-calculated
const SLIPPAGE_BPS  = 50n;     // 0.5 %
const DEPLOY_SUPPLY = Math.ceil(TOKEN_AMOUNT * 1.05); // 5 % buffer above liquidity

// ── ABIs ─────────────────────────────────────────────────────────────────────
const ROUTER_ABI = [
    "function addLiquidity(address,address,uint,uint,uint,uint,address,uint) returns (uint,uint,uint)",
];
const ERC20_ABI = [
    "function approve(address,uint256) returns (bool)",
    "function balanceOf(address) view returns (uint256)",
    "function allowance(address,address) view returns (uint256)",
];
const PAIR_ABI = [
    "function getReserves() view returns (uint112,uint112,uint32)",
    "function token0() view returns (address)",
    "function totalSupply() view returns (uint256)",
];

// ── Helpers ───────────────────────────────────────────────────────────────────
function computePairAddress(tokenA, tokenB) {
    const [t0, t1] = tokenA.toLowerCase() < tokenB.toLowerCase()
        ? [tokenA, tokenB]
        : [tokenB, tokenA];
    const salt = ethers.solidityPackedKeccak256(["address", "address"], [t0, t1]);
    return ethers.getCreate2Address(PANCAKE_FACTORY, salt, INIT_HASH);
}

async function readMarketPrice(pairAddr, tokenAddr) {
    const pair    = new ethers.Contract(pairAddr, PAIR_ABI, ethers.provider);
    const [r0, r1] = await pair.getReserves();
    const token0  = await pair.token0();
    const [tokR, usdR] = token0.toLowerCase() === tokenAddr.toLowerCase()
        ? [r0, r1] : [r1, r0];
    const price    = Number(ethers.formatUnits(usdR, 18)) / Number(ethers.formatUnits(tokR, 18));
    const totalLiq = Number(ethers.formatUnits(usdR, 18)) * 2;
    const lpTotal  = await pair.totalSupply();
    return { price, tokR, usdR, totalLiq, lpTotal };
}

function slipMin(amount) {
    return amount - (amount * SLIPPAGE_BPS) / 10_000n;
}

// ── Main ──────────────────────────────────────────────────────────────────────
async function main() {
    const [deployer] = await ethers.getSigners();
    const router = new ethers.Contract(PANCAKE_ROUTER, ROUTER_ABI, deployer);
    const usdt   = new ethers.Contract(USDT_ADDR,      ERC20_ABI,  deployer);

    const usdtAmt  = ethers.parseUnits(USDT_AMOUNT.toString(),  18);
    const tokenAmt = ethers.parseUnits(TOKEN_AMOUNT.toString(), 18);

    console.log("=".repeat(64));
    console.log("Wallet       :", deployer.address);
    console.log("Router       :", PANCAKE_ROUTER);
    console.log("Target Price : $" + TARGET_PRICE.toFixed(4) + " per token");
    console.log("Liquidity    :", TOKEN_AMOUNT.toLocaleString(), "token +", USDT_AMOUNT.toLocaleString(), "USDT");
    console.log("=".repeat(64));

    // ── [1/6] Deploy token ────────────────────────────────────────────────────
    console.log("\n[1/6] Deploying token (supply:", DEPLOY_SUPPLY.toLocaleString(), ")...");
    const Token   = await ethers.getContractFactory("TetherToken");
    const token   = await Token.deploy(DEPLOY_SUPPLY);
    await token.waitForDeployment();
    const tokenAddr = await token.getAddress();
    console.log("  Token    :", tokenAddr);

    // ── [2/6] Compute pair address ────────────────────────────────────────────
    console.log("\n[2/6] Computing PancakeSwap pair address...");
    const pairAddr = computePairAddress(tokenAddr, USDT_ADDR);
    console.log("  Pair     :", pairAddr);

    // ── [3/6] Whitelist router + pair ────────────────────────────────────────
    console.log("\n[3/6] Whitelisting router and pair contract...");
    const wTx1 = await token.allowAddress(PANCAKE_ROUTER);
    await wTx1.wait();
    console.log("  Router whitelisted ✓");
    const wTx2 = await token.allowAddress(pairAddr);
    await wTx2.wait();
    console.log("  Pair   whitelisted ✓");

    // ── [4/6] Check balances ──────────────────────────────────────────────────
    console.log("\n[4/6] Checking balances...");
    const usdtBal  = await usdt.balanceOf(deployer.address);
    const tokenBal = await token.balanceOf(deployer.address);
    console.log("  USDT  have:", Number(ethers.formatUnits(usdtBal,  18)).toLocaleString());
    console.log("  USDT  need:", USDT_AMOUNT.toLocaleString());
    console.log("  Token have:", Number(ethers.formatUnits(tokenBal, 18)).toLocaleString());
    console.log("  Token need:", TOKEN_AMOUNT.toLocaleString());

    if (usdtBal < usdtAmt) {
        console.error("\n  ERROR: Insufficient USDT. Need", USDT_AMOUNT.toLocaleString(), "— have", Number(ethers.formatUnits(usdtBal, 18)).toLocaleString());
        process.exit(1);
    }

    // ── [5/6] Approve router ──────────────────────────────────────────────────
    console.log("\n[5/6] Approving PancakeSwap router...");
    if ((await usdt.allowance(deployer.address, PANCAKE_ROUTER)) < usdtAmt) {
        const t = await usdt.approve(PANCAKE_ROUTER, usdtAmt);
        await t.wait();
        console.log("  USDT  approved :", t.hash);
    } else { console.log("  USDT  already approved"); }

    if ((await token.allowance(deployer.address, PANCAKE_ROUTER)) < tokenAmt) {
        const t = await token.approve(PANCAKE_ROUTER, tokenAmt);
        await t.wait();
        console.log("  Token approved :", t.hash);
    } else { console.log("  Token already approved"); }

    // ── [6/6] Add liquidity ───────────────────────────────────────────────────
    console.log("\n[6/6] Adding liquidity...");
    const deadline = Math.floor(Date.now() / 1000) + 1200;
    const txLiq    = await router.addLiquidity(
        tokenAddr, USDT_ADDR,
        tokenAmt,  usdtAmt,
        slipMin(tokenAmt), slipMin(usdtAmt),
        deployer.address,
        deadline
    );
    console.log("  TX sent   :", txLiq.hash);
    const receipt = await txLiq.wait();
    console.log("  Confirmed : block", receipt.blockNumber);

    // ── Market price verification ─────────────────────────────────────────────
    console.log("\n── Market Price ─────────────────────────────────────────────");
    const { price, tokR, usdR, totalLiq, lpTotal } = await readMarketPrice(pairAddr, tokenAddr);
    console.log("  Price       : $" + price.toFixed(6) + " USDT");
    console.log("  Token Rsrv  :", Number(ethers.formatUnits(tokR, 18)).toLocaleString());
    console.log("  USDT  Rsrv  :", Number(ethers.formatUnits(usdR, 18)).toLocaleString());
    console.log("  Total Liq   : ~$" + totalLiq.toLocaleString(undefined, { maximumFractionDigits: 2 }));
    console.log("  LP Tokens   :", ethers.formatUnits(lpTotal, 18));

    if (Math.abs(price - TARGET_PRICE) / TARGET_PRICE > 0.01) {
        console.warn("  ⚠  Price drifted >1% from target — re-check ratios");
    } else {
        console.log("  ✓  Price within 1% of target $" + TARGET_PRICE.toFixed(4));
    }

    console.log("\n" + "=".repeat(64));
    console.log("LIQUIDITY ADDED");
    console.log("  Token   :", tokenAddr);
    console.log("  Pair    :", pairAddr);
    console.log("  Price   : $" + price.toFixed(6));
    console.log("  Info    : https://pancakeswap.finance/info/v2/pairs/" + pairAddr.toLowerCase());
    console.log("=".repeat(64));
}

main().catch(e => { console.error(e); process.exitCode = 1; });
