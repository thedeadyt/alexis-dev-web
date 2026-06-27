<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller {
    public function index(): \Illuminate\View\View {
        return view('admin.services.index', ['services' => Service::orderBy('sort_order')->get()]);
    }
    public function create(): \Illuminate\View\View {
        return view('admin.services.form', ['service' => null]);
    }
    public function store(Request $request): \Illuminate\Http\RedirectResponse {
        Service::create($this->validated($request));
        return redirect('/admin/services')->with('success', 'Service créé.');
    }
    public function edit(Service $service): \Illuminate\View\View {
        return view('admin.services.form', compact('service'));
    }
    public function update(Request $request, Service $service): \Illuminate\Http\RedirectResponse {
        $service->update($this->validated($request));
        return redirect('/admin/services')->with('success', 'Service mis à jour.');
    }
    public function destroy(Service $service): \Illuminate\Http\RedirectResponse {
        $service->delete();
        return redirect('/admin/services')->with('success', 'Service supprimé.');
    }
    private function validated(Request $request): array {
        $data = $request->validate([
            'slug'       => 'required|string|max:100',
            'label'      => 'required|string|max:50',
            'title'      => 'required|string|max:200',
            'sub'        => 'required|string|max:200',
            'body'       => 'required|string',
            'tags'       => 'required|string',
            'price'      => 'required|string|max:100',
            'sort_order' => 'required|integer',
        ]);
        // Convert comma-separated string to array for JSON column
        $data['tags']   = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
        $data['active'] = $request->has('active');
        return $data;
    }
}
