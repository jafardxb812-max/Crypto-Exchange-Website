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
                <li><a href="/tracker">Tracker</a></li>
                <li><a href="/faq">FAQ</a></li>
                <li><a href="/agreement">Agreement</a></li>
                <li><a href="/contacts">Contacts</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="transaction-wrapper">

            {{-- Search --}}
            <div class="search-card">
                <h1>BSC Transaction Lookup</h1>
                <p class="search-sub">Search by transaction hash on BNB Smart Chain</p>
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
                    <button type="submit" class="search-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Search
                    </button>
                </form>
                @if ($errors->has('hash'))
                    <div class="alert alert-error">{{ $errors->first('hash') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif
            </div>

            @if (isset($data))

            @php
                $shortHash = fn($h) => substr($h, 0, 18) . '...' . substr($h, -6);
            @endphp

            {{-- Status banner --}}
            <div class="status-banner {{ $data['status'] === 'Success' ? 'banner-success' : ($data['status'] === 'Failed' ? 'banner-failed' : 'banner-pending') }}">
                @if ($data['status'] === 'Success')
                    <svg class="banner-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                @elseif ($data['status'] === 'Failed')
                    <svg class="banner-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                @else
                    <svg class="banner-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                @endif
                <div>
                    <div class="banner-title">Transaction {{ $data['status'] }}</div>
                    <div class="banner-hash">{{ $data['hash'] }}</div>
                </div>
            </div>

            {{-- Overview --}}
            <div class="result-card">
                <div class="card-header">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Transaction Overview
                </div>

                <div class="tx-row">
                    <span class="tx-label">Transaction Hash</span>
                    <span class="tx-value hash-cell">
                        <span class="hash-full">{{ $data['hash'] }}</span>
                        <button class="copy-tiny" onclick="copyText('{{ $data['hash'] }}', this)" title="Copy">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                    </span>
                </div>

                <div class="tx-row">
                    <span class="tx-label">Status</span>
                    <span class="tx-value">
                        <span class="badge {{ $data['status'] === 'Success' ? 'badge-success' : ($data['status'] === 'Failed' ? 'badge-failed' : 'badge-pending') }}">
                            {{ $data['status'] }}
                        </span>
                        @if ($data['confirmations'] !== null)
                            <span class="confirm-pill">{{ number_format($data['confirmations']) }} Block Confirmations</span>
                        @endif
                    </span>
                </div>

                @if ($data['timestamp'])
                <div class="tx-row">
                    <span class="tx-label">Timestamp</span>
                    <span class="tx-value">
                        @php
                            $dt = new \DateTime('@' . $data['timestamp']);
                            $dt->setTimezone(new \DateTimeZone('UTC'));
                        @endphp
                        <svg class="inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ $dt->format('d M Y, H:i:s') }} UTC
                        @if ($data['timeAgo'])<span class="ts-ago">({{ $data['timeAgo'] }})</span>@endif
                    </span>
                </div>
                @endif

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
                    <span class="tx-value hash-cell">
                        <a href="https://bscscan.com/address/{{ $data['from'] }}" target="_blank" rel="noopener">{{ $data['from'] }}</a>
                        <button class="copy-tiny" onclick="copyText('{{ $data['from'] }}', this)" title="Copy">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                    </span>
                </div>

                <div class="tx-row">
                    <span class="tx-label">To</span>
                    <span class="tx-value hash-cell">
                        <a href="https://bscscan.com/address/{{ $data['to'] }}" target="_blank" rel="noopener">{{ $data['to'] }}</a>
                        <button class="copy-tiny" onclick="copyText('{{ $data['to'] }}', this)" title="Copy">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                    </span>
                </div>

                <div class="tx-row">
                    <span class="tx-label">Value</span>
                    <span class="tx-value value-pill">{{ $data['bnbValue'] }} BNB</span>
                </div>

                @if ($data['txFee'] !== null)
                <div class="tx-row">
                    <span class="tx-label">Transaction Fee</span>
                    <span class="tx-value">{{ $data['txFee'] }} BNB</span>
                </div>
                @endif
            </div>

            {{-- Gas & Details --}}
            <div class="result-card">
                <div class="card-header">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                    Gas &amp; Details
                </div>

                <div class="tx-row">
                    <span class="tx-label">Gas Price</span>
                    <span class="tx-value">{{ $data['gasPriceGwei'] }} Gwei
                        <span class="muted">({{ bcdiv(bcmul($data['gasPriceGwei'], '1000000000'), bcpow('10', '18'), 18) }} BNB)</span>
                    </span>
                </div>

                <div class="tx-row">
                    <span class="tx-label">Gas Limit &amp; Usage</span>
                    <span class="tx-value">
                        {{ number_format($data['gasLimit']) }}
                        @if ($data['gasUsed'])
                            &nbsp;|&nbsp; {{ number_format($data['gasUsed']) }}
                            @php $pct = $data['gasLimit'] > 0 ? round($data['gasUsed'] / $data['gasLimit'] * 100, 2) : 0; @endphp
                            <span class="gas-bar-wrap">
                                <span class="gas-bar" style="width:{{ min($pct, 100) }}%"></span>
                            </span>
                            <span class="muted">{{ $pct }}%</span>
                        @endif
                    </span>
                </div>

                <div class="tx-row">
                    <span class="tx-label">Nonce</span>
                    <span class="tx-value">{{ $data['nonce'] }}</span>
                </div>

                <div class="tx-row">
                    <span class="tx-label">Network</span>
                    <span class="tx-value">
                        <span class="network-tag">BNB Smart Chain (BSC)</span>
                        <span class="muted">Chain ID: 56</span>
                    </span>
                </div>

                @if ($data['inputData'] && $data['inputData'] !== '0x')
                <div class="tx-row">
                    <span class="tx-label">Input Data</span>
                    <span class="tx-value">
                        <span class="method-tag">
                            {{ strlen($data['inputData']) >= 10 ? 'Method: 0x' . substr($data['inputData'], 2, 8) : 'Data' }}
                        </span>
                        <div class="input-data-box">{{ $data['inputData'] }}</div>
                    </span>
                </div>
                @else
                <div class="tx-row">
                    <span class="tx-label">Input Data</span>
                    <span class="tx-value muted">0x (BNB Transfer)</span>
                </div>
                @endif

                <a class="bscscan-link" href="https://bscscan.com/tx/{{ $data['hash'] }}" target="_blank" rel="noopener">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    View on BscScan
                </a>
            </div>

            {{-- Token Transfers --}}
            @if (!empty($data['tokenTransfers']))
            <div class="result-card">
                <div class="card-header">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    Token Transfers
                    <span class="count-badge">{{ count($data['tokenTransfers']) }}</span>
                </div>

                @foreach ($data['tokenTransfers'] as $t)
                    @php
                        $dec    = (int) ($t['tokenDecimal'] ?? 18);
                        $amt    = $dec > 0 ? bcdiv($t['value'] ?? '0', bcpow('10', (string)$dec), min($dec, 6)) : ($t['value'] ?? '0');
                        $sym    = $t['tokenSymbol'] ?? 'TOKEN';
                        $name   = $t['tokenName'] ?? '';
                        $ca     = $t['contractAddress'] ?? '';
                    @endphp
                    <div class="transfer-item">
                        <div class="transfer-top">
                            <div class="token-pill">
                                <div class="token-avatar">{{ strtoupper(substr($sym, 0, 2)) }}</div>
                                <div>
                                    <div class="token-amount">{{ number_format((float)$amt, 4) }} {{ $sym }}</div>
                                    @if ($name)<div class="token-name">{{ $name }}</div>@endif
                                </div>
                            </div>
                            @if ($ca)
                                <a class="ca-link" href="https://bscscan.com/token/{{ $ca }}" target="_blank" rel="noopener">Contract ↗</a>
                            @endif
                        </div>
                        <div class="transfer-flow">
                            <div class="flow-addr">
                                <span class="flow-tag">From</span>
                                <a href="https://bscscan.com/address/{{ $t['from'] }}" target="_blank" rel="noopener" class="flow-hash">{{ $t['from'] }}</a>
                            </div>
                            <div class="flow-arrow">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </div>
                            <div class="flow-addr">
                                <span class="flow-tag">To</span>
                                <a href="https://bscscan.com/address/{{ $t['to'] }}" target="_blank" rel="noopener" class="flow-hash">{{ $t['to'] }}</a>
                            </div>
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
                    <li><a href="/tracker">Location Tracker</a></li>
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
    <script>
        function copyText(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                btn.classList.add('copied');
                setTimeout(() => btn.classList.remove('copied'), 2000);
            });
        }
    </script>
</body>

</html>
