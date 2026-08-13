<?php

namespace App\Services;

use App\Models\DocumentFileModel;
use App\Models\DocumentsModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentFileService
{
    public function store(DocumentsModel $document, UploadedFile $file): DocumentFileModel
    {
        $path = $file->store('documents/' . $document->document_code, 'private');

        return DocumentFileModel::create([
            'document_id' => $document->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    public function download(DocumentFileModel $file)
    {
        $disk = Storage::disk('private');
        abort_unless($disk->exists($file->file_path), 404);

        $downloadName = preg_replace(
            '/[^\pL\pN._ -]+/u',
            '_',
            (string) ($file->original_name ?: basename($file->file_path))
        ) ?: 'document-file';

        return $disk->download($file->file_path, $downloadName, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
