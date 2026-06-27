<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Contact, Project, Service, Testimonial};

class DashboardController extends Controller {
    public function index(): \Illuminate\View\View {
        return view('admin.dashboard', [
            'contactsCount'     => Contact::count(),
            'projectsCount'     => Project::count(),
            'servicesCount'     => Service::count(),
            'testimonialsCount' => Testimonial::count(),
            'latestContacts'    => Contact::latest()->take(5)->get(),
        ]);
    }
}
