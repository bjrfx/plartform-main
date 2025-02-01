<?php /** @noinspection PhpUnused */

namespace App\QueryBuilders;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PaginationBuilder
{
    protected Builder $query;
    protected Request $request;
    protected string $anchorColumn = 'created_at';
    protected int $ttl = 3600;  // Cache duration in seconds
    protected string $cachePrefix = 'anchor_';
    protected ?string $userId = null;

    protected string $anchorCacheKey;
    protected string $pageCacheKey;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        $this->request = request();
        $this->userId = auth()->id();  // Null if guest
    }

    /**
     * @param Builder $query
     * @return self
     */
    public static function make(Builder $query): self
    {
        return new self($query);
    }

    public function withRequest(Request $request): self
    {
        $this->request = $request;
        $this->userId = auth()->id();
        return $this;
    }

    public function setAnchorColumn(string $anchorColumn): self
    {
        $this->anchorColumn = $anchorColumn;
        return $this;
    }

    public function setTTL(int $ttl): self
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function setCachePrefix(string $prefix): self
    {
        $this->cachePrefix = $prefix;
        return $this;
    }

    /**
     * Perform anchor-based pagination with caching.
     *
     * @return Paginator
     */
    public function paginate(): Paginator
    {
        $page = (int)$this->request->query('page', 1);
        $perPage = (int)$this->request->query('per-page', 20);

        // Skip caching for guest users (no auth)
        if (is_null($this->userId)) {
            return $this->query->paginate(perPage: $perPage, page: $page);
        }

        $this->setAnchorCacheKey();
        $this->setPageCacheKey();

        // Reset anchor if on first page
        $this->resetAnchorCacheIfFirstPage($page);

        // Cache or fetch anchor timestamp
        $anchorTimestamp = $this->getAnchorTimestamp();

        // Apply anchor filtering
        $this->query->whereRaw("CASE WHEN $this->anchorColumn <= ? THEN 1 ELSE 0 END = 1", [$anchorTimestamp]);

        // Cache paginated results
        //return Cache::remember($this->pageCacheKey, $this->ttl, function () use ($perPage, $page) {
        return $this->query->paginate(perPage: $perPage, page: $page);
        //});
    }

    private function setAnchorCacheKey(): void
    {
        $anchorQueryParamsHash = md5(json_encode([
            'user' => $this->userId,
            'route' => $this->request->route()->parameters(),
            'query' => $this->request->except('page')
        ]));

        $this->anchorCacheKey = $this->cachePrefix . $this->anchorColumn . '_' . $anchorQueryParamsHash;
    }

    private function setPageCacheKey(): void
    {
        $pageQueryParamsHash = md5(json_encode([
            'user' => $this->userId,
            'route' => $this->request->route()->parameters(),
            'query' => $this->request->query()
        ]));

        $this->pageCacheKey = 'paginate_' . $this->anchorColumn . '_' . $pageQueryParamsHash;
    }

    private function getAnchorTimestamp(): mixed
    {
        return Cache::remember($this->anchorCacheKey, $this->ttl, function () {
            return $this->query->clone()->max($this->anchorColumn);
        });
    }

    private function resetAnchorCacheIfFirstPage(int $page): void
    {
        if ($page === 1) {
            Cache::forget($this->anchorCacheKey);
            Cache::forget($this->pageCacheKey);
        }
    }
}
