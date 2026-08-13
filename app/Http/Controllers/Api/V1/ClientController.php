<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Admin\Api\ClientController as LegacyClientController;

class ClientController extends LegacyClientController
{
    // The legacy CRUD is kept as the compatibility implementation for v1.
}
