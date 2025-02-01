<?php

namespace App\Http\Controllers\Users;

use App\Helpers\General\DomainHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\Users\UserResource;
use App\Services\Users\UsersService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function __construct(
        protected UsersService $usersService
    )
    {
    }

    public function index(Request $request): PaginatedResource
    {
        $merchants = $request->input('merchants', []);
        if (is_string($merchants)) {
            //if the value is string cast to array
            $request->merge(['merchants' => [$merchants]]);
        }
        $requestData = $request->validate([
            'sort' => ['sometimes', 'nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'nullable', 'string', 'in:asc,desc'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'search_type' => ['sometimes', 'nullable', 'string', 'in:name,email,null'],
            'merchants.*' => [
                'sometimes',
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!($value === '0' || (is_string($value) && Str::isUuid($value)))) {
                        $fail("$attribute is invalid.");
                    }
                },
            ],
        ]);

        $merchant = DomainHelper::getMerchant();
        $merchantId = $merchant?->getKey();

        $users = $this->usersService->list($requestData, $merchantId);

        return new PaginatedResource($users, UserResource::class);
    }
}
