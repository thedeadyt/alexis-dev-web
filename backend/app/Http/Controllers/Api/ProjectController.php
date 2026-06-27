<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller {
    public function index(): \Illuminate\Http\JsonResponse {
        $query = Project::where('active', true)->orderBy('sort_order');
        if (request('category') && request('category') !== 'Tous') {
            $query->where('category', request('category'));
        }
        return response()->json($query->get());
    }

    public function show(string $slug): \Illuminate\Http\JsonResponse {
        $project = Project::where('slug', $slug)->where('active', true)->firstOrFail();
        return response()->json($project);
    }
}
