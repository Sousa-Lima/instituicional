<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiRootController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => 'SLC API',
            'status' => 'ok',
            'docs' => '/api/documentation',
            'openapi' => '/docs',
            'panel' => '/admin',
        ]);
    }
}
