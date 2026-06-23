/**
 * marketPrice.js — Read live PancakeSwap market price and pool stats
 *
 * Usage:
 *   npx hardhat run scripts/marketPrice.js --network bsc
 *
 * Requires TOKEN_ADDRESS in .env
 */
const { ethers } = require("hardhat");
require("dotenv").config();

// ── Config ────────────────────────────────────────────────────────────────────
const TOKEN_ADDR      = process.env.TOKEN_ADDRESS;
const USDT_ADDR       = "0x55d398326f99059fF775485246999027B3197955";
const PANCAKE_ROUTER  = "0x10ED43C718714eb63d5aA57B78B54704E256024E";
const PANCAKE_FACTORY = "0xcA143Ce32Fe78f1f7019d7d551a6402fC5350c73";
const INIT_HASH       = "0x00fb7f630766e6a796048ea87d01acd3068e8ff67d078148a3fa3f4a84f69bd5";

// ── ABIs ─────────────────────────────────────────────────────────────────────
const PAIR_ABI = [
    "function getReserves() view returns (uint112 r0, uint112 r1, uint32 ts)",
    "function token0() view returns (address)",
    "function totalSupply() view returns (uint256)",
];
const ROUTER_ABI = [
    "function getAmountsOut(uint amountIn, address[] calldata path) view returns (uint[] memory amounts)",
];
const ERC20_ABI = [
    "function name() view returns (string)",
    "function symbol() view returns (string)",
    "function decimals() view returns (uint8)",
    "function totalSupply() view returns (uint256)",
    "function balanceOf(address) view returns (uint256)",
];

// ── Helpers ───────────────────────────────────────────────────────────────────
function computePairAddress(tokenA, tokenB) {
    const [t0, t1] = tokenA.toLowerCase() < tokenB.toLowerCase()
        ? [tokenA, tokenB]
        : [tokenB, tokenA];
    const salt = ethers.solidityPackedKeccak256(["address", "address"], [t0, t1]);
    return ethers.getCreate2Address(PANCAKE_FACTORY, salt, INIT_HASH);
}

function fmt(n, dec = 2) {
    return Number(n).toLocaleString(undefined, { maximumFractionDigits: dec });
}

// ── Main ──────────────────────────────────────────────────────────────────────
async function main() {
    if (!TOKEN_ADDR) {
        console.error("ERROR: Set TOKEN_ADDRESS=0x... in your .env file");
        process.exit(1);
    }

    const [signer]  = await ethers.getSigners();
    const token     = new ethers.Contract(TOKEN_ADDR, ERC20_ABI, ethers.provider);
    const router    = new ethers.Contract(PANCAKE_ROUTER, ROUTER_ABI, ethers.provider);
    const pairAddr  = computePairAddress(TOKEN_ADDR, USDT_ADDR);
    const pair      = new ethers.Contract(pairAddr, PAIR_ABI, ethers.provider);

    // ── Token info ────────────────────────────────────────────────────────────
    const [name, symbol, decimals, totalSupply] = await Promise.all([
        token.name(),
        token.symbol(),
        token.decimals(),
        token.totalSupply(),
    ]);

    // ── Pool data ─────────────────────────────────────────────────────────────
    const [r0, r1]  = await pair.getReserves();
    const token0    = await pair.token0();
    const lpSupply  = await pair.totalSupply();
    const isToken0  = token0.toLowerCase() === TOKEN_ADDR.toLowerCase();
    const [tokR, usdR] = isToken0 ? [r0, r1] : [r1, r0];

    const tokResNum  = Number(ethers.formatUnits(tokR, decimals));
    const usdResNum  = Number(ethers.formatUnits(usdR, 18));
    const spotPrice  = usdResNum / tokResNum;
    const totalLiq   = usdResNum * 2;
    const mktCap     = spotPrice * Number(ethers.formatUnits(totalSupply, decimals));

    // ── Swap quotes ───────────────────────────────────────────────────────────
    let buyQuote  = null;
    let sellQuote = null;
    try {
        const oneToken = ethers.parseUnits("1", decimals);
        const oneUsdt  = ethers.parseUnits("1", 18);
        const sellOut  = await router.getAmountsOut(oneToken, [TOKEN_ADDR, USDT_ADDR]);
        const buyOut   = await router.getAmountsOut(oneUsdt,  [USDT_ADDR, TOKEN_ADDR]);
        sellQuote = Number(ethers.formatUnits(sellOut[1], 18));
        buyQuote  = Number(ethers.formatUnits(buyOut[1],  decimals));
    } catch (_) { /* pair may be new */ }

    // ── Wallet balance ────────────────────────────────────────────────────────
    const walletBal = await token.balanceOf(signer.address);

    // ── Output ────────────────────────────────────────────────────────────────
    console.log("\n" + "═".repeat(62));
    console.log(`  ${name} (${symbol})`);
    console.log("  Token    : " + TOKEN_ADDR);
    console.log("  Pair     : " + pairAddr);
    console.log("═".repeat(62));

    console.log("\n── Market Price ─────────────────────────────────────────────");
    console.log("  Spot Price   : $" + spotPrice.toFixed(6) + " USDT");
    if (sellQuote) console.log("  Sell Quote   : $" + sellQuote.toFixed(6) + " (1 token → USDT, incl. 0.25% fee)");
    if (buyQuote)  console.log("  Buy  Quote   : " + buyQuote.toFixed(6) + " tokens per 1 USDT");

    console.log("\n── Pool Reserves ────────────────────────────────────────────");
    console.log("  " + symbol + " Reserve : " + fmt(tokResNum));
    console.log("  USDT Reserve  : " + fmt(usdResNum));
    console.log("  Total Liq     : ~$" + fmt(totalLiq));
    console.log("  LP Tokens     : " + fmt(Number(ethers.formatUnits(lpSupply, 18))));

    console.log("\n── Token Stats ──────────────────────────────────────────────");
    console.log("  Total Supply  : " + fmt(Number(ethers.formatUnits(totalSupply, decimals))));
    console.log("  Market Cap    : ~$" + fmt(mktCap));
    console.log("  Your Balance  : " + fmt(Number(ethers.formatUnits(walletBal, decimals))) + " " + symbol);

    console.log("\n── Links ────────────────────────────────────────────────────");
    console.log("  PancakeSwap   : https://pancakeswap.finance/info/v2/pairs/" + pairAddr.toLowerCase());
    console.log("  Chart         : https://dexscreener.com/bsc/" + pairAddr.toLowerCase());
    console.log("  BscScan       : https://bscscan.com/token/" + TOKEN_ADDR);
    console.log("═".repeat(62) + "\n");
}

main().catch(e => { console.error(e); process.exitCode = 1; });
