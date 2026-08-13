<?php

namespace App\Services;

use App\Models\DocumentsModel;

class DocumentCreationService
{
    public function create(array $attributes): DocumentsModel
    {
        return DocumentsModel::create($attributes);
    }
}
