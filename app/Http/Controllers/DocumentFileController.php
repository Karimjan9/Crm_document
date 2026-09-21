<?php

namespace App\Http\Controllers;

use App\Models\DocumentFileModel;
use App\Services\DocumentFileService;

class DocumentFileController extends Controller
{
    public function __construct(private readonly DocumentFileService $files) {}

    public function show(DocumentFileModel $documentFile)
    {
        $documentFile->loadMissing('document.courierAssignment');
        $this->authorize('view', $documentFile);
        $document = $documentFile->document;

        return $this->files->download($documentFile, request()->user());
    }
}
