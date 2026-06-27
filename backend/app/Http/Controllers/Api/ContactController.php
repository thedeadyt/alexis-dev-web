<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\MailService;
use Illuminate\Http\Request;

class ContactController extends Controller {
    public function store(Request $request): \Illuminate\Http\JsonResponse {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email',
            'phone'      => 'nullable|string|max:20',
            'type'       => 'nullable|string',
            'budget'     => 'nullable|string',
            'message'    => 'nullable|string|max:2000',
        ]);

        $contact = Contact::create($data);

        try {
            (new MailService())->sendContactNotification($data);
        } catch (\Exception $e) {
            \Log::error('Mail contact failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
