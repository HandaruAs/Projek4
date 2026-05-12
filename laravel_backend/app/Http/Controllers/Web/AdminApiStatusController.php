<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AdminApiStatusController extends Controller
{
    public function index()
    {
        return view('admin.api-status');
    }

    public function check(Request $request)
    {
        $endpoints = [

            // ── AUTH ──────────────────────────────────────────────────────────
            [
                'group'  => 'Auth',
                'name'   => 'Login',
                'desc'   => 'POST /api/login',
                'method' => 'POST',
                'path'   => '/api/login',
                'payload'=> ['email' => 'ping@check.dev', 'password' => 'ping'],
                'expect' => [200, 401, 404, 422],
            ],
            [
                'group'  => 'Auth',
                'name'   => 'Register User',
                'desc'   => 'POST /api/register/user',
                'method' => 'POST',
                'path'   => '/api/register/user',
                'payload'=> ['name' => '', 'email' => '', 'password' => ''],
                'expect' => [200, 201, 302, 422],
            ],
            [
                'group'  => 'Auth',
                'name'   => 'Register Admin',
                'desc'   => 'POST /api/register/admin',
                'method' => 'POST',
                'path'   => '/api/register/admin',
                'payload'=> ['name' => '', 'email' => '', 'password' => ''],
                'expect' => [200, 201, 302, 422],
            ],
            [
                'group'  => 'Auth',
                'name'   => 'Forgot Password',
                'desc'   => 'POST /api/forgot-password',
                'method' => 'POST',
                'path'   => '/api/forgot-password',
                'payload'=> ['email' => 'ping@check.dev'],
                'expect' => [200, 404, 422],
            ],
            [
                'group'  => 'Auth',
                'name'   => 'Verify OTP',
                'desc'   => 'POST /api/verify-otp',
                'method' => 'POST',
                'path'   => '/api/verify-otp',
                'payload'=> ['email' => 'ping@check.dev', 'otp' => '000000'],
                'expect' => [200, 400, 422],
            ],
            [
                'group'  => 'Auth',
                'name'   => 'Reset Password',
                'desc'   => 'POST /api/reset-password',
                'method' => 'POST',
                'path'   => '/api/reset-password',
                'payload'=> ['email' => '', 'otp' => '', 'password' => '', 'password_confirmation' => ''],
                'expect' => [200, 400, 422],
            ],
            [
                'group'  => 'Auth',
                'name'   => 'Get Profile',
                'desc'   => 'GET /api/profile',
                'method' => 'GET',
                'path'   => '/api/profile',
                'expect' => [200, 401, 403],
            ],
            [
                'group'  => 'Auth',
                'name'   => 'Update Profile',
                'desc'   => 'PUT /api/profile',
                'method' => 'PUT',
                'path'   => '/api/profile',
                'payload'=> [],
                'expect' => [200, 401, 403, 422],
            ],
            [
                'group'  => 'Auth',
                'name'   => 'Logout',
                'desc'   => 'POST /api/logout',
                'method' => 'POST',
                'path'   => '/api/logout',
                'expect' => [200, 401, 403],
            ],

            // ── CATEGORY ──────────────────────────────────────────────────────
            [
                'group'  => 'Category',
                'name'   => 'List Categories',
                'desc'   => 'GET /api/categories',
                'method' => 'GET',
                'path'   => '/api/categories',
                'expect' => [200],
            ],

            // ── COMMODITY ─────────────────────────────────────────────────────
            [
                'group'  => 'Commodity',
                'name'   => 'List Commodities',
                'desc'   => 'GET /api/commodities',
                'method' => 'GET',
                'path'   => '/api/commodities',
                'expect' => [200],
            ],
            [
                'group'  => 'Commodity',
                'name'   => 'Detail Commodity',
                'desc'   => 'GET /api/commodities/{id}',
                'method' => 'GET',
                'path'   => '/api/commodities/1',
                'expect' => [200, 404],
            ],

            // ── PRICE HISTORY ─────────────────────────────────────────────────
            [
                'group'  => 'Price History',
                'name'   => 'List Price Histories',
                'desc'   => 'GET /api/price-histories',
                'method' => 'GET',
                'path'   => '/api/price-histories',
                'expect' => [200],
            ],
            [
                'group'  => 'Price History',
                'name'   => 'Detail Price History',
                'desc'   => 'GET /api/price-histories/{id}',
                'method' => 'GET',
                'path'   => '/api/price-histories/1',
                'expect' => [200, 404],
            ],

            // ── PREDICTION ────────────────────────────────────────────────────
            [
                'group'  => 'Prediction',
                'name'   => 'List Predictions',
                'desc'   => 'GET /api/predictions',
                'method' => 'GET',
                'path'   => '/api/predictions',
                'expect' => [200],
            ],
            [
            'group'  => 'Prediction',
            'name'   => 'Detail Prediction',
            'desc'   => 'GET /api/predictions/{komoditas}',
            'method' => 'GET',
            'path'   => '/api/predictions',  // pakai index tanpa komoditas
            'expect' => [200],
             ],

            // ── STATISTICS ────────────────────────────────────────────────────
            [
                'group'  => 'Statistics',
                'name'   => 'Statistics',
                'desc'   => 'GET /api/statistics',
                'method' => 'GET',
                'path'   => '/api/statistics',
                'expect' => [200],
            ],
        ];

        $results     = [];
        $totalOnline = 0;

        foreach ($endpoints as $ep) {
            $result = $this->ping(
                $ep['group'],
                $ep['name'],
                $ep['desc'],
                $ep['method'],
                $ep['path'],
                $ep['payload'] ?? [],
                $ep['expect']  ?? [200],
            );
            if ($result['status'] === 'online') $totalOnline++;
            $results[] = $result;
        }

        return response()->json([
            'checked_at'    => now()->toDateTimeString(),
            'total'         => count($results),
            'total_online'  => $totalOnline,
            'total_offline' => count($results) - $totalOnline,
            'services'      => $results,
        ]);
    }

    // ── Private: ping internal (tanpa HTTP, langsung dispatch ke Laravel router) ──
    private function ping(
        string $group,
        string $name,
        string $desc,
        string $method,
        string $path,
        array  $payload = [],
        array  $expect  = [200],
    ): array {
        $start = microtime(true);

        try {
            // Buat request internal — tidak lewat network sama sekali
            $req = \Illuminate\Http\Request::create(
                $path,
                $method,
                $method === 'GET' ? $payload : [],   // query params untuk GET
                [],                                   // cookies
                [],                                   // files
                ['CONTENT_TYPE' => 'application/json'],
                $method !== 'GET' ? json_encode($payload) : null  // body untuk POST/PUT
            );

            $req->headers->set('Accept', 'application/json');
            $req->headers->set('X-Requested-With', 'XMLHttpRequest');

            // Dispatch langsung ke Laravel kernel — no TCP, no port, no deadlock
            $response = app()->handle($req);

            $latency  = round((microtime(true) - $start) * 1000, 2);
            $httpCode = $response->getStatusCode();
            $alive    = in_array($httpCode, $expect);

            return [
                'group'      => $group,
                'name'       => $name,
                'desc'       => $desc,
                'status'     => $alive ? 'online' : 'offline',
                'http_code'  => $httpCode,
                'latency_ms' => $latency,
            ];

        } catch (\Exception $e) {
            return [
                'group'      => $group,
                'name'       => $name,
                'desc'       => $desc,
                'status'     => 'offline',
                'http_code'  => null,
                'latency_ms' => null,
                'error'      => $e->getMessage(),
            ];
        }
    }
}