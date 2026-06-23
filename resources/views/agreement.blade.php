<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EX-Change — Agreement</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/agreement.css') }}">
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
                <li><a href="/agreement" class="active">Agreement</a></li>
                <li><a href="/contacts">Contacts</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="agreement_card">
            <section>
                <h1>Exchange Rules</h1>
                <h3>1. General Provisions</h3>
                <p>This Agreement (hereinafter referred to as the Agreement) describes the terms and conditions under which the Ex-Change service provides its services and is an official written public offer addressed to individuals (hereinafter referred to as the User) to enter into an Agreement for the provision of services by the Ex-Change service on the terms set forth below.</p>
                <p>Before using the services of the Ex-Change service, the User must fully familiarize themselves with the terms of the "Agreement for the provision of services by the Ex-Change service."</p>
                <p>Using the services of the Ex-Change service is possible only if the User accepts all the terms of the Agreement.</p>
                <p>The current version of the Agreement is publicly accessible on the Ex-Change service website.</p>
            </section>

            <section>
                <h3>2. Terms and Definitions Used in the Agreement</h3>
                <p><strong>Ex-Change Service (hereinafter referred to as the Service)</strong> – a system providing online services for exchanging, selling, and purchasing electronic assets.</p>
                <p><strong>Website of the Service</strong> – <a href="https://www.ex-change.cc">www.ex-change.cc</a></p>
                <p><strong>User</strong> – any individual or legal entity using the Ex-Change Service and having accepted the Agreement under its terms.</p>
                <p><strong>Partner</strong> – a person providing the Service with services to attract Users, the conditions of which are described in this Agreement.</p>
                <p><strong>Payment System</strong> – a software product created by a third party, representing a mechanism for accounting monetary (electronic money) and/or other obligations, paying for goods and services online, as well as organizing settlements between users.</p>
                <p>The main payment systems within the framework of this Agreement are: Bitcoin, Bitcoin Cash, Ethereum, and others. The exact list of Payment Systems is indicated on the Service's website.</p>
                <p><strong>Payment/Transaction</strong> – the transfer of electronic and/or other electronic assets from the payer to the recipient.</p>
                <p><strong>Application</strong> – an expression of intent by the User to use one of the services offered by the Ex-Change Service by filling out an electronic form via the Service's website, under the terms described in the Agreement and specified in the parameters of this Application.</p>
                <p><strong>Verification of the card</strong> – checking the card's (or account's) ownership by its owner. The terms of verification are set by the Service and are performed once for each new account (card) of the client.</p>
            </section>

            <section>
                <h3>3. Subject of the Agreement and Procedure for Its Entry into Force</h3>
                <p><strong>3.1.</strong> The subject of this Agreement is the provision of the following services to the User by the Ex-Change Service:</p>
                <ul>
                    <li>Exchange of electronic assets;</li>
                    <li>Sale of electronic assets to the User;</li>
                    <li>Purchase of electronic assets from the User.</li>
                </ul>
                <p><strong>3.2.</strong> Acceptance (agreement to the terms) of this Agreement (Offer) is carried out by the User in case of:</p>
                <ul>
                    <li>Submitting an Application for one of the services;</li>
                    <li>Agreeing to all provisions specified in the Agreement.</li>
                </ul>
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