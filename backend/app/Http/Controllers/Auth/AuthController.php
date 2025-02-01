<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Users\UserRoleEnums;
use App\Enums\Users\UserTokenAbilityEnums;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Users\UserBasicInfoResource;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use App\Services\Users\PasswordResetService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Handle user login and return a Bearer token.
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        // Validate incoming request
        $requestData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        /*
        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages(['message' => 'Invalid credentials']);
        }
        */
        $merchantId = $request->input('merchant_id');
        // Attempt to authenticate the user
        $this->loginAuthAttempt(requestData: $requestData, merchantId: $merchantId);

        // Retrieve the authenticated user
        /** @var User $user */
        $user = Auth::user();

        $tokenAbilities = $this->determineTokenAccessAbility(user: $user);


        // Revoke all previous tokens (optional)
        //$user->tokens()->delete();

        // Create a new token
        $token = $user->createToken('auth_token', $tokenAbilities)->plainTextToken;

        // Return the token in the response
        return response()->json([
            'message' => 'Login successful',
            'user' => new UserBasicInfoResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'is_system' => is_null($request->input('subdomain'))
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * @noinspection SpellCheckingInspection
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Get only validated data
        $validated = $request->validated();
        // Create the user
        $user = User::query()
            ->create([
                'first_name' => Arr::get($validated, 'first_name'),
                'middle_name' => Arr::get($validated, 'middle_name'),
                'last_name' => Arr::get($validated, 'last_name'),
                'email' => Arr::get($validated, 'email'),
                'phone' => Arr::get($validated, 'phone'),
                'street' => Arr::get($validated, 'street'),
                'city' => Arr::get($validated, 'city'),
                'state' => Arr::get($validated, 'state'),
                'zip_code' => Arr::get($validated, 'zip_code'),
                'is_ebilling_enabled' => Arr::get($validated, 'is_ebilling_enabled', 0),
                'password' => Hash::make(Arr::get($validated, 'password')),
                'role' => UserRoleEnums::MEMBER,
                'merchant_id' => Arr::get($validated, 'merchant_id'),
            ]);

        // Return a response
        return response()->json([
            'message' => 'User registered successfully.',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * @throws ValidationException
     */
    public function activate(string $token, PasswordResetService $service): JsonResponse
    {
        $userId = $service->retrieveEntry(token: $token);

        /** @var User $user */
        $user = User::query()->where('id', $userId)->firstOrFail();

        // Activate the user
        $user->setAttribute('is_enabled', true);
        $user->save();

        return response()->json([
            'message' => 'Your Account has been successfully activated. You can now log in using the username and password you chose during the registration'
        ]);
    }

    public function passwordRules(): JsonResponse
    {
        $passwordConfig = config('platform.users.password');
        return response()->json($passwordConfig);
    }

    /**
     * @noinspection SpellCheckingInspection
     */
    public function token(string $token): JsonResponse
    {
        $sysRedirectToken = null;

        if (request()->input('merchant_id')) {
            // Find the token manually
            $tokenRecord = PersonalAccessToken::findToken($token);

            if ($tokenRecord) {
                /** @var User $user */
                $user = $tokenRecord->getRelationValue('tokenable');

                $redirected = 'REDIRECTED';

                // Directly check token abilities
                $abilities = $tokenRecord->getAttribute('abilities');
                $hasSystemAbility = in_array(UserTokenAbilityEnums::SYSTEM->value, $abilities, true);
                $alreadyRedirected = in_array($redirected, $abilities, true);

                // Create a new token if it's a SYSTEM user and hasn't already been redirected
                if ($hasSystemAbility && !$alreadyRedirected) {
                    $tokenAbilities = [
                        UserTokenAbilityEnums::SYSTEM->value,
                        $redirected,
                    ];

                    // Create a new token for redirection
                    $sysRedirectToken = $user->createToken('auth_token', $tokenAbilities)->plainTextToken;
                }
            }
        }

        // Return the newly created token (or null if conditions aren't met)
        return response()->json(['access_token' => $sysRedirectToken]);
    }

    /**
     * @throws ValidationException
     */
    private function loginAuthAttempt(array $requestData, ?string $merchantId = null): void
    {
        // Attempt to authenticate the user
        if (!Auth::attempt([
            'email' => $requestData['email'],
            'password' => $requestData['password'],
            fn(Builder $query) => $query->when(is_null($merchantId),
                fn($q) => $q->whereNull('merchant_id'),
                fn($q) => $q->where(function ($q) use ($merchantId) {
                    // This prioritization ensures that the database evaluates merchant_id = $merchantId first
                    // Allowing the system to assign the same email to both a system-wide user (with merchant_id = null) and a user under a specific merchant.
                    // This guarantees that when both records exist, the user under the merchant (merchant_id = $merchantId) is prioritized during authentication.
                    $q->where('merchant_id', $merchantId)
                        ->orWhere(function ($subQuery) {
                            $subQuery->whereNull('merchant_id');
                        });
                })
            )
        ])) {
            // Authentication Fail
            if ($merchantId) {
                throw ValidationException::withMessages([
                    'message' => 'Login failed. Please check your email and password, or reset your password if the issue persists.'
                ]);
            } else {
                throw ValidationException::withMessages([
                    'message' => 'Access denied. Please select a merchant to proceed'
                ]);
            }
        }
    }

    /**
     * @example
     * if ($request->user()->tokenCan(UserTokenAbilityEnums::SYSTEM->value)) {
     * // Perform actions for SYSTEM role
     * }
     *
     * if ($request->user()->tokenCan(UserTokenAbilityEnums::MERCHANT->value)) {
     * // Perform actions for MERCHANT role
     * }
     */
    private function determineTokenAccessAbility(User $user): array
    {
        return match ($user->getAttribute('role')) {
            UserRoleEnums::SYSTEM_ADMIN,
            UserRoleEnums::ADMIN,
            UserRoleEnums::SUPPORT => [UserTokenAbilityEnums::SYSTEM->value],

            UserRoleEnums::MERCHANT_STAFF,
            UserRoleEnums::MERCHANT_ADMIN => [UserTokenAbilityEnums::MERCHANT->value],

            default => [], // Empty array if no role matches
        };
    }
}
