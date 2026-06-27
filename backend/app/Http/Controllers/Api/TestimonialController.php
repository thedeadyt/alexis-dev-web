<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;

class TestimonialController extends Controller {
    public function index(): \Illuminate\Http\JsonResponse {
        return response()->json(Testimonial::where('active', true)->orderBy('sort_order')->get());
    }
}
