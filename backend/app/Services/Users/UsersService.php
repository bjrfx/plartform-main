<?php

namespace App\Services\Users;

use App\Models\User;
use App\QueryBuilders\PaginationBuilder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class UsersService
{
    public function list(array $requestData, ?string $merchantId = null): Paginator
    {
        $sort = Arr::get($requestData, 'sort', 'name');
        $order = Arr::get($requestData, 'order', 'asc');
        $search = Arr::get($requestData, 'search');
        $searchType = Arr::get($requestData, 'search_type');
        $merchants = Arr::get($requestData, 'merchants', []);

        $query = User::query()
            ->visibleToAuthUser()
            ->when(is_null($merchantId), function ($query) {
                $query->with(['merchant' => function ($query) {
                    $query->select('id', 'name');
                }]);
            }, function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            })
            ->orderBy($sort, $order);

        if (!is_null($search) && !is_null($searchType)) {
            $query->where($searchType, 'like', '%' . $search . '%');
        }

        if (is_null($merchantId) && count($merchants) > 0) {
            $hasSystem = Arr::pull($merchants, 0) === '0';

            $query->when(
                $hasSystem,
                function ($query) use ($merchants) {
                    $query->where(function (Builder $query) use ($merchants) {
                        $query//->whereIn('merchant_id', $merchants)
                        ->orWhereNull('merchant_id');
                    });
                },
                function ($query) use ($merchants) {
                    $query->whereIn('merchant_id', $merchants);
                }
            );

        }

        return PaginationBuilder::make($query)->paginate();
    }
}
