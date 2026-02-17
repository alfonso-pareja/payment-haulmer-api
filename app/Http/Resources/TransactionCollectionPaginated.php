<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollectionPaginated extends ResourceCollection
{
    public function with(Request $request): array
    {
        return ['success' => true];
    }
}
