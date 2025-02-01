<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class PasswordServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->password();
    }

    private function password(): void
    {
        $passwordConfig = config('platform.users.password');
        Password::defaults(function () use ($passwordConfig) {
            $rule = Password::min(Arr::get($passwordConfig, 'min', 8));
            if (Arr::get($passwordConfig, 'numbers')) {
                $rule->numbers();
            }
            if (Arr::get($passwordConfig, 'symbols')) {
                $rule->symbols();
            }
            if (Arr::get($passwordConfig, 'uncompromised')) {
                $rule->uncompromised();
            }
            if (Arr::get($passwordConfig, 'mixedCase')) {
                $rule->mixedCase();
            } else {
                $rule->letters();
            }

            return $rule;
        });
    }
}

