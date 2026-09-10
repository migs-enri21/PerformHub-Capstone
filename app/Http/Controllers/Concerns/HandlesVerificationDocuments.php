<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use App\Models\VerificationDocument;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

trait HandlesVerificationDocuments
{
    protected function storeVerificationDocument(User $user, string $type, UploadedFile $file, array $meta = [], bool $replace = true): void
    {
        if ($replace) {
            $existing = $user->verificationDocuments()->where('document_type', $type)->first();

            if ($existing) {
                $existing->delete();
            }
        }

        $supabase = new SupabaseStorageService();

        $bucket = $user->isPerformer() ? 'performer-files' : 'organizer-files';

        $path = $supabase->upload($file, $bucket, $type, $user->id);

        VerificationDocument::create([
            'user_id' => $user->id,
            'document_type' => $type,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'government_id_type' => $meta['government_id_type'] ?? null,
            'government_id_other' => $meta['government_id_other'] ?? null,
        ]);
    }

    /**
     * @param  array<int, UploadedFile|null>  $files
     */
    protected function storeVerificationDocuments(User $user, string $type, array $files, array $meta = []): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->storeVerificationDocument($user, $type, $file, $meta, false);
            }
        }
    }

    /**
     * @return array<int, UploadedFile>
     */
    protected function uploadedFiles(Request $request, string $key): array
    {
        $files = $request->file($key);

        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));
    }
}
