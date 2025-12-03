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
            : 'https://api-demo.bybit.com';
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

    public function placeOrder($symbol, $side, $qty, $orderType = 'Market', $price = null, $stopLoss = null, $takeProfit = null, $leverage = null)
    {
        try {
            // Set leverage BEFORE placing order if specified
            if ($leverage !== null) {
                $this->setLeverage($symbol, $leverage);
            }
            
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
    
    public function setLeverage($symbol, $leverage)
    {
        try {
            // If leverage is 'Max', get the maximum available leverage for the symbol
            if (strtolower($leverage) === 'max') {
                $leverage = $this->getMaxLeverage($symbol);
            }
            
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
                'buyLeverage' => (string) $leverage,
                'sellLeverage' => (string) $leverage,
            ];
            
            try {
                $response = $this->signedRequest('POST', '/v5/position/set-leverage', $params, $timestamp);
                Log::info("Leverage set for {$symbol}: {$leverage}x");
                return $response['result'] ?? null;
            } catch (\Exception $e) {
                // If leverage is already set to this value, don't treat it as an error
                if (strpos($e->getMessage(), 'leverage not modified') !== false) {
                    Log::info("Leverage already set for {$symbol}: {$leverage}x (not modified)");
                    return null; // Not an error, just already set
                }
                // Re-throw other errors
                throw $e;
            }
        } catch (\Exception $e) {
            // Only log and throw if it's NOT a "leverage not modified" error
            if (strpos($e->getMessage(), 'leverage not modified') === false) {
                Log::error("Failed to set leverage for {$symbol}: " . $e->getMessage());
                throw $e;
            }
            // If it's "leverage not modified", just return null (success)
            return null;
        }
    }
    
    public function getMaxLeverage($symbol)
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
            ];
            
            $response = $this->signedRequest('GET', '/v5/market/instruments-info', $params, $timestamp);
            
            if (isset($response['result']['list'][0]['leverageFilter']['maxLeverage'])) {
                $maxLeverage = (float) $response['result']['list'][0]['leverageFilter']['maxLeverage'];
                Log::info("Max leverage for {$symbol}: {$maxLeverage}x");
                return $maxLeverage;
            }
            
            // Default to 10x if unable to fetch
            Log::warning("Could not fetch max leverage for {$symbol}, defaulting to 10x");
            return 10;
        } catch (\Exception $e) {
            Log::error("Failed to get max leverage for {$symbol}: " . $e->getMessage());
            return 10; // Safe default
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
            
            // Get unrealized P&L before closing
            $unrealizedPnl = (float) ($position['unrealisedPnl'] ?? 0);
            
            // Reverse side to close position
            $closeSide = $side === 'Buy' ? 'Sell' : 'Buy';
            
            $closeResult = $this->placeOrder($symbol, $closeSide, $qty, 'Market');
            
            // Return close info including P&L
            return [
                'result' => $closeResult,
                'unrealized_pnl' => $unrealizedPnl,
                'qty' => $qty,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to close position: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getClosedPnL($symbol)
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
                'limit' => 1,
            ];
            
            $response = $this->signedRequest('GET', '/v5/position/closed-pnl', $params, $timestamp);
            
            if (isset($response['result']['list'][0])) {
                $closedPnl = $response['result']['list'][0];
                return (float) ($closedPnl['closedPnl'] ?? 0);
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to get closed P&L: ' . $e->getMessage());
            return 0; // Return 0 instead of throwing to not break the flow
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

    /**
     * Get order status from Bybit (real-time orders)
     * 
     * @param string $symbol
     * @param string $orderId
     * @return array|null Order details with status
     */
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
            
            if (isset($response['result']['list'][0])) {
                return $response['result']['list'][0];
            }
            
            // If not found in real-time orders, check history
            Log::debug("Order {$orderId} not found in real-time orders, checking history...");
            return $this->getOrderHistory($symbol, $orderId);
            
        } catch (\Exception $e) {
            Log::error("Failed to get order status for {$orderId}: " . $e->getMessage());
            
            // Try history as fallback
            try {
                return $this->getOrderHistory($symbol, $orderId);
            } catch (\Exception $historyError) {
                Log::error("Failed to get order from history: " . $historyError->getMessage());
                return null;
            }
        }
    }

    /**
     * Get order history (completed orders)
     * 
     * @param string $symbol
     * @param string $orderId
     * @return array|null Order details from history
     */
    public function getOrderHistory($symbol, $orderId)
    {
        try {
            $timestamp = round(microtime(true) * 1000);
            $params = [
                'category' => 'linear',
                'symbol' => $symbol,
                'orderId' => $orderId,
            ];
            
            $response = $this->signedRequest('GET', '/v5/order/history', $params, $timestamp);
            
            if (isset($response['result']['list'][0])) {
                return $response['result']['list'][0];
            }
            
            Log::warning("Order {$orderId} not found in history for symbol {$symbol}");
            return null;
            
        } catch (\Exception $e) {
            Log::error("Failed to get order history for {$orderId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get actual execution price for an order
     * This fetches the avgPrice (average fill price) from Bybit
     * 
     * @param string $symbol
     * @param string $orderId
     * @return float|null
     */
    public function getExecutionPrice($symbol, $orderId)
    {
        try {
            $orderStatus = $this->getOrderStatus($symbol, $orderId);
            
            if (!$orderStatus) {
                Log::warning("Could not get order status for order {$orderId}");
                return null;
            }

            // avgPrice is the actual average execution price
            $executionPrice = $orderStatus['avgPrice'] ?? null;
            
            if ($executionPrice && $executionPrice > 0) {
                Log::info("Execution price for order {$orderId}: {$executionPrice}");
                return (float) $executionPrice;
            }
            
            // If avgPrice is 0, order might not be filled yet
            $orderStatus_status = $orderStatus['orderStatus'] ?? 'Unknown';
            
            if ($orderStatus_status === 'Filled') {
                // For filled orders, avgPrice should be available
                Log::warning("Order {$orderId} is Filled but avgPrice is 0 or missing");
            } else {
                Log::info("Order {$orderId} status: {$orderStatus_status} - not filled yet");
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Failed to get execution price for order {$orderId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Wait for order to fill and get execution price
     * Polls order status up to maxAttempts times with delay between attempts
     * 
     * @param string $symbol
     * @param string $orderId
     * @param int $maxAttempts
     * @param int $delayMs Delay in milliseconds between attempts
     * @return float|null
     */
    public function waitForOrderFillAndGetPrice($symbol, $orderId, $maxAttempts = 10, $delayMs = 500)
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $executionPrice = $this->getExecutionPrice($symbol, $orderId);
            
            if ($executionPrice !== null && $executionPrice > 0) {
                Log::info("Order {$orderId} filled on attempt " . ($i + 1) . " with price: {$executionPrice}");
                return $executionPrice;
            }
            
            if ($i < $maxAttempts - 1) {
                // Sleep before next attempt (convert ms to microseconds)
                usleep($delayMs * 1000);
            }
        }
        
        Log::warning("Order {$orderId} did not fill after {$maxAttempts} attempts");
        return null;
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
        
        // Check for Bybit API errors
        if (isset($data['retCode']) && $data['retCode'] != 0) {
            $errorMsg = $data['retMsg'] ?? 'Unknown error';
            $errorCode = $data['retCode'];
            
            // Don't throw exception for non-critical errors
            $nonCriticalErrors = [
                'leverage not modified',
                'position idx not match position mode',
            ];
            
            foreach ($nonCriticalErrors as $nonCritical) {
                if (stripos($errorMsg, $nonCritical) !== false) {
                    Log::warning("Bybit API warning (code {$errorCode}): {$errorMsg}");
                    return $data; // Return data instead of throwing
                }
            }
            
            // Throw exception for actual errors
            throw new \Exception("Bybit API error (code {$errorCode}): {$errorMsg}");
        }
        
        return $data;
    }
}