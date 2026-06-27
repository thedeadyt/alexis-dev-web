<?php
namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function generateDevis(\App\Models\Contact $contact): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('pdf.devis', ['contact' => $contact]);
        $filename = 'devis-' . $contact->id . '-' . str($contact->last_name)->slug() . '.pdf';
        return $pdf->download($filename);
    }
}
