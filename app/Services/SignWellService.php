<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SignWellService
{
    public function isConfigured(): bool
    {
        return filled(config('services.signwell.api_key'));
    }

    public function sendContractForSignature(Booking $booking): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('SignWell is not configured yet.');
        }

        if (! $booking->hasContract()) {
            throw new RuntimeException('Upload a contract before sending it for signature.');
        }

        $booking->loadMissing('performer');

        if (! $booking->performer || ! $booking->performer->email) {
            throw new RuntimeException('The performer does not have an email address.');
        }

        $payload = [
            'test_mode' => filter_var(config('services.signwell.test_mode'), FILTER_VALIDATE_BOOLEAN),
            'name' => $booking->event_name.' Contract',
            'subject' => 'Please sign your contract for '.$booking->event_name,
            'message' => 'Please review and sign this contract through SignWell.',
            'files' => [[
                'name' => 'contract.pdf',
                'file_url' => $booking->contractUrl(),
            ]],
            'recipients' => [[
                'id' => '1',
                'name' => $booking->performer->fullName(),
                'email' => $booking->performer->email,
                'delivery_method' => 'email',
                'send_email' => false,
            ]],
            'with_signature_page' => true,
            'embedded_signing' => true,
            'embedded_signing_notifications' => false,
            'reminders' => false,
            'draft' => false,
            'metadata' => [
                'booking_id' => (string) $booking->id,
            ],
        ];

        $response = Http::acceptJson()
            ->withHeaders(['X-Api-Key' => config('services.signwell.api_key')])
            ->timeout(30)
            ->post('https://www.signwell.com/api/v1/documents', $payload);

        if ($response->failed()) {
            throw new RuntimeException('SignWell could not send the contract: '.$response->body());
        }

        $document = $response->json();

        if (! isset($document['id'])) {
            throw new RuntimeException('SignWell did not return a document ID.');
        }

        $signWellStatus = 'sent';

        if (isset($document['status'])) {
            $signWellStatus = $document['status'];
        }

        $signingUrl = null;

        if (isset($document['recipients'][0]['embedded_signing_url'])) {
            $signingUrl = $document['recipients'][0]['embedded_signing_url'];
        }

        $booking->update([
            'signwell_document_id' => $document['id'],
            'signwell_status' => $signWellStatus,
            'signwell_signing_url' => $signingUrl,
            'signwell_sent_at' => now(),
        ]);
    }

    public function signingUrl(Booking $booking): ?string
    {
        if (! $this->isConfigured() || ! $booking->signwell_document_id) {
            return null;
        }

        $documentUrl = 'https://www.signwell.com/api/v1/documents/'.$booking->signwell_document_id;
        $response = Http::acceptJson()
            ->withHeaders(['X-Api-Key' => config('services.signwell.api_key')])
            ->timeout(30)
            ->get($documentUrl);

        if ($response->failed()) {
            throw new RuntimeException('SignWell signing screen could not be loaded: '.$response->body());
        }

        $document = $response->json();
        $signingUrl = null;

        if (isset($document['recipients'][0]['embedded_signing_url'])) {
            $signingUrl = $document['recipients'][0]['embedded_signing_url'];
        }

        if ($signingUrl) {
            $booking->update(['signwell_signing_url' => $signingUrl]);
        }

        return $signingUrl;
    }

    public function syncStatus(Booking $booking): bool
    {
        if (! $this->isConfigured() || ! $booking->signwell_document_id) {
            return false;
        }

        $documentUrl = 'https://www.signwell.com/api/v1/documents/'.$booking->signwell_document_id;
        $response = Http::acceptJson()
            ->withHeaders(['X-Api-Key' => config('services.signwell.api_key')])
            ->timeout(30)
            ->get($documentUrl);

        if ($response->failed()) {
            throw new RuntimeException('SignWell status check failed: '.$response->body());
        }

        $document = $response->json();
        $status = 'sent';

        if (isset($document['status'])) {
            $status = $document['status'];
        }
        $booking->update(['signwell_status' => $status]);

        if ($status !== 'completed' || $booking->hasSignedContract()) {
            return false;
        }

        $pdfResponse = Http::withHeaders([
            'X-Api-Key' => config('services.signwell.api_key'),
        ])->timeout(30)
            ->get($documentUrl.'/completed_pdf');

        if ($pdfResponse->failed()) {
            throw new RuntimeException('SignWell signed PDF download failed: '.$pdfResponse->body());
        }

        $storage = new SupabaseStorageService();
        $path = $storage->uploadContents(
            $pdfResponse->body(),
            'booking-'.$booking->id.'-signed.pdf',
            'organizer-files',
            'signed_contract',
            $booking->performer_id,
            'application/pdf'
        );

        $booking->update([
            'signed_contract_path' => $path,
            'signed_contract_uploaded_at' => now(),
            'signwell_completed_at' => now(),
        ]);

        return true;
    }
}
