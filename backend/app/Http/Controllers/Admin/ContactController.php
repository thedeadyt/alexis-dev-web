<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\PdfService;

class ContactController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contacts = Contact::latest()->paginate(20);
        return view('admin.contacts.index', compact('contacts'));
    }

    public function pdf(Contact $contact): \Illuminate\Http\Response
    {
        return (new PdfService())->generateDevis($contact);
    }

    public function destroy(Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $contact->delete();
        return redirect('/admin/contacts')->with('success', 'Demande supprimée.');
    }
}
