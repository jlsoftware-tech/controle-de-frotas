<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class PaginatedCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'statusCode' => 200,
            'data' => [
                'items' => $this->collection->map->resolve($request)->all(),
                'pagination' => [
                    'numPerPage' => $this->perPage(),
                    'currPage' => $this->currentPage(),
                    'totalEntries' => $this->total(),
                    'totalPages' => $this->lastPage(),
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $paginated
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    public function paginationInformation($request, $paginated, $default): array
    {
        return [];
    }
}
