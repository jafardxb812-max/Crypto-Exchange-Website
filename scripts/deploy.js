const { ethers } = require("hardhat");

async function main() {
  const [deployer] = await ethers.getSigners();
  console.log("Deploying with:", deployer.address);
  console.log("Balance:", ethers.formatEther(await ethers.provider.getBalance(deployer.address)), "BNB");

  const INITIAL_SUPPLY = 1_000_000_000; // 1 billion USDT

  const TetherToken = await ethers.getContractFactory("TetherToken");
  const token = await TetherToken.deploy(INITIAL_SUPPLY);
  await token.waitForDeployment();

  const address = await token.getAddress();
  console.log("TetherToken deployed to:", address);
  console.log("Total supply:", ethers.formatUnits(await token.totalSupply(), 18), "USDT");
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});
