const { expect }  = require("chai");
const { ethers }  = require("hardhat");

describe("TetherToken", function () {
  let token, owner, alice, bob;
  const SUPPLY = 1_000_000n;
  const UNITS  = 10n ** 18n;

  beforeEach(async () => {
    [owner, alice, bob] = await ethers.getSigners();
    const Factory = await ethers.getContractFactory("TetherToken");
    token = await Factory.deploy(SUPPLY);
  });

  // ── Deployment ──────────────────────────────────────────────────────────────

  it("sets name, symbol, decimals", async () => {
    expect(await token.name()).to.equal("Tether USD");
    expect(await token.symbol()).to.equal("USDT");
    expect(await token.decimals()).to.equal(18);
  });

  it("mints initial supply to deployer", async () => {
    expect(await token.totalSupply()).to.equal(SUPPLY * UNITS);
    expect(await token.balanceOf(owner.address)).to.equal(SUPPLY * UNITS);
  });

  // ── Whitelist ────────────────────────────────────────────────────────────────

  it("owner is allowed by default", async () => {
    expect(await token.isAllowed(owner.address)).to.be.true;
  });

  it("transfer to non-allowed address reverts", async () => {
    await expect(token.transfer(alice.address, 100n * UNITS))
      .to.be.revertedWith("TetherToken: recipient address not allowed");
  });

  it("owner can allow an address and then transfer succeeds", async () => {
    await token.allowAddress(alice.address);
    await expect(token.transfer(alice.address, 100n * UNITS)).to.not.be.reverted;
    expect(await token.balanceOf(alice.address)).to.equal(100n * UNITS);
  });

  it("non-allowed sender cannot transfer even if recipient is allowed", async () => {
    await token.allowAddress(alice.address);
    await token.allowAddress(bob.address);
    await token.transfer(alice.address, 100n * UNITS);

    // Remove alice from allowlist
    await token.disallowAddress(alice.address);

    await expect(token.connect(alice).transfer(bob.address, 50n * UNITS))
      .to.be.revertedWith("TetherToken: sender address not allowed");
  });

  it("mint auto-allows recipient", async () => {
    await token.mint(alice.address, 100n);
    expect(await token.isAllowed(alice.address)).to.be.true;
    expect(await token.balanceOf(alice.address)).to.equal(100n * UNITS);
  });

  // ── Transfers ───────────────────────────────────────────────────────────────

  it("transfers tokens between allowed accounts", async () => {
    await token.allowAddress(alice.address);
    await token.transfer(alice.address, 100n * UNITS);
    expect(await token.balanceOf(alice.address)).to.equal(100n * UNITS);
  });

  // ── Pause ────────────────────────────────────────────────────────────────────

  it("owner can pause and unpause", async () => {
    await token.allowAddress(alice.address);
    await token.pause();
    expect(await token.paused()).to.be.true;

    await expect(token.transfer(alice.address, 1n * UNITS))
      .to.be.revertedWith("TetherToken: token transfer is paused");

    await token.unpause();
    await expect(token.transfer(alice.address, 1n * UNITS)).to.not.be.reverted;
  });

  it("non-owner cannot pause", async () => {
    await expect(token.connect(alice).pause())
      .to.be.revertedWith("Ownable: caller is not the owner");
  });

  // ── Blacklist ────────────────────────────────────────────────────────────────

  it("owner can blacklist an address", async () => {
    await token.allowAddress(alice.address);
    await token.blacklist(alice.address);
    expect(await token.isBlacklisted(alice.address)).to.be.true;

    await expect(token.transfer(alice.address, 1n * UNITS))
      .to.be.revertedWith("TetherToken: account is blacklisted");
  });

  it("blacklisted address cannot send", async () => {
    await token.allowAddress(alice.address);
    await token.allowAddress(bob.address);
    await token.transfer(alice.address, 100n * UNITS);
    await token.blacklist(alice.address);

    await expect(token.connect(alice).transfer(bob.address, 1n * UNITS))
      .to.be.revertedWith("TetherToken: account is blacklisted");
  });

  it("owner can un-blacklist", async () => {
    await token.allowAddress(alice.address);
    await token.blacklist(alice.address);
    await token.unBlacklist(alice.address);
    await expect(token.transfer(alice.address, 1n * UNITS)).to.not.be.reverted;
  });

  // ── Mint / Burn ──────────────────────────────────────────────────────────────

  it("owner can mint tokens", async () => {
    await token.mint(alice.address, 500n);
    expect(await token.balanceOf(alice.address)).to.equal(500n * UNITS);
  });

  it("holder can burn own tokens", async () => {
    await token.allowAddress(alice.address);
    await token.transfer(alice.address, 200n * UNITS);
    await token.connect(alice).burn(100n);
    expect(await token.balanceOf(alice.address)).to.equal(100n * UNITS);
  });

  it("owner can burnFrom any address", async () => {
    await token.allowAddress(alice.address);
    await token.transfer(alice.address, 200n * UNITS);
    await token.burnFrom(alice.address, 200n);
    expect(await token.balanceOf(alice.address)).to.equal(0n);
  });

  // ── Ownership ────────────────────────────────────────────────────────────────

  it("transfers ownership", async () => {
    await token.transferOwnership(alice.address);
    expect(await token.owner()).to.equal(alice.address);
  });

  it("new owner can mint, old owner cannot", async () => {
    await token.allowAddress(alice.address);
    await token.transferOwnership(alice.address);
    await expect(token.mint(bob.address, 1n)).to.be.revertedWith("Ownable: caller is not the owner");
    await expect(token.connect(alice).mint(bob.address, 1n)).to.not.be.reverted;
  });
});
