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
        $this->authToken = config('services.matrix.auth_token', 'b927c65283b74065a1d77e60c47356e0');
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
                $request->attach($file['name'], $file['contents'], $file['filename']);
            }
            $request->withHeaders(['Content-Type' => 'multipart/form-data']);
        } elseif ($method === 'POST' && !empty($data)) {
            $request->asJson()->withBody(json_encode($data), 'application/json');
        }

        $response = $request->$method($this->baseUrl . '/reseller/matrix/' . $endpoint, $data);

        return $response->json();
    }

    /**
     * Get Countries
     */
    public function getCountries()
    {
        $response = $this->makeRequest('get', 'countries-covered');
        return response()->json($response);
    }

    /**
     * Get Plans
     */
    public function getPlans(Request $request)
    {
        $country = $request->query('country_covered', 'Italy');
        $response = $this->makeRequest('get', 'plans?country_covered=' . urlencode($country));
        return response()->json($response);
    }

    /**
     * Validate Order
     */
    public function validateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request' => 'required|array',
            'request.*.id' => 'required|string',
            'request.*.planName' => 'required|string',
            'request.*.customerFirstName' => 'required|string',
            'request.*.customerLastName' => 'required|string',
            'request.*.customerDOB' => 'required|date_format:Y-m-d',
            'request.*.customerPassportNo' => 'required|string',
            'request.*.travelStartDate' => 'required|date_format:Y-m-d',
            'request.*.travelEndDate' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = $this->makeRequest('post', 'validate-order', $request->all());
        return response()->json($response);
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

        $response = $this->makeRequest('post', 'create-order', $request->all());
        return response()->json($response);
    }

    /**
     * Get Orders
     */
    public function getOrders(Request $request)
    {
        $limit = $request->query('limit', 10);
        $page = $request->query('page_no', 1);
        $response = $this->makeRequest('get', "get-orders?limit=$limit&page_no=$page");
        return response()->json($response);
    }

    /**
     * Upload Documents
     */
    public function uploadDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'passportF' => 'required|file|mimes:jpg,png,pdf',
            'passportB' => 'required|file|mimes:jpg,png,pdf',
            'visaCopy1' => 'required|file|mimes:jpg,png,pdf',
            'eTicket1' => 'required|file|mimes:jpg,png,pdf',
            'eTicket2' => 'required|file|mimes:jpg,png,pdf',
            'orderNo' => 'required|string',
            'simNo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $files = [
            ['name' => 'passportF', 'contents' => fopen($request->file('passportF')->path(), 'r'), 'filename' => $request->file('passportF')->getClientOriginalName()],
            ['name' => 'passportB', 'contents' => fopen($request->file('passportB')->path(), 'r'), 'filename' => $request->file('passportB')->getClientOriginalName()],
            ['name' => 'visaCopy1', 'contents' => fopen($request->file('visaCopy1')->path(), 'r'), 'filename' => $request->file('visaCopy1')->getClientOriginalName()],
            ['name' => 'eTicket1', 'contents' => fopen($request->file('eTicket1')->path(), 'r'), 'filename' => $request->file('eTicket1')->getClientOriginalName()],
            ['name' => 'eTicket2', 'contents' => fopen($request->file('eTicket2')->path(), 'r'), 'filename' => $request->file('eTicket2')->getClientOriginalName()],
            ['name' => 'orderNo', 'contents' => $request->input('orderNo')],
            ['name' => 'simNo', 'contents' => $request->input('simNo')],
        ];

        $response = $this->makeRequest('post', 'upload-documents', [], $files);
        return response()->json($response);
    }

    /**
     * Get Wallet Balance
     */
    public function getWalletBalance()
    {
        $response = $this->makeRequest('get', 'wallet-balance');
        return response()->json($response);
    }

    /**
     * Get Mobile Usage
     */
    public function getMobileUsage(Request $request)
    {
        $simNo = $request->query('simNo', '999999999999999');
        $response = $this->makeRequest('get', "get-mobile-usage?simNo=$simNo");
        return response()->json($response);
    }

    /**
     * Get Recharge Plans
     */
    public function getRechargePlans(Request $request)
    {
        $simNo = $request->query('simNo', '999999999999999');
        $response = $this->makeRequest('get', "get-recharge-plans?simNo=$simNo");
        return response()->json($response);
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
        return response()->json($response);
    }

    /**
     * Get eSIM Installation Status
     */
    public function getSimInstallationStatus(Request $request)
    {
        $simNo = $request->query('simNo', '999999999999999');
        $response = $this->makeRequest('get', "get-sim-installation-status?simNo=$simNo");
        return response()->json($response);
    }
}