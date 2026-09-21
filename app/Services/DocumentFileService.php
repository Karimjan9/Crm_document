<?php

namespace App\Services;

use App\Models\DocumentFileModel;
use App\Models\DocumentsModel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentFileService
{
    public function __construct(
        private readonly FileSecurityService $security,
        private readonly WatermarkedDownloadService $downloads,
    ) {}

    public function store(DocumentsModel $document, UploadedFile $file): DocumentFileModel
    {
        $path = $this->security->store($file, 'documents/'.$document->document_code);

        return DocumentFileModel::create([
            'document_id' => $document->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    public function download(DocumentFileModel $file, ?User $user = null)
    {
        $disk = Storage::disk('private');
        abort_unless($disk->exists($file->file_path), 404);

        $downloadName = preg_replace(
            '/[^\pL\pN._ -]+/u',
            '_',
            (string) ($file->original_name ?: basename($file->file_path))
        ) ?: 'document-file';

        return $this->downloads->download($file->file_path, $downloadName, $user);
    }
}
