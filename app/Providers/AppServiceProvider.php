<?php

namespace App\Providers;

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
       \Blade::directive('latexescape', function ($expression) {
        return "<?php echo str_replace(
            ['\\', '&', '%', '\$', '#', '_', '{', '}', '~', '^'],
            ['\\\\', '\\&', '\\%', '\\\$', '\\#', '\\_', '\\{', '\\}', '\\textasciitilde{}', '\\textasciicircum{}'],
            $expression
        ); ?>";
    });
    }
}
