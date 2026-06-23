<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EX-Change — Frequently Asked Question</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/faq.css') }}">
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
                <li><a href="/faq" class="active">FAQ</a></li>
                <li><a href="/agreement">Agreement</a></li>
                <li><a href="/contacts">Contacts</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="faq_cards">
            <section class="faq-section">
                <div class="faq-container">
                    <h1 class="faq-title">Frequently Asked Questions</h1>

                    <div class="faq-card">
                        <h2 class="faq-question">How to make an exchange?</h2>
                        <p class="faq-answer">
                            Start by selecting the currency you will give. Then choose the currency you want to receive. Enter your payment details: Email, Sending Account (card/wallet number you will send from), and Receiving Account (card/wallet number where you will receive funds). Finally, click the "Exchange" button and pay for the request. Once the payment is confirmed, you will receive your funds.
                        </p>
                    </div>

                    <div class="faq-card">
                        <h2 class="faq-question">How fast is the exchange process?</h2>
                        <p class="faq-answer">
                            Exchanges are processed automatically. On average, it takes 5–10 minutes. If you are sending cryptocurrency, the system waits for 2 confirmations from the network before transferring the funds. Incorrect payment details provided by the user may delay the exchange.
                        </p>
                    </div>

                    <div class="faq-card">
                        <h2 class="faq-question">How to log in to my account?</h2>
                        <p class="faq-answer">
                            To log in to your account, you need to register on our website. Click the "Register" button in the top-right corner of the site. Then, enter your name, email, and create a password.
                        </p>
                    </div>

                    <div class="faq-card">
                        <h2 class="faq-question">How to track my exchange request?</h2>
                        <p class="faq-answer">
                            You can track your request at every stage. After making an exchange request, a block will appear showing the "Request Status." Possible statuses include: "Awaiting Payment," "Error Occurred," "Payment Confirmation," and "Successful Exchange."
                        </p>
                    </div>

                    <div class="faq-card">
                        <h2 class="faq-question">Have another question?</h2>
                        <p class="faq-answer">
                            If you have additional questions that you cannot find answers to, contact our 24/7 support team. Visit the "Contacts" section and send us an email or message us on Telegram.
                        </p>
                    </div>
                </div>
            </section>
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