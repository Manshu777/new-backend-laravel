<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ApiService;

class ApiController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function getToken()
    {
        try {
            $token = $this->apiService->getToken();
            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
