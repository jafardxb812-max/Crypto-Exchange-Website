/**
 * Binance MCP Server
 * ──────────────────
 * Real-time Binance market data via WebSocket + REST.
 * Includes paper-trading simulator (no real funds).
 *
 * Tools:
 *   binance_price         — live price (WS cache, zero delay)
 *   binance_stats_24h     — 24h change %, high, low, volume
 *   binance_orderbook     — top bids / asks
 *   binance_klines        — OHLCV candlestick data
 *   binance_top_movers    — top 10 gainers & losers
 *   binance_simulate_trade— [PAPER] buy/sell at live price
 *   binance_portfolio     — [PAPER] portfolio & trade history
 */

import { Server }               from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
    CallToolRequestSchema,
    ListToolsRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import https  from 'https';
import WebSocket from 'ws';

// ── Constants ─────────────────────────────────────────────────────────────────
const REST   = 'https://api.binance.com/api/v3';
const WS_URL = 'wss://stream.binance.com:9443/stream';

// ── REST fetch ────────────────────────────────────────────────────────────────
function fetchJSON(url) {
    return new Promise((resolve, reject) => {
        https.get(url, { timeout: 8000 }, res => {
            let raw = '';
            res.on('data', d => raw += d);
            res.on('end', () => {
                try { resolve(JSON.parse(raw)); }
                catch (e) { reject(new Error('Parse error: ' + raw.slice(0, 120))); }
            });
        }).on('error', reject).on('timeout', () => reject(new Error('Request timeout')));
    });
}

// ── WebSocket price cache (zero-delay) ────────────────────────────────────────
const priceCache   = new Map();   // SYMBOL → { price, change, high, low, vol, ts }
const activeStreams = new Map();   // SYMBOL → WebSocket

function subscribeWS(symbol) {
    if (activeStreams.has(symbol)) return;
    const ws = new WebSocket(`${WS_URL}?streams=${symbol.toLowerCase()}@ticker`);

    ws.on('open',  ()  => process.stderr.write(`[WS] connected: ${symbol}\n`));
    ws.on('close', ()  => { activeStreams.delete(symbol); });
    ws.on('error', ()  => { activeStreams.delete(symbol); ws.terminate(); });

    ws.on('message', raw => {
        try {
            const { data: d } = JSON.parse(raw);
            if (!d) return;
            priceCache.set(symbol, {
                price:  d.c,   // last/close
                change: d.P,   // 24h % change
                high:   d.h,
                low:    d.l,
                vol:    d.v,   // base volume
                qvol:   d.q,   // quote volume (USDT)
                open:   d.o,
                count:  d.n,   // trade count
                ts:     Date.now(),
            });
        } catch (_) {}
    });

    activeStreams.set(symbol, ws);
}

// ── Paper-trading portfolio ───────────────────────────────────────────────────
const paper = {
    usdt:    10_000,
    hold:    {},       // { BTC: 0.5, ETH: 2.0, ... }
    history: [],
};

function paperTrade(symbol, side, qty, price) {
    const cost = qty * parseFloat(price);
    const base = symbol.toUpperCase().replace(/USDT$/, '');

    if (side === 'BUY') {
        if (paper.usdt < cost) return { ok: false, err: `Need $${cost.toFixed(2)} USDT, have $${paper.usdt.toFixed(2)}` };
        paper.usdt       -= cost;
        paper.hold[base]  = (paper.hold[base] || 0) + qty;
    } else {
        if ((paper.hold[base] || 0) < qty) return { ok: false, err: `Need ${qty} ${base}, have ${paper.hold[base] || 0}` };
        paper.hold[base] -= qty;
        paper.usdt       += cost;
    }

    const order = { id: Date.now(), symbol: symbol.toUpperCase(), side, qty, price: parseFloat(price), cost, ts: new Date().toISOString() };
    paper.history.push(order);
    return { ok: true, order };
}

// ── Format helpers ────────────────────────────────────────────────────────────
const usd   = n => '$' + parseFloat(n).toLocaleString(undefined, { maximumFractionDigits: 6 });
const num   = (n, d = 4) => parseFloat(n).toFixed(d);
const dir   = n => parseFloat(n) >= 0 ? '▲' : '▼';
const pad   = (s, w) => String(s).padStart(w);

