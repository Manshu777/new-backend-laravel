<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MatrixController extends Controller
{
    protected $baseUrl;
    protected $authToken;

    public function __construct()
    {
        $this->baseUrl = config('services.matrix.base_url', 'https://azcms.matrix.co.in');
        $this->authToken = config('services.matrix.auth_token','b927c65283b74065a1d77e60c47356e0');
    }

    /**
     * Make an HTTP request to the Matrix API
     */
    protected function makeRequest($method, $endpoint, $data = [], $files = [])
    {
        $request = Http::withHeaders([
            'Auth-Token' => $this->authToken,
        ]);

        if (!empty($files)) {
            foreach ($files as $file) {
                if (isset($file['contents'])) {
                    $request->attach($file['name'], $file['contents'], $file['filename']);
                } else {
                    $request->attach($file['name'], $file['value']);
                }
            }
        } elseif ($method === 'POST' && !empty($data)) {
            $jsonData = json_encode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 0,
                    'message' => 'JSON encoding failed: ' . json_last_error_msg(),
                    'data' => [],
                    'http_status' => 400,
                ];
            }
            $request->withHeaders(['Content-Type' => 'application/json']);
            $request->withBody($jsonData, 'application/json');
        }

        try {
            $response = $request->$method($this->baseUrl . '/reseller/matrix/' . $endpoint);
            $responseData = $response->json();

            if ($response->status() !== 200 || !isset($responseData['status']) || $responseData['status'] === 0) {
                return [
                    'status' => 0,
                    'message' => $responseData['message'] ?? 'API request failed',
                    'data' => $responseData['data'] ?? [],
                    'http_status' => $response->status(),
                ];
            }

            return $responseData;
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'data' => [],
                'http_status' => 500,
            ];
        }
    }

    /**
     * Get Countries Covered
     */
    public function getCountries()
    {
        $response = $this->makeRequest('get', 'countries-covered');
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Get Plans
     */
    public function getPlans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_covered' => 'sometimes|string',
            'limit' => 'sometimes|integer|min:1',
            'page_no' => 'sometimes|integer|min:1',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $query = http_build_query($request->only(['country_covered', 'limit', 'page_no']));
        $endpoint = 'plans' . ($query ? '?' . $query : '');
        $response = $this->makeRequest('get', $endpoint);
    
        if ($response['status'] === 1) {
            $response['data'] = array_map(function ($plan) {
                return [
                    'id' => $plan['id'] ?? '',
                    'planName' => $plan['planName'] ?? $plan['productName'] ?? '',
                    'dataCapacity' => $plan['dataCapacity'] ?? 0,
                    'dataCapacityUnit' => $plan['dataCapacityUnit'] ?? '',
                    'validity' => $plan['validity'] ?? 0,
                    'totalPrice' => $plan['totalPrice'] ?? 0,
                    'currency' => $plan['currency'] ?? '',
                    'coverages' => $plan['coverages'] ?? '',
                    'isRechargeable' => $plan['isRechargeable'] ?? false,
                    'description' => $plan['description'] ?? '',
                ];
            }, $response['data']);
        }
    
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Validate Order
     */
    public function validateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request' => 'required|array|min:1',
            'request.*.id' => 'required|string',
            'request.*.planName' => 'required|string',
            'request.*.customerFirstName' => 'required|string',
            'request.*.customerLastName' => 'required|string',
            'request.*.customerDOB' => 'required|date_format:Y-m-d',
            'request.*.customerPassportNo' => 'required|string',
            'request.*.travelStartDate' => 'required|date_format:Y-m-d|after_or_equal:today',
            'request.*.travelEndDate' => 'required|date_format:Y-m-d',
        ], [
            'request.*.travelEndDate.after_or_equal' => 'The travel end date must be on or after the travel start date.',
        ]);

        foreach ($request->input('request', []) as $index => $order) {
            $validator->sometimes("request.$index.travelEndDate", 'after_or_equal:request.*.travelStartDate', function () use ($order) {
                return isset($order['travelStartDate']);
            });
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = $this->makeRequest('post', 'validate-order', $request->all());
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Create Order
     */
    public function createOrder(Request $request)
     {
    $validator = Validator::make($request->all(), [
        'validatedOrderId' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Check wallet balance
    $walletResponse = $this->makeRequest('get', 'wallet-balance');
    if ($walletResponse['status'] !== 1 || !isset($walletResponse['data']['balance'])) {
        return response()->json([
            'status' => 0,
            'message' => 'Unable to verify wallet balance',
            'data' => [],
            'http_status' => 400,
        ], 400);
    }

    // Assuming the API returns balance in 'data.balance' (adjust based on actual response)
    $balance = $walletResponse['data']['balance'];
    // Fetch order cost from validate-order response or pass it in the request
    $orderCost = $request->input('orderCost'); // Add orderCost to request or fetch from validate-order
    if ($orderCost && $balance < $orderCost) {
        return response()->json([
            'status' => 0,
            'message' => 'Insufficient wallet balance',
            'data' => [],
            'http_status' => 400,
        ], 400);
    }

    $response = $this->makeRequest('post', 'create-order', $request->all());

    if ($response['status'] === 1 && isset($response['orderDetail']) && is_array($response['orderDetail']) && !empty($response['orderDetail'])) {
        $response['orderNo'] = $response['orderId'] ?? '';
        $response['simNo'] = $response['orderDetail'][0]['simNumber'] ?? '';
    }

    return response()->json($response, $response['http_status'] ?? 200);
}
    /**
     * Get Orders
     */
    public function getOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'sometimes|integer|min:1',
            'page_no' => 'sometimes|integer|min:1',
            'order_id' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $params = [];
        if ($request->has('limit')) {
            $params[] = 'limit=' . $request->query('limit');
        }
        if ($request->has('page_no')) {
            $params[] = 'page_no=' . $request->query('page_no');
        }
        if ($request->has('order_id')) {
            $params[] = 'order_id=' . urlencode($request->query('order_id'));
        }

        $endpoint = 'get-orders' . (empty($params) ? '' : '?' . implode('&', $params));
        $response = $this->makeRequest('get', $endpoint);
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Upload Documents
     */
    public function uploadDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'passportF' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'passportB' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'visaCopy1' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'visaCopy2' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'eTicket1' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'eTicket2' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'orderNo' => 'required|string',
            'simNo' => 'required|string',
            'mobileNo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $files = [
            ['name' => 'orderNo', 'value' => $request->input('orderNo')],
            ['name' => 'simNo', 'value' => $request->input('simNo')],
            ['name' => 'mobileNo', 'value' => $request->input('mobileNo')],
        ];

        foreach (['passportF', 'passportB', 'visaCopy1', 'visaCopy2', 'eTicket1', 'eTicket2'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $file = $request->file($fileField);
                $files[] = [
                    'name' => $fileField,
                    'contents' => file_get_contents($file->path()),
                    'filename' => $file->getClientOriginalName(),
                ];
            }
        }

        $response = $this->makeRequest('post', 'upload-documents', [], $files);
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Get Wallet Balance
     */
    public function getWalletBalance()
    {
        $response = $this->makeRequest('get', 'wallet-balance');
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Get Mobile Usage
     */
    public function getMobileUsage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'simNo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $endpoint = 'get-mobile-usage?simNo=' . urlencode($request->query('simNo'));
        $response = $this->makeRequest('get', $endpoint);
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Get Recharge Plans
     */
    public function getRechargePlans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'simNo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $endpoint = 'get-recharge-plans?simNo=' . urlencode($request->query('simNo'));
        $response = $this->makeRequest('get', $endpoint);
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Create Recharge
     */
    public function createRecharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'simNo' => 'required|string',
            'planId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = $this->makeRequest('post', 'create-recharge', $request->all());
        return response()->json($response, $response['http_status'] ?? 200);
    }

    /**
     * Get eSIM Installation Status
     */
    public function getSimInstallationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'simNo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $endpoint = 'get-sim-installation-status?simNo=' . urlencode($request->query('simNo'));
        $response = $this->makeRequest('get', $endpoint);
        return response()->json($response, $response['http_status'] ?? 200);
    }
}