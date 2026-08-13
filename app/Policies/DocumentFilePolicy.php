<?php

namespace App\Policies;

use App\Models\DocumentFileModel;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DocumentFilePolicy
{
    public function view(User $user, DocumentFileModel $documentFile): bool
    {
        $document = $documentFile->document;

        return $document !== null && Gate::forUser($user)->allows('view', $document);
    }
}