// ── Tool text response ────────────────────────────────────────────────────────
function text(content) {
    return { content: [{ type: 'text', text: content }] };
}

// ── Server ────────────────────────────────────────────────────────────────────
const server = new Server(
    { name: 'binance-mcp', version: '1.0.0' },
    { capabilities: { tools: {} } }
);

// ── List tools ────────────────────────────────────────────────────────────────
server.setRequestHandler(ListToolsRequestSchema, async () => ({
    tools: [
        {
            name: 'binance_price',
            description: 'Get real-time price for a Binance pair. WebSocket cached — zero delay on repeat calls.',
            inputSchema: {
                type: 'object',
                properties: { symbol: { type: 'string', description: 'e.g. BTCUSDT, ETHUSDT, BNBUSDT' } },
                required: ['symbol'],
            },
        },
        {
            name: 'binance_stats_24h',
            description: 'Get 24-hour statistics: price change %, high, low, volume, open, close, trade count.',
            inputSchema: {
                type: 'object',
                properties: { symbol: { type: 'string', description: 'e.g. BTCUSDT' } },
                required: ['symbol'],
            },
        },
        {
            name: 'binance_orderbook',
            description: 'Get order book: top bids (buy) and asks (sell) with quantities.',
            inputSchema: {
                type: 'object',
                properties: {
                    symbol: { type: 'string', description: 'e.g. BTCUSDT' },
                    limit:  { type: 'number', description: 'Depth: 5, 10, or 20 (default 5)', default: 5 },
                },
                required: ['symbol'],
            },
        },
        {
            name: 'binance_klines',
            description: 'Get OHLCV candlestick (kline) data.',
            inputSchema: {
                type: 'object',
                properties: {
                    symbol:   { type: 'string', description: 'e.g. BTCUSDT' },
                    interval: { type: 'string', description: '1m 5m 15m 1h 4h 1d (default 1h)', default: '1h' },
                    limit:    { type: 'number', description: 'Candles to fetch, max 100 (default 20)', default: 20 },
                },
                required: ['symbol'],
            },
        },
        {
            name: 'binance_top_movers',
            description: 'Get top 10 gainers and top 10 losers across all USDT pairs in 24h.',
            inputSchema: { type: 'object', properties: {} },
        },
        {
            name: 'binance_simulate_trade',
            description: '[PAPER TRADING — NO REAL FUNDS] Simulate BUY/SELL at live Binance price. Tracks a virtual $10,000 portfolio.',
            inputSchema: {
                type: 'object',
                properties: {
                    symbol:   { type: 'string',  description: 'e.g. BTCUSDT' },
                    side:     { type: 'string',  enum: ['BUY', 'SELL'], description: 'BUY or SELL' },
                    quantity: { type: 'number',  description: 'Amount of base asset (e.g. 0.01 for 0.01 BTC)' },
                },
                required: ['symbol', 'side', 'quantity'],
            },
        },
        {
            name: 'binance_portfolio',
            description: '[PAPER TRADING] Show virtual portfolio holdings, live value, and recent trade history.',
            inputSchema: { type: 'object', properties: {} },
        },
    ],
}));

