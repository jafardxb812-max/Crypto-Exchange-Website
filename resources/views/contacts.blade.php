<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EX-Change — Contacts</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contacts.css') }}">
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
                <li><a href="/transaction">Transaction</a></li>
                <li><a href="/tracker">Tracker</a></li>
                <li><a href="/faq">FAQ</a></li>
                <li><a href="/agreement">Agreement</a></li>
                <li><a href="/contacts" class="active">Contacts</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="contacts_card">
            <h1>Technical Support</h1>
            <div class="icon_row">
                <div class="icon_item">
                    <img src="images/icons/clock_icon.svg" alt="Clock Icon">
                    <div>
                        <div class="info_title">Working Hours</div>
                        <div class="info_text">24/7</div>
                    </div>
                </div>
                <div class="icon_item">
                    <img src="images/icons/mail_icon.svg" alt="Mail Icon">
                    <div>
                        <div class="info_title">Email</div>
                        <div class="info_text">ex-change@gmail.com</div>
                    </div>
                </div>
                <div class="icon_item">
                    <img src="images/icons/tg_icon.svg" alt="Telegram Icon">
                    <div>
                        <div class="info_title">Telegram</div>
                        <div class="info_text">@exchange_support</div>
                    </div>
                </div>
            </div>
            <p class="help__text">
                If you have any technical or financial issues, please write to us, and we will help you resolve your problem. We respond to inquiries within 15–60 minutes, depending on the service load. We work for our clients and are always happy to hear their feedback. Your feedback helps us make our service even better!
            </p>
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
    
</body>

</html>