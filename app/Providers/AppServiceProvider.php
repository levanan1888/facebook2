<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Đăng ký RepositoryServiceProvider
        $this->app->register(RepositoryServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký Blade directives cho permission
        Blade::directive('role', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });

        Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('permission', function ($permission) {
            return "<?php if(auth()->check() && \\Spatie\\Permission\\Models\\Permission::where('name', {$permission})->exists() && auth()->user()->can({$permission})): ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('anyrole', function ($roles) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyRole({$roles})): ?>";
        });

        Blade::directive('endanyrole', function () {
            return "<?php endif; ?>";
        });

        // Custom error handling
        $this->app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', function ($app) {
            return new class($app) extends \Illuminate\Foundation\Exceptions\Handler {
                public function render($request, \Throwable $e)
                {
                    if ($e instanceof NotFoundHttpException) {
                        if ($request->expectsJson()) {
                            return response()->json(['error' => 'Not Found'], 404);
                        }
                        
                        return response()->view('errors.404', [], 404);
                    }
                    
                    if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 403) {
                        if ($request->expectsJson()) {
                            return response()->json(['error' => 'Forbidden'], 403);
                        }
                        
                        return response()->view('errors.403', [], 403);
                    }
                    
                    return parent::render($request, $e);
                }
            };
        });
    }
}