// ── Call tools ────────────────────────────────────────────────────────────────
server.setRequestHandler(CallToolRequestSchema, async (req) => {
    const { name, arguments: args } = req.params;

    try {
        // ── binance_price ─────────────────────────────────────────────────────
        if (name === 'binance_price') {
            const sym = args.symbol.toUpperCase();
            subscribeWS(sym);   // open WS stream (noop if already open)

            const cached = priceCache.get(sym);
            if (cached && Date.now() - cached.ts < 3000) {
                const chg = parseFloat(cached.change);
                return text([
                    `${sym}  [WebSocket live]`,
                    `Price  : ${usd(cached.price)}`,
                    `24h    : ${dir(chg)} ${chg >= 0 ? '+' : ''}${num(chg, 2)}%`,
                    `High   : ${usd(cached.high)}`,
                    `Low    : ${usd(cached.low)}`,
                    `Volume : ${parseFloat(cached.vol).toLocaleString()} ${sym.replace('USDT', '')}`,
                    `Updated: ${new Date(cached.ts).toISOString()}`,
                ].join('\n'));
            }

            // REST fallback while WS warms up
            const d = await fetchJSON(`${REST}/ticker/price?symbol=${sym}`);
            return text(`${sym}\nPrice: ${usd(d.price)}\n(WS stream warming up — next call will be real-time)`);
        }

        // ── binance_stats_24h ─────────────────────────────────────────────────
        if (name === 'binance_stats_24h') {
            const sym = args.symbol.toUpperCase();
            const d   = await fetchJSON(`${REST}/ticker/24hr?symbol=${sym}`);
            const chg = parseFloat(d.priceChangePercent);
            return text([
                `${sym}  —  24h Statistics`,
                `─────────────────────────────────────`,
                `Last Price   : ${usd(d.lastPrice)}`,
                `24h Change   : ${dir(chg)} ${chg >= 0 ? '+' : ''}${num(chg, 2)}%  (${usd(d.priceChange)})`,
                `Open         : ${usd(d.openPrice)}`,
                `High         : ${usd(d.highPrice)}`,
                `Low          : ${usd(d.lowPrice)}`,
                `Volume       : ${parseFloat(d.volume).toLocaleString()} ${sym.replace('USDT', '')}`,
                `USDT Volume  : $${parseFloat(d.quoteVolume).toLocaleString()}`,
                `Trades       : ${Number(d.count).toLocaleString()}`,
                `Timestamp    : ${new Date(d.closeTime).toISOString()}`,
            ].join('\n'));
        }

        // ── binance_orderbook ─────────────────────────────────────────────────
        if (name === 'binance_orderbook') {
            const sym   = args.symbol.toUpperCase();
            const limit = Math.min(args.limit || 5, 20);
            const d     = await fetchJSON(`${REST}/depth?symbol=${sym}&limit=${limit}`);

            const spread = (parseFloat(d.asks[0][0]) - parseFloat(d.bids[0][0])).toFixed(6);
            const rows   = [`${sym}  Order Book (depth ${limit})`, ''];

            rows.push(`${'PRICE'.padStart(18)}   ${'QTY'.padStart(14)}   SIDE`);
            rows.push('─'.repeat(52));
            [...d.asks].reverse().forEach(([p, q]) =>
                rows.push(`${pad(num(p, 4), 18)}   ${pad(num(q, 4), 14)}   ASK ▲`));
            rows.push(`${'── Spread: ' + spread + ' ──'.padStart(40)}`);
            d.bids.forEach(([p, q]) =>
                rows.push(`${pad(num(p, 4), 18)}   ${pad(num(q, 4), 14)}   BID ▼`));

            return text(rows.join('\n'));
        }

        // ── binance_klines ────────────────────────────────────────────────────
        if (name === 'binance_klines') {
            const sym      = args.symbol.toUpperCase();
            const interval = args.interval || '1h';
            const limit    = Math.min(args.limit || 20, 100);
            const data     = await fetchJSON(`${REST}/klines?symbol=${sym}&interval=${interval}&limit=${limit}`);

            const rows = [`${sym}  —  ${interval} candles (last ${Math.min(limit, data.length)})`, ''];
            rows.push(`${'TIME'.padEnd(17)} ${'OPEN'.padStart(12)} ${'HIGH'.padStart(12)} ${'LOW'.padStart(12)} ${'CLOSE'.padStart(12)} ${'VOLUME'.padStart(14)}`);
            rows.push('─'.repeat(85));

            data.slice(-20).forEach(k => {
                const t   = new Date(k[0]).toISOString().slice(0, 16).replace('T', ' ');
                const up  = parseFloat(k[4]) >= parseFloat(k[1]);
                const sym2 = up ? '▲' : '▼';
                rows.push(
                    `${t}  ${pad('$'+num(k[1],2), 12)} ${pad('$'+num(k[2],2), 12)} ${pad('$'+num(k[3],2), 12)} ${sym2}${pad('$'+num(k[4],2), 11)} ${pad(num(k[5],2), 14)}`
                );
            });

            return text(rows.join('\n'));
        }

        // ── binance_top_movers ────────────────────────────────────────────────
        if (name === 'binance_top_movers') {
            const all  = await fetchJSON(`${REST}/ticker/24hr`);
            const usdt = all
                .filter(t => t.symbol.endsWith('USDT'))
                .map(t => ({ s: t.symbol, p: parseFloat(t.lastPrice), c: parseFloat(t.priceChangePercent), v: parseFloat(t.quoteVolume) }));

            const gainers = [...usdt].sort((a, b) => b.c - a.c).slice(0, 10);
            const losers  = [...usdt].sort((a, b) => a.c - b.c).slice(0, 10);

            const rows = ['BINANCE — TOP MOVERS (USDT pairs, 24h)', ''];
            rows.push('TOP 10 GAINERS');
            rows.push('─'.repeat(56));
            gainers.forEach((t, i) =>
                rows.push(`  ${String(i+1).padStart(2)}. ${t.s.padEnd(12)} ▲ +${num(t.c, 2).padStart(7)}%   ${usd(t.p)}`));

            rows.push('', 'TOP 10 LOSERS');
            rows.push('─'.repeat(56));
            losers.forEach((t, i) =>
                rows.push(`  ${String(i+1).padStart(2)}. ${t.s.padEnd(12)} ▼  ${num(t.c, 2).padStart(7)}%   ${usd(t.p)}`));

            return text(rows.join('\n'));
        }

        // ── binance_simulate_trade ────────────────────────────────────────────
        if (name === 'binance_simulate_trade') {
            const sym  = args.symbol.toUpperCase();
            const priceData = await fetchJSON(`${REST}/ticker/price?symbol=${sym}`);
            const livePrice = priceData.price;

            const result = paperTrade(sym, args.side.toUpperCase(), args.quantity, livePrice);
            if (!result.ok) return text(`[PAPER] ERROR: ${result.err}`);

            const o = result.order;
            return text([
                '⚠  SIMULATED ORDER — PAPER TRADING ONLY — NO REAL FUNDS',
                '─'.repeat(52),
                `Order ID   : ${o.id}`,
                `Symbol     : ${o.symbol}`,
                `Side       : ${o.side}`,
                `Quantity   : ${o.qty}`,
                `Live Price : ${usd(o.price)}`,
                `Total Cost : $${o.cost.toFixed(2)} USDT`,
                `Timestamp  : ${o.ts}`,
                `Status     : FILLED [SIMULATED]`,
                '─'.repeat(52),
                `Virtual USDT balance: $${paper.usdt.toFixed(2)}`,
            ].join('\n'));
        }

        // ── binance_portfolio ─────────────────────────────────────────────────
        if (name === 'binance_portfolio') {
            const rows = ['⚠  PAPER PORTFOLIO — SIMULATED ONLY', '─'.repeat(48)];
            rows.push(`Virtual USDT : $${paper.usdt.toFixed(2)}`);
            rows.push('');
            rows.push('Holdings:');

            let totalHoldVal = 0;
            const holdEntries = Object.entries(paper.hold).filter(([, q]) => q > 0);
            if (holdEntries.length === 0) {
                rows.push('  (none)');
            } else {
                for (const [asset, qty] of holdEntries) {
                    try {
                        const p   = await fetchJSON(`${REST}/ticker/price?symbol=${asset}USDT`);
                        const val = qty * parseFloat(p.price);
                        totalHoldVal += val;
                        rows.push(`  ${asset.padEnd(8)} ${String(qty.toFixed(6)).padStart(14)}  ~$${val.toFixed(2)}`);
                    } catch {
                        rows.push(`  ${asset.padEnd(8)} ${String(qty.toFixed(6)).padStart(14)}`);
                    }
                }
            }

            rows.push('');
            rows.push(`Total Portfolio Value: ~$${(paper.usdt + totalHoldVal).toFixed(2)}`);
            rows.push('');
            rows.push(`Recent Trades (last 5):`);
            rows.push('─'.repeat(48));
            const recent = paper.history.slice(-5).reverse();
            if (recent.length === 0) rows.push('  (no trades yet)');
            recent.forEach(o =>
                rows.push(`  [${o.ts.slice(0,16)}] ${o.side} ${o.qty} ${o.symbol} @ ${usd(o.price)} = $${o.cost.toFixed(2)}`));

            return text(rows.join('\n'));
        }

        return text(`Unknown tool: ${name}`);

    } catch (err) {
        return text(`Error: ${err.message}`);
    }
});

// ── Connect & start ───────────────────────────────────────────────────────────
const transport = new StdioServerTransport();
await server.connect(transport);
process.stderr.write('Binance MCP server started\n');
