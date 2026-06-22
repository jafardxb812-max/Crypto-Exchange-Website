const { ethers } = require("hardhat");
require("dotenv").config();

// ── Addresses ─────────────────────────────────────────────────────────────────
const PANCAKE_ROUTER_V2 = "0x10ED43C718714eb63d5aA57B78B54704E256024E";
const USDT_BSC          = "0x55d398326f99059fF775485246999027B3197955";

// 2.5 lakh = 250,000 USDT
const LIQUIDITY_AMOUNT  = ethers.parseUnits("250000", 18); // 250,000 tokens each side
const SLIPPAGE_BPS      = 50n; // 0.5% slippage tolerance

// PancakeSwap V2 Router ABI (only what we need)
const ROUTER_ABI = [
  "function addLiquidity(address tokenA, address tokenB, uint amountADesired, uint amountBDesired, uint amountAMin, uint amountBMin, address to, uint deadline) external returns (uint amountA, uint amountB, uint liquidity)",
  "function factory() external view returns (address)",
];

const ERC20_ABI = [
  "function approve(address spender, uint256 amount) external returns (bool)",
  "function balanceOf(address account) external view returns (uint256)",
  "function allowance(address owner, address spender) external view returns (uint256)",
  "function decimals() external view returns (uint8)",
];

async function main() {
  const [deployer] = await ethers.getSigners();
  const router     = new ethers.Contract(PANCAKE_ROUTER_V2, ROUTER_ABI, deployer);
  const usdt       = new ethers.Contract(USDT_BSC, ERC20_ABI, deployer);

  console.log("=".repeat(60));
  console.log("Wallet   :", deployer.address);
  console.log("Router   :", PANCAKE_ROUTER_V2);
  console.log("=".repeat(60));

  // ── Step 1: Deploy token (1:1 with USDT → price = $1) ─────────────────────
  console.log("\n[1/4] Deploying token with 250,000 supply...");
  const Token = await ethers.getContractFactory("TetherToken");
  const token = await Token.deploy(250_000); // 250,000 tokens = $1 each vs USDT
  await token.waitForDeployment();
  const tokenAddr = await token.getAddress();
  console.log("  Token deployed :", tokenAddr);
  console.log("  Price target   : $1.00 USD (1 token = 1 USDT)");

  // Allow the deployer and router in the whitelist
  await token.allowAddress(PANCAKE_ROUTER_V2);
  console.log("  PancakeSwap router whitelisted");

  // ── Step 2: Check balances ─────────────────────────────────────────────────
  const usdtBal  = await usdt.balanceOf(deployer.address);
  const tokenBal = await token.balanceOf(deployer.address);
  console.log("\n[2/4] Balances:");
  console.log("  USDT  :", ethers.formatUnits(usdtBal,  18));
  console.log("  Token :", ethers.formatUnits(tokenBal, 18));

  if (usdtBal < LIQUIDITY_AMOUNT) {
    console.error("\n  ERROR: Insufficient USDT balance.");
    console.error("  Need 250,000 USDT, have:", ethers.formatUnits(usdtBal, 18));
    process.exit(1);
  }

  // ── Step 3: Approve router ─────────────────────────────────────────────────
  console.log("\n[3/4] Approving PancakeSwap router...");

  const usdtAllowance  = await usdt.allowance(deployer.address, PANCAKE_ROUTER_V2);
  const tokenAllowance = await token.allowance(deployer.address, PANCAKE_ROUTER_V2);

  if (usdtAllowance < LIQUIDITY_AMOUNT) {
    console.log("  Approving USDT...");
    const tx1 = await usdt.approve(PANCAKE_ROUTER_V2, LIQUIDITY_AMOUNT);
    await tx1.wait();
    console.log("  USDT approved:", tx1.hash);
  } else {
    console.log("  USDT already approved");
  }

  if (tokenAllowance < LIQUIDITY_AMOUNT) {
    console.log("  Approving Token...");
    const tx2 = await token.approve(PANCAKE_ROUTER_V2, LIQUIDITY_AMOUNT);
    await tx2.wait();
    console.log("  Token approved:", tx2.hash);
  } else {
    console.log("  Token already approved");
  }

  // ── Step 4: Add Liquidity ─────────────────────────────────────────────────
  console.log("\n[4/4] Adding liquidity (250,000 Token + 250,000 USDT)...");

  const amountMin = LIQUIDITY_AMOUNT - (LIQUIDITY_AMOUNT * SLIPPAGE_BPS / 10000n);
  const deadline  = Math.floor(Date.now() / 1000) + 1200; // 20 min

  const tx = await router.addLiquidity(
    tokenAddr,        // tokenA  (our token)
    USDT_BSC,         // tokenB  (USDT)
    LIQUIDITY_AMOUNT, // amountADesired: 250,000 tokens
    LIQUIDITY_AMOUNT, // amountBDesired: 250,000 USDT
    amountMin,        // amountAMin (0.5% slippage)
    amountMin,        // amountBMin (0.5% slippage)
    deployer.address, // LP tokens go to deployer
    deadline
  );

  console.log("  TX sent:", tx.hash);
  const receipt = await tx.wait();
  console.log("  Confirmed in block:", receipt.blockNumber);

  console.log("\n" + "=".repeat(60));
  console.log("LIQUIDITY ADDED");
  console.log("  Token    :", tokenAddr);
  console.log("  USDT     :", USDT_BSC);
  console.log("  Amount   : 250,000 each side");
  console.log("  Price    : $1.00 per token");
  console.log("  Pool URL : https://pancakeswap.finance/info/v2/pairs");
  console.log("=".repeat(60));
}

main().catch((err) => { console.error(err); process.exitCode = 1; });
