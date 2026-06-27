<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller {
    public function index(): \Illuminate\View\View {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::orderBy('sort_order')->get()]);
    }
    public function create(): \Illuminate\View\View {
        return view('admin.testimonials.form', ['testimonial' => null]);
    }
    public function store(Request $request): \Illuminate\Http\RedirectResponse {
        Testimonial::create($this->validated($request));
        return redirect('/admin/testimonials')->with('success', 'Témoignage créé.');
    }
    public function edit(Testimonial $testimonial): \Illuminate\View\View {
        return view('admin.testimonials.form', compact('testimonial'));
    }
    public function update(Request $request, Testimonial $testimonial): \Illuminate\Http\RedirectResponse {
        $testimonial->update($this->validated($request));
        return redirect('/admin/testimonials')->with('success', 'Témoignage mis à jour.');
    }
    public function destroy(Testimonial $testimonial): \Illuminate\Http\RedirectResponse {
        $testimonial->delete();
        return redirect('/admin/testimonials')->with('success', 'Témoignage supprimé.');
    }
    private function validated(Request $request): array {
        return $request->validate([
            'quote'      => 'required|string',
            'author'     => 'required|string|max:100',
            'role'       => 'required|string|max:150',
            'sort_order' => 'required|integer',
        ]) + ['active' => $request->has('active')];
    }
}
