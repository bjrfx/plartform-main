<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginatedResource extends ResourceCollection
{
    /**
     * The resource class to transform each item in the collection.
     */
    protected string $resourceClass;

    /**
     * Paginator instance.
     */
    protected Paginator $paginator;

    public function __construct(Paginator $resource, string $resourceClass)
    {
        $this->resourceClass = $resourceClass;
        $this->paginator = $resource;

        parent::__construct($resource->items());
    }

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->resolveResourceCollection(),
            'pagination' => $this->paginationDetails(),
        ];
    }

    /**
     * Get pagination details.
     */
    protected function paginationDetails(): array
    {
        // Ensure compatibility with both LengthAwarePaginator and SimplePaginator
        $pagination = [
            'current_page' => $this->paginator->currentPage(),
            'from' => $this->paginator->firstItem(),
            'to' => $this->paginator->lastItem(),
            'per_page' => $this->paginator->perPage(),
            'has_next_page' => (bool)$this->paginator->nextPageUrl(),
        ];

        // Add total and last_page only if the paginator is LengthAwarePaginator
        if ($this->paginator instanceof LengthAwarePaginator) {
            $pagination['total'] = $this->paginator->total();
            $pagination['last_page'] = $this->paginator->lastPage();
        }

        return $pagination;
    }

    /**
     * Resolve and instantiate the resource class dynamically.
     */
    protected function resolveResourceCollection()
    {
        return call_user_func([$this->resourceClass, 'collection'], $this->collection);
    }
}
