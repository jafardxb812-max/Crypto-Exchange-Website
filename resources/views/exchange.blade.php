<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EX-Change — Currency Exchange</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
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
                <li><a href="/exchange" class="active">Exchange</a></li>
                <li><a href="/transaction">Transaction</a></li>
                <li><a href="/tracker">Tracker</a></li>
                <li><a href="/faq">FAQ</a></li>
                <li><a href="/agreement">Agreement</a></li>
                <li><a href="/contacts">Contacts</a></li>
            </ul>
        </nav>
    </header>


    <main>
        <div class="cards">
            <!-- Send Card -->
            <div class="card send">
                <h1 class="text_card">You Send</h1>
                <div class="form">
                    <!-- Контейнер для іконки та символу -->
                    <div class="input-icon-container">
                        <img src="{{ $cryptocurrencies->firstWhere('symbol', 'BTC')->image }}" class="currency-img send-img" alt="Currency">
                        <span class="text_currency send-text">BTC</span>
                    </div>
                    <input class="input" required type="number" inputmode="numeric" placeholder="0">
                    <span class="input-border"></span>
                </div>
                <p class="text_choose">Choose a system</p>
                <ul class="currency-list send-list">
                    <!-- Виводимо всі криптовалюти -->
                    @foreach($cryptocurrencies as $currency)
                    <li class="currency" data-currency="{{ $currency->symbol }}" data-image="{{ $currency->image }}">
                        <div class="title">
                            <!-- Виводимо іконку та назву криптовалюти -->
                            <img src="{{ $currency->image }}" alt="{{ $currency->name }}" class="currency-icon">
                            <span>{{ $currency->name }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>


            <!-- Receive Card -->
            <div class="card receive">
                <h1 class="text_card">You Receive</h1>
                <div class="form">
                    <!-- Контейнер для іконки та символу -->
                    <div class="input-icon-container">
                        <img src="{{ $cryptocurrencies->firstWhere('symbol', 'ETH')->image }}" class="currency-img receive-img" alt="Currency">
                        <span class="text_currency receive-text">ETH</span>
                    </div>
                    <input class="input" required type="number" inputmode="numeric" placeholder="0">
                    <span class="input-border"></span>
                </div>
                <p class="text_choose">Choose a system</p>
                <ul class="currency-list receive-list">
                    <!-- Виводимо всі криптовалюти -->
                    @foreach($cryptocurrencies as $currency)
                    <li class="currency" data-currency="{{ $currency->symbol }}" data-image="{{ $currency->image }}">
                        <div class="title">
                            <!-- Виводимо іконку та назву криптовалюти -->
                            <img src="{{ $currency->image }}" alt="{{ $currency->name }}" class="currency-icon">
                            <span>{{ $currency->name }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>


            <!-- Payment Data Card -->
            <div class="card info_card">
                <h1 class="text_card dop_func">Payment Data</h1>
                <div class="payment-info">
                    <div class="info-item">
                        <img id="send-img" src="{{ $currency->image }}" alt="Send Currency">
                        <div class="info-text">
                            <span id="send-currency-name">Bitcoin</span>
                            <div>0 <span class="currency-short" id="send-currency-symbol">BTC</span></div>
                        </div>
                    </div>
                    <div class="img-item">
                        <img src="{{ asset('images/icons/arrow_left.svg') }}" alt="arrow_left" class="arrow-left-icon">
                    </div>
                    <div class="info-item">
                        <img id="receive-img" src="{{ $currency->image }}" alt="Receive Currency">
                        <div class="info-text">
                            <span id="receive-currency-name">Ethereum</span>
                            <div>0 <span class="currency-short" id="receive-currency-symbol">ETH</span></div>
                        </div>
                    </div>
                </div>
                <div class="exchange-rate">
                    <p>Exchange rate...</p>
                </div>
                <!-- Form for Exchange -->
                <div class="form_info">
                    <div class="form">
                        <input class="input input_email" placeholder="E-mail" required="" type="email">
                        <span class="input-border"></span>
                    </div>
                    <div class="form">
                        <input class="input input_your_requisites" placeholder="Sender details" required="" type="text">
                        <span class="input-border"></span>
                    </div>
                    <div class="form">
                        <input class="input input_requisites" placeholder="Receiver details" required="" type="text">
                        <span class="input-border"></span>
                    </div>
                    <label class="checkbox-container">
                        <input type="checkbox" checked>
                        <p>I agree with the <a href="/agreement" class="agree_link">terms of service</a></p>
                    </label>
                    <button class="exchange_button">Exchange</button>
                </div>
            </div>

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
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contacts</h4>
                <ul>
                    <li><a href="mailto:support@ex-change.com">support@ex-change.com</a></li>
                    <li><a href="https://t.me/your_telegram" target="_blank">
                            <img src="images/icons/tg_logo.svg" alt="Telegram"></a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-sponsors">
            <div class="sponsors">
                <img src="images/sponsors/bestchange.svg" alt="Sponsor 1" class="sponsor-img">
                <img src="images/sponsors/bitsmedia.svg" alt="Sponsor 2" class="sponsor-img">
                <img src="images/sponsors/emon.svg" alt="Sponsor 3" class="sponsor-img">
            </div>
        </div>

        <div class="footer-copyright">
            <p>© 2024 All copyrights reserved</p>
        </div>
    </footer>


    <script src="{{ asset('js/menu.js') }}"></script>
    <script src="{{ asset('js/currency.js') }}"></script>
</body>

</html>