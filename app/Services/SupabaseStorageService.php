<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;

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
            'contract'           => 'contracts',
        ];

        $folder = $folders[$type] ?? $type;

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '-', $file->getClientOriginalName());
        $filename = time() . '_' . $safeName;

        $path = $folder . '/' . $userId . '/' . $filename;

        // Stream the file instead of reading it fully into memory — matters now
        // that uploads can be up to the Supabase project's 500 MB size ceiling.
        $stream = fopen($file->getRealPath(), 'r');

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $key,
                'apikey'        => $key,
                'x-upsert'      => 'true',
                'Content-Type'  => $file->getMimeType(),
            ])
            ->withBody($stream, $file->getMimeType())
            ->post("{$url}/storage/v1/object/{$bucket}/{$path}");
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($response->failed()) {
            throw new RuntimeException(
                "Supabase upload failed for {$bucket}/{$path}: ".$response->body()
            );
        }

        return $path;
    }

    public function url(string $bucket, string $path): string
    {
        $url = rtrim(Config::get('services.supabase.url'), '/');

        return "{$url}/storage/v1/object/public/{$bucket}/{$path}";
    }

    public function delete(string $bucket, string $path): void
    {
        $url = rtrim(Config::get('services.supabase.url'), '/');
        $key = Config::get('services.supabase.service_key');

        Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer '.$key,
            'apikey'        => $key,
        ])->delete("{$url}/storage/v1/object/{$bucket}/{$path}");
    }
}