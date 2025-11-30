<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BybitService
{
    protected $apiKey;
    protected $apiSecret;
    protected $baseUrl;
    protected $testnet;

    public function __construct($apiKey, $apiSecret, $testnet = false)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->testnet = $testnet;
        $this->baseUrl = $testnet 
            ? 'https://api-testnet.bybit.com' 
            : 'https://api.bybit.com';
    }

    public function testConnection()
    {
        try {
            $response = $this->getBalance();
            return $response !== null;
        } catch (\Exception $e) {
            Log::error('Bybit connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getBalance()
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'accountType' => 'UNIFIED',
            ];
            
            $response = $this->signedRequest('GET', '/v5/account/wallet-balance', $params, $timestamp);
            
            if (isset($response['result']['list'][0]['totalWalletBalance'])) {
                return (float) $response['result']['list'][0]['totalWalletBalance'];
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to get Bybit balance: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPositions($symbol = null)
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'settleCoin' => 'USDT',
            ];
            
            if ($symbol) {
                $params['symbol'] = $symbol;
            }
            
            $response = $this->signedRequest('GET', '/v5/position/list', $params, $timestamp);
            
            return $response['result']['list'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get Bybit positions: ' . $e->getMessage());
            throw $e;
        }
    }

    public function placeOrder($symbol, $side, $qty, $orderType = 'Market', $price = null, $stopLoss = null, $takeProfit = null)
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
                'side' => ucfirst(strtolower($side)), // Buy or Sell
                'orderType' => $orderType,
                'qty' => (string) $qty,
                'timeInForce' => $orderType === 'Market' ? 'IOC' : 'GTC',
            ];
            
            if ($price && $orderType === 'Limit') {
                $params['price'] = (string) $price;
            }
            
            if ($stopLoss) {
                $params['stopLoss'] = (string) $stopLoss;
            }
            
            if ($takeProfit) {
                $params['takeProfit'] = (string) $takeProfit;
            }
            
            $response = $this->signedRequest('POST', '/v5/order/create', $params, $timestamp);
            
            return $response['result'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to place Bybit order: ' . $e->getMessage());
            throw $e;
        }
    }

    public function setTradingStop($symbol, $stopLoss = null, $takeProfit = null, $positionIdx = 0)
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
                'positionIdx' => $positionIdx,
            ];
            
            if ($stopLoss) {
                $params['stopLoss'] = (string) $stopLoss;
            }
            
            if ($takeProfit) {
                $params['takeProfit'] = (string) $takeProfit;
            }
            
            $response = $this->signedRequest('POST', '/v5/position/trading-stop', $params, $timestamp);
            
            return $response['result'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to set trading stop: ' . $e->getMessage());
            throw $e;
        }
    }

    public function closePosition($symbol, $side)
    {
        try {
            $positions = $this->getPositions($symbol);
            
            if (empty($positions)) {
                return null;
            }
            
            $position = $positions[0];
            $qty = abs((float) $position['size']);
            
            // Reverse side to close position
            $closeSide = $side === 'Buy' ? 'Sell' : 'Buy';
            
            return $this->placeOrder($symbol, $closeSide, $qty, 'Market');
        } catch (\Exception $e) {
            Log::error('Failed to close Bybit position: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cancelOrder($symbol, $orderId)
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
                'orderId' => $orderId,
            ];
            
            $response = $this->signedRequest('POST', '/v5/order/cancel', $params, $timestamp);
            
            return $response['result'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to cancel Bybit order: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getOrderStatus($symbol, $orderId)
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
                'orderId' => $orderId,
            ];
            
            $response = $this->signedRequest('GET', '/v5/order/realtime', $params, $timestamp);
            
            return $response['result']['list'][0] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get order status: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getKlines($symbol, $interval = '15', $limit = 200)
    {
        try {
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
                'interval' => $interval,
                'limit' => $limit,
            ];
            
            $response = Http::get($this->baseUrl . '/v5/market/kline', $params);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['result']['list'] ?? [];
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get klines: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCurrentPrice($symbol)
    {
        try {
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
            ];
            
            $response = Http::get($this->baseUrl . '/v5/market/tickers', $params);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['result']['list'][0]['lastPrice'])) {
                    return (float) $data['result']['list'][0]['lastPrice'];
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get current price: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function signedRequest($method, $endpoint, $params = [], $timestamp = null)
    {
        $timestamp = $timestamp ?? round(microtime(true) * 1000);
        $recvWindow = 5000;
        
        $queryString = http_build_query($params);
        
        if ($method === 'GET') {
            $signString = $timestamp . $this->apiKey . $recvWindow . $queryString;
        } else {
            $signString = $timestamp . $this->apiKey . $recvWindow . json_encode($params);
        }
        
        $signature = hash_hmac('sha256', $signString, $this->apiSecret);
        
        $headers = [
            'X-BAPI-API-KEY' => $this->apiKey,
            'X-BAPI-SIGN' => $signature,
            'X-BAPI-TIMESTAMP' => $timestamp,
            'X-BAPI-RECV-WINDOW' => $recvWindow,
            'Content-Type' => 'application/json',
        ];
        
        $url = $this->baseUrl . $endpoint;
        
        if ($method === 'GET') {
            $url .= '?' . $queryString;
            $response = Http::withHeaders($headers)->get($url);
        } else {
            $response = Http::withHeaders($headers)->post($url, $params);
        }
        
        if (!$response->successful()) {
            throw new \Exception('Bybit API error: ' . $response->body());
        }
        
        $data = $response->json();
        
        if (isset($data['retCode']) && $data['retCode'] != 0) {
            throw new \Exception('Bybit API error: ' . ($data['retMsg'] ?? 'Unknown error'));
        }
        
        return $data;
    }
}