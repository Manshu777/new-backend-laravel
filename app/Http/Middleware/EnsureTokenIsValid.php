<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\ApiService;

class EnsureTokenIsValid
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Ensure the token is available
        try {
            $this->apiService->getToken(); // This will authenticate if token is expired
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to authenticate'], 401);
        }

        return $next($request);
    }
}
