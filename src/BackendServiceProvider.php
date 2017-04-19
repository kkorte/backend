<?php

namespace Hideyo\Ecommerce\Backend;

use Illuminate\Support\ServiceProvider;

use Cviebrock\EloquentSluggable\ServiceProvider as SluggableServiceProvider;
use hisorange\BrowserDetect\Provider\BrowserDetectService;
use Collective\Html\HtmlServiceProvider;
use Hideyo\Ecommerce\Backend\Services\HtmlServiceProvider as CustomHtmlServiceProvider;
use Krucas\Notification\NotificationServiceProvider;
use Yajra\Datatables\DatatablesServiceProvider;
use Felixkiss\UniqueWithValidator\UniqueWithValidatorServiceProvider;
use Auth;
use Schema;
use Route;

class BackendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    { 

        Schema::defaultStringLength(191);

        $router->middlewareGroup('hideyobackend', array(
                \App\Http\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \App\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Krucas\Notification\Middleware\NotificationMiddleware::class
            )
        );
        
        $this->loadRoutesFrom(__DIR__.'/Routes/backend.php');
        $router->aliasMiddleware('auth.hideyo.backend', '\Hideyo\Ecommerce\Backend\Middleware\AuthenticateAdmin::class');
    
        $this->publishes([
            __DIR__.'/config/hideyo.php' => config_path('hideyo.php'),
            __DIR__.'/../seeds' => database_path('seeds/'),            
            __DIR__.'/Resources/views' => resource_path('views/vendor/hideyobackend'),
            __DIR__.'/Resources/scss' => resource_path('assets/vendor/hideyobackend/scss'),
            __DIR__.'/Resources/javascript' => resource_path('assets/vendor/hideyobackend/javascript'),
            __DIR__.'/Resources/bower.json' => resource_path('assets/vendor/hideyobackend/bower.json'),
            __DIR__.'/Resources/gulpfile.js' => resource_path('assets/vendor/hideyobackend/gulpfile.js'),
            __DIR__.'/Resources/package.json' => resource_path('assets/vendor/hideyobackend/package.json'),

        ]);

        $this->loadViewsFrom(__DIR__.'/Resources/views/', 'hideyo_backend');

        $this->loadTranslationsFrom(__DIR__.'/Resources/translations', 'hideyo');

        $this->loadMigrationsFrom(__DIR__.'/../migrations'); 
    }
    
    private function mergeConfig()
    {
        //merge provider and guard, reducing installation guide  
        $this->mergeConfigFrom(__DIR__ . '/Config/provider.php', 'auth.providers');
        $this->mergeConfigFrom(__DIR__ . '/Config/guard.php', 'auth.guards');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfig();

        $this->registerRequiredProviders();

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\BrandRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\BrandRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\BlogRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\BlogRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\RedirectRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\RedirectRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductCombinationRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductCombinationRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\AttributeGroupRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\AttributeGroupRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\AttributeRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\AttributeRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\LanguageRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\LanguageRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\UserRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\UserRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\RoleRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\RoleRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductRelatedProductRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductRelatedProductRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductExtraFieldValueRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductExtraFieldValueRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ExtraFieldDefaultValueRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ExtraFieldDefaultValueRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ExtraFieldRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ExtraFieldRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\CouponRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\CouponRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ClientRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ClientRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ClientAddressRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ClientAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductCategoryRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductCategoryRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ShopRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ShopRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\UserLogRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\UserLogRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductAmountOptionRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductAmountOptionRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductAmountSeriesRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductAmountSeriesRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductTagGroupRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductTagGroupRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ProductWaitingListRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ProductWaitingListRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\TaxRateRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\TaxRateRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\PaymentMethodRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\PaymentMethodRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\SendingMethodRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\SendingMethodRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\OrderRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\OrderRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\OrderAddressRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\OrderAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\OrderPaymentLogRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\OrderPaymentLogRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\OrderStatusRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\OrderStatusRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\OrderStatusEmailTemplateRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\OrderStatusEmailTemplateRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\InvoiceRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\InvoiceRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\InvoiceAddressRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\InvoiceAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\SendingPaymentMethodRelatedRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\SendingPaymentMethodRelatedRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\RecipeRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\RecipeRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\NewsRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\NewsRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ContentRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ContentRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\FaqItemRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\FaqItemRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\HtmlBlockRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\HtmlBlockRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\GeneralSettingRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\GeneralSettingRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Backend\Repositories\ExceptionRepositoryInterface',
            'Hideyo\Ecommerce\Backend\Repositories\ExceptionRepository'
        );

    }

    /**
     * Register 3rd party providers.
     */
    protected function registerRequiredProviders()
    {
        $this->app->register(SluggableServiceProvider::class);
        $this->app->register(HtmlServiceProvider::class);
        $this->app->register(NotificationServiceProvider::class);
        $this->app->register(DatatablesServiceProvider::class);
        $this->app->register(CustomHtmlServiceProvider::class);
        $this->app->register(UniqueWithValidatorServiceProvider::class);

        if (class_exists('Illuminate\Foundation\AliasLoader')) {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Form', \Collective\Html\FormFacade::class);
            $loader->alias('Html', \Collective\Html\HtmlFacade::class);
            $loader->alias('Notification', \Krucas\Notification\Facades\Notification::class);
        }
    }


    protected function generateCrud($path, $controllerName, $routeName = false) {

        if(!$routeName) {
            $routeName = $path;
        }

        Route::resource($path, $controllerName, ['names' => [
            'index'     => 'hideyo.'.$routeName.'.index',
            'create'    => 'hideyo.'.$routeName.'.create',
            'store'     => 'hideyo.'.$routeName.'.store',
            'edit'      => 'hideyo.'.$routeName.'.edit',
            'update'    => 'hideyo.'.$routeName.'.update',
            'destroy'   => 'hideyo.'.$routeName.'.destroy'
        ]]);
    }
}
