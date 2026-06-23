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

        $hash   = $request->input('hash');
        $apiKey = config('services.bscscan.key', 'YourApiKeyToken');

        // Parallel API calls
        $txRes      = Http::timeout(10)->get($this->apiBase, ['module' => 'proxy',   'action' => 'eth_getTransactionByHash',  'txhash' => $hash, 'apikey' => $apiKey]);
        $rcptRes    = Http::timeout(10)->get($this->apiBase, ['module' => 'proxy',   'action' => 'eth_getTransactionReceipt', 'txhash' => $hash, 'apikey' => $apiKey]);
        $tokenRes   = Http::timeout(10)->get($this->apiBase, ['module' => 'account', 'action' => 'tokentx', 'txhash' => $hash, 'apikey' => $apiKey]);
        $blockRes   = Http::timeout(10)->get($this->apiBase, ['module' => 'proxy',   'action' => 'eth_blockNumber', 'apikey' => $apiKey]);

        $tx      = $txRes->json('result');
        $receipt = $rcptRes->json('result');
        $tokens  = $tokenRes->json('result');
        $curBlock = (int) $this->hexToDec($blockRes->json('result') ?? '0x0');

        if (!$tx) {
            return back()->withInput()->with('error', 'Transaction not found. Please check the hash and try again.');
        }

        $bnbValue     = bcdiv($this->hexToDec($tx['value']    ?? '0x0'), bcpow('10', '18'), 8);
        $gasPriceWei  = $this->hexToDec($tx['gasPrice']       ?? '0x0');
        $gasPriceGwei = bcdiv($gasPriceWei, bcpow('10', '9'), 6);
        $gasLimit     = (int) $this->hexToDec($tx['gas']      ?? '0x0');
        $blockNumber  = $tx['blockNumber'] ? (int) $this->hexToDec($tx['blockNumber']) : null;
        $nonce        = (int) $this->hexToDec($tx['nonce']    ?? '0x0');
        $inputData    = $tx['input'] ?? '0x';

        $status       = null;
        $gasUsed      = null;
        $txFee        = null;
        $timestamp    = null;
        $confirmations = null;

        if ($receipt) {
            $statusCode = $this->hexToDec($receipt['status'] ?? '0x0');
            $status     = $statusCode === '1' ? 'Success' : 'Failed';
            $gasUsed    = (int) $this->hexToDec($receipt['gasUsed'] ?? '0x0');
            $txFeeWei   = bcmul($gasPriceWei, (string) $gasUsed);
            $txFee      = bcdiv($txFeeWei, bcpow('10', '18'), 8);
        }

        // Fetch block for timestamp & confirmations
        if ($blockNumber) {
            $blockHex    = '0x' . dechex($blockNumber);
            $blockDetail = Http::timeout(10)->get($this->apiBase, [
                'module'  => 'proxy',
                'action'  => 'eth_getBlockByNumber',
                'tag'     => $blockHex,
                'boolean' => 'false',
                'apikey'  => $apiKey,
            ])->json('result');

            if ($blockDetail && isset($blockDetail['timestamp'])) {
                $timestamp = (int) $this->hexToDec($blockDetail['timestamp']);
            }
            if ($curBlock > 0) {
                $confirmations = $curBlock - $blockNumber;
            }
        }

        $timeAgo = null;
        if ($timestamp) {
            $diff = time() - $timestamp;
            if ($diff < 60)          $timeAgo = $diff . ' secs ago';
            elseif ($diff < 3600)    $timeAgo = floor($diff / 60) . ' mins ago';
            elseif ($diff < 86400)   $timeAgo = floor($diff / 3600) . ' hrs ago';
            else                     $timeAgo = floor($diff / 86400) . ' days ago';
        }

        $data = [
            'hash'           => $hash,
            'from'           => $tx['from']  ?? null,
            'to'             => $tx['to']    ?? null,
            'bnbValue'       => $bnbValue,
            'gasPriceGwei'   => $gasPriceGwei,
            'gasLimit'       => $gasLimit,
            'gasUsed'        => $gasUsed,
            'txFee'          => $txFee,
            'blockNumber'    => $blockNumber,
            'nonce'          => $nonce,
            'inputData'      => $inputData,
            'status'         => $status ?? 'Pending',
            'timestamp'      => $timestamp,
            'timeAgo'        => $timeAgo,
            'confirmations'  => $confirmations,
            'tokenTransfers' => is_array($tokens) ? $tokens : [],
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
