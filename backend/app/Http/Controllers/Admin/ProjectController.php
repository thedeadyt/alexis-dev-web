<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller {
    public function index(): \Illuminate\View\View {
        return view('admin.projects.index', ['projects' => Project::orderBy('sort_order')->get()]);
    }

    public function create(): \Illuminate\View\View {
        return view('admin.projects.form', ['project' => null]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse {
        $data = $this->validated($request);
        Project::create($data);
        return redirect('/admin/projects')->with('success', 'Projet créé.');
    }

    public function edit(Project $project): \Illuminate\View\View {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project): \Illuminate\Http\RedirectResponse {
        $data = $this->validated($request);
        $project->update($data);
        return redirect('/admin/projects')->with('success', 'Projet mis à jour.');
    }

    public function destroy(Project $project): \Illuminate\Http\RedirectResponse {
        $project->delete();
        return redirect('/admin/projects')->with('success', 'Projet supprimé.');
    }

    private function validated(Request $request): array {
        $data = $request->validate([
            'slug'       => 'required|string|max:100',
            'name'       => 'required|string|max:200',
            'client'     => 'required|string|max:100',
            'category'   => 'required|in:Sites vitrines,Applications,Infra',
            'year'       => 'required|digits:4',
            'summary'    => 'required|string',
            'full_text'  => 'required|string',
            'tech'       => 'required|string',
            'rendered'   => 'required|string',
            'sort_order' => 'required|integer',
        ]);

        // Convert newline-separated strings to arrays for JSON columns
        $data['full_text'] = array_values(array_filter(array_map('trim', explode("\n", $data['full_text']))));
        $data['tech']      = array_values(array_filter(array_map('trim', explode("\n", $data['tech']))));
        $data['rendered']  = array_values(array_filter(array_map('trim', explode("\n", $data['rendered']))));
        $data['active']    = $request->has('active');

        return $data;
    }
}
