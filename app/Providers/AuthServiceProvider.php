<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
// Use the Passport class by name at runtime to keep this provider safe when the package
// is not installed (avoids static analyzers complaining about missing methods).

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\\Models\\Model' => 'App\\Policies\\ModelPolicy',
    ];

    public function register(): void
    {
        // nothing to register here
    }

    public function boot(): void
    {
        $this->registerPolicies();

        // Register Passport routes and default scopes if Passport is installed
        $passportClass = 'Laravel\\Passport\\Passport';
        if (class_exists($passportClass)) {
            // Only call routes() if the method exists on the installed Passport class
            if (method_exists($passportClass, 'routes')) {
                $passportClass::routes();
            }

            // Enable password grant explicitly for Passport versions that disable it by default
            if (method_exists($passportClass, 'enablePasswordGrant')) {
                $passportClass::enablePasswordGrant();
            }

            // Define some default scopes if tokensCan is available
            if (method_exists($passportClass, 'tokensCan')) {
                $passportClass::tokensCan([
                    'admin' => 'Full administrative access',
                    'manage-accounts' => 'Create/update/delete accounts',
                    'read-accounts' => 'Read accounts',
                ]);
            }
        }
    }
}
