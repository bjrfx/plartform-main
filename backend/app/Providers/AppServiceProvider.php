<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\Users\RolePolicy;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'local') {
            $isBackendFolder = basename(getcwd()) === 'backend';

            if ($isBackendFolder) {
                // Override the default storage path to use `backend/storage` instanced of 'storage' since we are on root
                $this->app->useStoragePath(base_path('backend/storage'));
                // Override DB_HOST to '127.0.0.1' if running under the backend folder
                // This way from the root you can migrate via:
                // docker-compose exec backend php artisan migrate
                // This way from within backend folder you can migrate via:
                // php artisan migrate
                config(['database.connections.mysql.host' => '127.0.0.1']);
                // Only clear config cache if config:cache has been run previously:
                if (file_exists(base_path('bootstrap/cache/config.php'))) {
                    Artisan::call('config:clear');
                }
            }
        }

        $this->assignCustomPolicies();
    }

    private function assignCustomPolicies(): void
    {
        // Laravel only Allows One policy per Model !!!
        Gate::policy(User::class, RolePolicy::class);
    }
}
