<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EX-Change — Your Trusted Exchange Platform</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/slider.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/loader.css') }}">
    <link rel="icon" href="{{ asset('images/logo/logo.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body>

    <div class="loader">
        <div class="box box-1">
            <div class="side-left"></div>
            <div class="side-right"></div>
            <div class="side-top"></div>
        </div>
        <div class="box box-2">
            <div class="side-left"></div>
            <div class="side-right"></div>
            <div class="side-top"></div>
        </div>
        <div class="box box-3">
            <div class="side-left"></div>
            <div class="side-right"></div>
            <div class="side-top"></div>
        </div>
        <div class="box box-4">
            <div class="side-left"></div>
            <div class="side-right"></div>
            <div class="side-top"></div>
        </div>
    </div>

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
                <li><a href="/contacts">Contacts</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="home_cards">
            <section class="main_content">
                <div class="content_wrapper">
                    <div class="content_image">
                        <img src="{{ asset('images/home_page/main_photo.png') }}" alt="Crypto Exchange">
                    </div>
                    <div class="content_text">
                        <h1>Your Gateway to Fast and Secure Crypto Transactions</h1>
                        <p>Instant cryptocurrency exchange at favorable rates. Data protection and 24/7 support.</p>
                        <a href="/exchange" class="btn">Start Exchange</a>
                    </div>
                </div>
            </section>


            <section class="home_card why-choose-us">
                <h1 class="text_card">Why Choose Us?</h1>
                <div class="features-cards">
                    <div class="card-feature">
                        <h3>Wide Range of Currencies</h3>
                        <p>Exchange Bitcoin, Ethereum, Dogecoin, and more.</p>
                    </div>
                    <div class="card-feature">
                        <h3>Real-Time Rates</h3>
                        <p>Stay updated with the latest exchange rates for all supported currencies.</p>
                    </div>
                    <div class="card-feature">
                        <h3>Fast Transactions</h3>
                        <p>Complete your exchanges in just a few clicks.</p>
                    </div>
                    <div class="card-feature">
                        <h3>Secure Platform</h3>
                        <p>Your data and funds are protected with industry-leading encryption.</p>
                    </div>
                </div>
            </section>

            <!-- Latest Reviews Section (with slider) -->
            <section class="home_card reviews-slider">
                <h1 class="text_card">Latest Reviews</h1>
                <div class="content-wrapper">
                    <div class="slider-container">
                        <div class="slider">

                            <div class="review-slide">
                                <h3>Anna P.</h3>
                                <p>I’ve tried several platforms for exchanging cryptocurrencies, but this one truly stands out. The process is incredibly smooth, and the rates are updated in real-time, which makes a huge difference when making decisions. I’ve already completed multiple transactions, and each one was seamless. Highly recommend to both beginners and experienced users!</p>
                                <span class="date">July 17, 2025</span>
                            </div>

                            <div class="review-slide">
                                <h3>James R.</h3>
                                <p>What I appreciate the most about this platform is how user-friendly it is. Even as someone who isn’t super tech-savvy, I had no issues navigating through the process. The security features gave me peace of mind, and I especially liked how transparent the fee structure is. It’s rare to find a service that combines speed, reliability, and excellent customer support.</p>
                                <span class="date">November 15, 2025</span>
                            </div>

                            <div class="review-slide">
                                <h3>Maria L.</h3>
                                <p>I was initially hesitant to use a new exchange platform, but this exceeded all my expectations. The transaction speeds are unmatched, and I love how detailed the step-by-step process is. I had a minor issue with my first exchange, but customer support responded almost immediately and resolved it efficiently. It’s now my go-to platform for all crypto exchanges.</p>
                                <span class="date">January 15, 2025</span>
                            </div>

                            <div class="review-slide">
                                <h3>Viktor K.</h3>
                                <p>The simplicity of this platform is what won me over. I’ve been trading cryptocurrencies for a few years now, and this site offers everything I need. The exchange rates are always competitive, and I love how quickly transactions are processed. Plus, the security measures in place are top-notch—it's reassuring to know my funds are safe. A great experience overall!</p>
                                <span class="date">April 4, 2025</span>
                            </div>

                            <div class="review-slide">
                                <h3>Sophia T.</h3>
                                <p>I was blown away by how easy it was to exchange my crypto here. The design is clean, and everything is laid out perfectly for users like me who aren’t financial experts. I exchanged Bitcoin to Ethereum within minutes and got exactly what I expected. No hidden fees, no delays—just smooth transactions. I wish I had found this platform sooner!</p>
                                <span class="date">May 14, 2025</span>
                            </div>
                        </div>
                        <button class="prev-btn">&#10094;</button>
                        <button class="next-btn">&#10095;</button>
                    </div>
                    <div class="review-image">
                        <img src="{{ asset('images/home_page/review_photo.png') }}" alt="Review Photo">
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
    <script src="{{ asset('js/slider.js') }}"></script>
    <script src="{{ asset('js/loader.js') }}"></script>
</body>

</html>