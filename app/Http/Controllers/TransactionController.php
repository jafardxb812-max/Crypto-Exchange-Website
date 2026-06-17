<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    private string $apiBase = 'https://api.bscscan.com/api';

    public function index()
    {
        return view('transaction');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'hash' => ['required', 'regex:/^0x[a-fA-F0-9]{64}$/'],
        ], [
            'hash.regex' => 'Invalid transaction hash format. Must be 0x followed by 64 hex characters.',
        ]);

        $hash = $request->input('hash');
        $apiKey = config('services.bscscan.key', 'YourApiKeyToken');

        [$txResponse, $receiptResponse, $tokenResponse] = [
            Http::timeout(10)->get($this->apiBase, [
                'module' => 'proxy',
                'action' => 'eth_getTransactionByHash',
                'txhash' => $hash,
                'apikey' => $apiKey,
            ]),
            Http::timeout(10)->get($this->apiBase, [
                'module' => 'proxy',
                'action' => 'eth_getTransactionReceipt',
                'txhash' => $hash,
                'apikey' => $apiKey,
            ]),
            Http::timeout(10)->get($this->apiBase, [
                'module'  => 'account',
                'action'  => 'tokentx',
                'txhash'  => $hash,
                'apikey'  => $apiKey,
            ]),
        ];

        $tx = $txResponse->json('result');
        $receipt = $receiptResponse->json('result');
        $tokenTransfers = $tokenResponse->json('result');

        if (!$tx) {
            return back()->withInput()->with('error', 'Transaction not found. Please check the hash and try again.');
        }

        $bnbValue   = bcdiv($this->hexToDec($tx['value'] ?? '0x0'), bcpow('10', '18'), 8);
        $gasPriceGwei = bcdiv($this->hexToDec($tx['gasPrice'] ?? '0x0'), bcpow('10', '9'), 4);
        $gasLimit   = (int) $this->hexToDec($tx['gas'] ?? '0x0');
        $blockNumber = $tx['blockNumber'] ? (int) $this->hexToDec($tx['blockNumber']) : null;

        $status  = null;
        $gasUsed = null;
        if ($receipt) {
            $status  = $this->hexToDec($receipt['status'] ?? '0x0') === '1' ? 'Success' : 'Failed';
            $gasUsed = (int) $this->hexToDec($receipt['gasUsed'] ?? '0x0');
        }

        $data = [
            'hash'           => $hash,
            'from'           => $tx['from'] ?? null,
            'to'             => $tx['to'] ?? null,
            'bnbValue'       => $bnbValue,
            'gasPriceGwei'   => $gasPriceGwei,
            'gasLimit'       => $gasLimit,
            'gasUsed'        => $gasUsed,
            'blockNumber'    => $blockNumber,
            'status'         => $status ?? 'Pending',
            'tokenTransfers' => is_array($tokenTransfers) ? $tokenTransfers : [],
        ];

        return view('transaction', compact('data', 'hash'));
    }

    private function hexToDec(string $hex): string
    {
        $hex = ltrim($hex, '0x');
        if ($hex === '' || $hex === '0') return '0';

        $result = '0';
        foreach (str_split($hex) as $char) {
            $result = bcadd(bcmul($result, '16'), (string) hexdec($char));
        }
        return $result;
    }
}
