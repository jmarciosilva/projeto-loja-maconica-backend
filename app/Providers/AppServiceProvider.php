<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        // Models live under app/Modules/{Module}/Models instead of app/Models,
        // so factories are resolved by class basename rather than Laravel's
        // default App\Models\{Model} => Database\Factories\{Model}Factory guess.
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Database\\Factories\\'.Str::afterLast($modelName, '\\').'Factory',
        );
    }
}
