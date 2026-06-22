<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EX-Change — Transaction Lookup</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="icon" href="{{ asset('images/logo/logo.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <a class="logotype" href="/"><img src="{{ asset('images/logo/logotype.svg') }}" alt="logo"></a>
        <div class="menu-toggle" id="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav class="main-nav" id="main-nav">
            <ul class="nav__links">
                <li><a href="/exchange">Exchange</a></li>
                <li><a href="/transaction" class="active">Transaction</a></li>
                <li><a href="/faq">FAQ</a></li>
                <li><a href="/agreement">Agreement</a></li>
                <li><a href="/contacts">Contacts</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="transaction-wrapper">

            {{-- Search Card --}}
            <div class="search-card">
                <h1>BSC Transaction Lookup</h1>
                <form class="search-form" action="/transaction/lookup" method="GET">
                    <div class="form">
                        <input
                            class="input"
                            type="text"
                            name="hash"
                            placeholder="Enter transaction hash (0x...)"
                            value="{{ $hash ?? old('hash') }}"
                            autocomplete="off"
                            spellcheck="false"
                        >
                        <span class="input-border"></span>
                    </div>
                    <button type="submit" class="search-btn">Search</button>
                </form>

                @if ($errors->has('hash'))
                    <div class="alert alert-error" style="margin-top:16px;">
                        {{ $errors->first('hash') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error" style="margin-top:16px;">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            @if (isset($data))

                {{-- Transaction Overview --}}
                <div class="result-card">
                    <h2>Transaction Details</h2>

                    <div class="tx-row">
                        <span class="tx-label">Transaction Hash</span>
                        <span class="tx-value">
                            <a href="https://bscscan.com/tx/{{ $data['hash'] }}" target="_blank" rel="noopener">
                                {{ $data['hash'] }}
                            </a>
                        </span>
                    </div>

                    <div class="tx-row">
                        <span class="tx-label">Status</span>
                        <span class="tx-value">
                            @php
                                $statusClass = match($data['status']) {
                                    'Success' => 'badge-success',
                                    'Failed'  => 'badge-failed',
                                    default   => 'badge-pending',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $data['status'] }}</span>
                        </span>
                    </div>

                    @if ($data['blockNumber'])
                    <div class="tx-row">
                        <span class="tx-label">Block</span>
                        <span class="tx-value">
                            <a href="https://bscscan.com/block/{{ $data['blockNumber'] }}" target="_blank" rel="noopener">
                                {{ number_format($data['blockNumber']) }}
                            </a>
                        </span>
                    </div>
                    @endif

                    <div class="tx-row">
                        <span class="tx-label">From</span>
                        <span class="tx-value">
                            <a href="https://bscscan.com/address/{{ $data['from'] }}" target="_blank" rel="noopener">
                                {{ $data['from'] }}
                            </a>
                        </span>
                    </div>

                    <div class="tx-row">
                        <span class="tx-label">To</span>
                        <span class="tx-value">
                            <a href="https://bscscan.com/address/{{ $data['to'] }}" target="_blank" rel="noopener">
                                {{ $data['to'] }}
                            </a>
                        </span>
                    </div>

                    <div class="tx-row">
                        <span class="tx-label">Value</span>
                        <span class="tx-value">{{ $data['bnbValue'] }} BNB</span>
                    </div>

                    <div class="tx-row">
                        <span class="tx-label">Gas Price</span>
                        <span class="tx-value">{{ $data['gasPriceGwei'] }} Gwei</span>
                    </div>

                    <div class="tx-row">
                        <span class="tx-label">Gas Limit</span>
                        <span class="tx-value">{{ number_format($data['gasLimit']) }}</span>
                    </div>

                    @if ($data['gasUsed'])
                    <div class="tx-row">
                        <span class="tx-label">Gas Used</span>
                        <span class="tx-value">
                            {{ number_format($data['gasUsed']) }}
                            ({{ $data['gasLimit'] > 0 ? round($data['gasUsed'] / $data['gasLimit'] * 100, 2) : 0 }}%)
                        </span>
                    </div>
                    @endif

                    <a class="bscscan-link" href="https://bscscan.com/tx/{{ $data['hash'] }}" target="_blank" rel="noopener">
                        View full details on BscScan &rarr;
                    </a>
                </div>

                {{-- Token Transfers --}}
                @if (!empty($data['tokenTransfers']))
                <div class="result-card">
                    <h2>Token Transfers ({{ count($data['tokenTransfers']) }})</h2>

                    @foreach ($data['tokenTransfers'] as $transfer)
                        @php
                            $decimals = (int) ($transfer['tokenDecimal'] ?? 18);
                            $raw      = $transfer['value'] ?? '0';
                            $amount   = $decimals > 0
                                ? bcdiv($raw, bcpow('10', (string) $decimals), $decimals > 6 ? 6 : $decimals)
                                : $raw;
                            $symbol   = $transfer['tokenSymbol'] ?? 'TOKEN';
                            $name     = $transfer['tokenName'] ?? '';
                        @endphp
                        <div class="transfer-item">
                            <div class="transfer-amount">
                                {{ number_format((float) $amount, 2) }} {{ $symbol }}
                                @if ($name)
                                    <span style="font-size:0.75rem; font-weight:400; color:#8ea8c3;">({{ $name }})</span>
                                @endif
                            </div>
                            <div class="transfer-meta">
                                <span><b>From:</b> {{ $transfer['from'] }}</span>
                                <span><b>To:</b> {{ $transfer['to'] }}</span>
                                @if (!empty($transfer['contractAddress']))
                                    <span>
                                        <b>Contract:</b>
                                        <a href="https://bscscan.com/token/{{ $transfer['contractAddress'] }}" target="_blank" rel="noopener" style="color:#4da6ff;">
                                            {{ $transfer['contractAddress'] }}
                                        </a>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

            @endif

        </div>
    </main>

    <footer>
        <div class="footer-links">
            <div class="footer-about">
                <h4>About</h4>
                <ul>
                    <li><a href="/agreement">Agreement</a></li>
                    <li><a href="/faq">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-services">
                <h4>Our Services</h4>
                <ul>
                    <li><a href="/exchange">Currency Exchange</a></li>
                    <li><a href="/transaction">Transaction Lookup</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contacts</h4>
                <ul>
                    <li><a href="mailto:support@ex-change.com">support@ex-change.com</a></li>
                    <li><a href="https://t.me/your_telegram" target="_blank">
                            <img src="{{ asset('images/icons/tg_logo.svg') }}" alt="Telegram"></a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-sponsors">
            <div class="sponsors">
                <img src="{{ asset('images/sponsors/bestchange.svg') }}" alt="Sponsor 1" class="sponsor-img">
                <img src="{{ asset('images/sponsors/bitsmedia.svg') }}" alt="Sponsor 2" class="sponsor-img">
                <img src="{{ asset('images/sponsors/emon.svg') }}" alt="Sponsor 3" class="sponsor-img">
            </div>
        </div>

        <div class="footer-copyright">
            <p>© 2024 All copyrights reserved</p>
        </div>
    </footer>

    <script src="{{ asset('js/menu.js') }}"></script>
</body>

</html>
