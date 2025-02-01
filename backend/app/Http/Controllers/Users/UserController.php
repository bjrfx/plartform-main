<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserInternalRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use App\Services\Users\UserService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    )
    {
    }

    /**
     * @throws ValidationException
     */
    public function edit(string $userId): UserResource
    {
        $user = $this->userService->getUser($userId);

        return UserResource::make($user);
    }


    /**
     * @throws ValidationException
     */
    public function save(UserInternalRequest $request, ?User $user = null): UserResource
    {

        $role = Arr::get($request, 'role');

        if (!Gate::allows('assignRole', [$user ?? new User(), $role])) {
            throw ValidationException::withMessages([
                'role' => 'You are not authorized to assign this role.',
            ]);
        }

        $user = $this->userService->save($request->validated(), $user);

        return UserResource::make($user);
    }
}
