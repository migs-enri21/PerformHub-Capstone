<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class SupabaseStorageService
{
    public function upload(UploadedFile $file, string $bucket, string $type, int $userId): string {

        $url = rtrim(Config::get('services.supabase.url'), '/');
        $key = Config::get('services.supabase.service_key');

        $folders = [
            'government_id'      => 'government-ids',
            'business_permit'    => 'business-permits',
            'proof_of_events'    => 'proof-of-events',
            'bir_certificate'    => 'bir-certificates',
            'performance_sample' => 'performance-videos',
            'profile_picture'    => 'profile-pictures',
            'portfolio_image'    => 'portfolio-images',
            'portfolio_video'    => 'portfolio-videos',
            'banner_photo'       => 'banner-photos',
            'certificate'        => 'certificates',
            'organization_logo'  => 'organization-logos',
            'event_banner'       => 'event-banners',
        ];

        $folder = $folders[$type] ?? $type;

        $filename = time() . '_' . $file->getClientOriginalName();

        $path = $folder . '/' . $userId . '/' . $filename;

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'apikey'        => $key,
            'x-upsert'      => 'true',
            'Content-Type'  => $file->getMimeType(),
        ])
        ->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
        ->post("{$url}/storage/v1/object/{$bucket}/{$path}");


        return $path;
    }
}