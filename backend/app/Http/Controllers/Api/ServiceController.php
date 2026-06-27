<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Service;

class ServiceController extends Controller {
    public function index(): \Illuminate\Http\JsonResponse {
        return response()->json(Service::where('active', true)->orderBy('sort_order')->get());
    }
}
