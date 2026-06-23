<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EX-Change — Location Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tracker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="icon" href="{{ asset('images/logo/logo.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Leaflet map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
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
                <li><a href="/tracker" class="active">Tracker</a></li>
                <li><a href="/faq">FAQ</a></li>
                <li><a href="/agreement">Agreement</a></li>
                <li><a href="/contacts">Contacts</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="tracker-wrapper">
            <div class="tracker-card">
                <h1>Location Tracker</h1>

                <!-- Map -->
                <div id="map"></div>

                <!-- Status -->
                <div class="status-row">
                    <span class="status-dot waiting" id="status-dot"></span>
                    <span id="status-text">Press Start to begin tracking</span>
                </div>

                <!-- Coordinates -->
                <div class="coords-grid">
                    <div class="coord-box">
                        <div class="coord-label">Latitude</div>
                        <div class="coord-value" id="lat">—</div>
                    </div>
                    <div class="coord-box">
                        <div class="coord-label">Longitude</div>
                        <div class="coord-value" id="lng">—</div>
                    </div>
                    <div class="coord-box">
                        <div class="coord-label">Accuracy</div>
                        <div class="coord-value" id="acc">—</div>
                    </div>
                    <div class="coord-box">
                        <div class="coord-label">Updated</div>
                        <div class="coord-value" id="updated">—</div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="btn-row">
                    <button class="tracker-btn" id="start-btn" onclick="startTracking()">Start Tracking</button>
                    <button class="tracker-btn stop-btn" id="stop-btn" onclick="stopTracking()" disabled>Stop</button>
                </div>

                <!-- Share link -->
                <div class="copy-box" id="share-box" style="display:none;">
                    <input class="copy-input" id="share-link" readonly>
                    <button class="copy-btn" onclick="copyLink()">Copy Link</button>
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLs=" crossorigin=""></script>
    <script src="{{ asset('js/menu.js') }}"></script>
    <script>
        // ── Map setup ──────────────────────────────────────────────────────────
        const map    = L.map('map').setView([20, 0], 2);
        const tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        L.tileLayer(tileUrl, {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(map);

        const markerIcon = L.divIcon({
            className: '',
            html: `<div style="
                width:18px;height:18px;border-radius:50%;
                background:#007bff;border:3px solid #fff;
                box-shadow:0 0 0 4px rgba(0,123,255,0.3);
            "></div>`,
            iconSize: [18, 18],
            iconAnchor: [9, 9],
        });

        let marker    = null;
        let watchId   = null;
        let firstFix  = true;

        // ── DOM refs ───────────────────────────────────────────────────────────
        const dot       = document.getElementById('status-dot');
        const statusTxt = document.getElementById('status-text');
        const startBtn  = document.getElementById('start-btn');
        const stopBtn   = document.getElementById('stop-btn');
        const shareBox  = document.getElementById('share-box');
        const shareLink = document.getElementById('share-link');

        function setStatus(state, msg) {
            dot.className = 'status-dot ' + state;
            statusTxt.textContent = msg;
        }

        // ── Tracking ───────────────────────────────────────────────────────────
        function startTracking() {
            if (!navigator.geolocation) {
                setStatus('error', 'Geolocation is not supported by this browser.');
                return;
            }
            setStatus('waiting', 'Getting your location…');
            startBtn.disabled = true;
            stopBtn.disabled  = false;
            firstFix = true;

            watchId = navigator.geolocation.watchPosition(onPosition, onError, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0,
            });
        }

        function stopTracking() {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            setStatus('waiting', 'Tracking stopped.');
            startBtn.disabled = false;
            stopBtn.disabled  = true;
        }

        function onPosition(pos) {
            const { latitude: lat, longitude: lng, accuracy } = pos.coords;

            // Update map
            const latlng = [lat, lng];
            if (!marker) {
                marker = L.marker(latlng, { icon: markerIcon }).addTo(map);
            } else {
                marker.setLatLng(latlng);
            }
            if (firstFix) {
                map.setView(latlng, 15);
                firstFix = false;
            }

            // Update info
            document.getElementById('lat').textContent     = lat.toFixed(6);
            document.getElementById('lng').textContent     = lng.toFixed(6);
            document.getElementById('acc').textContent     = Math.round(accuracy) + ' m';
            document.getElementById('updated').textContent = new Date().toLocaleTimeString();

            setStatus('active', 'Tracking live…');

            // Share link
            const url = `https://www.google.com/maps?q=${lat},${lng}`;
            shareLink.value = url;
            shareBox.style.display = 'flex';
        }

        function onError(err) {
            const msgs = {
                1: 'Permission denied. Please allow location access.',
                2: 'Position unavailable. Check GPS signal.',
                3: 'Timed out. Try again.',
            };
            setStatus('error', msgs[err.code] || 'Unknown error.');
            startBtn.disabled = false;
            stopBtn.disabled  = true;
        }

        function copyLink() {
            shareLink.select();
            navigator.clipboard.writeText(shareLink.value).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy Link', 2000);
            });
        }
    </script>
</body>

</html>
