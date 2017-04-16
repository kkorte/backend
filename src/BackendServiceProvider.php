<?php

namespace Hideyo\Backend;

use Illuminate\Support\ServiceProvider;

use Cviebrock\EloquentSluggable\ServiceProvider as SluggableServiceProvider;
use hisorange\BrowserDetect\Provider\BrowserDetectService;
use Collective\Html\HtmlServiceProvider;
use Hideyo\Backend\Services\HtmlServiceProvider as CustomHtmlServiceProvider;
use Krucas\Notification\NotificationServiceProvider;
use Yajra\Datatables\DatatablesServiceProvider;
use Felixkiss\UniqueWithValidator\UniqueWithValidatorServiceProvider;
use Auth;
use Schema;

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
        $router->aliasMiddleware('auth.hideyo.backend', '\Hideyo\Backend\Middleware\AuthenticateAdmin::class');
    
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
            'Hideyo\Backend\Repositories\BrandRepositoryInterface',
            'Hideyo\Backend\Repositories\BrandRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\BlogRepositoryInterface',
            'Hideyo\Backend\Repositories\BlogRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\RedirectRepositoryInterface',
            'Hideyo\Backend\Repositories\RedirectRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductCombinationRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductCombinationRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\AttributeGroupRepositoryInterface',
            'Hideyo\Backend\Repositories\AttributeGroupRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\AttributeRepositoryInterface',
            'Hideyo\Backend\Repositories\AttributeRepository'
        );


        $this->app->bind(
            'Hideyo\Backend\Repositories\LanguageRepositoryInterface',
            'Hideyo\Backend\Repositories\LanguageRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\UserRepositoryInterface',
            'Hideyo\Backend\Repositories\UserRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\RoleRepositoryInterface',
            'Hideyo\Backend\Repositories\RoleRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductRelatedProductRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductRelatedProductRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductExtraFieldValueRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductExtraFieldValueRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ExtraFieldDefaultValueRepositoryInterface',
            'Hideyo\Backend\Repositories\ExtraFieldDefaultValueRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ExtraFieldRepositoryInterface',
            'Hideyo\Backend\Repositories\ExtraFieldRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\CouponRepositoryInterface',
            'Hideyo\Backend\Repositories\CouponRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ClientRepositoryInterface',
            'Hideyo\Backend\Repositories\ClientRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ClientAddressRepositoryInterface',
            'Hideyo\Backend\Repositories\ClientAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductCategoryRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductCategoryRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ShopRepositoryInterface',
            'Hideyo\Backend\Repositories\ShopRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\UserLogRepositoryInterface',
            'Hideyo\Backend\Repositories\UserLogRepository'
        );


        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductAmountOptionRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductAmountOptionRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductAmountSeriesRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductAmountSeriesRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductTagGroupRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductTagGroupRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ProductWaitingListRepositoryInterface',
            'Hideyo\Backend\Repositories\ProductWaitingListRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\TaxRateRepositoryInterface',
            'Hideyo\Backend\Repositories\TaxRateRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\PaymentMethodRepositoryInterface',
            'Hideyo\Backend\Repositories\PaymentMethodRepository'
        );


        $this->app->bind(
            'Hideyo\Backend\Repositories\SendingMethodRepositoryInterface',
            'Hideyo\Backend\Repositories\SendingMethodRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\OrderRepositoryInterface',
            'Hideyo\Backend\Repositories\OrderRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\OrderAddressRepositoryInterface',
            'Hideyo\Backend\Repositories\OrderAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\OrderPaymentLogRepositoryInterface',
            'Hideyo\Backend\Repositories\OrderPaymentLogRepository'
        );


        $this->app->bind(
            'Hideyo\Backend\Repositories\OrderStatusRepositoryInterface',
            'Hideyo\Backend\Repositories\OrderStatusRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\OrderStatusEmailTemplateRepositoryInterface',
            'Hideyo\Backend\Repositories\OrderStatusEmailTemplateRepository'
        );


        $this->app->bind(
            'Hideyo\Backend\Repositories\InvoiceRepositoryInterface',
            'Hideyo\Backend\Repositories\InvoiceRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\InvoiceAddressRepositoryInterface',
            'Hideyo\Backend\Repositories\InvoiceAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\SendingPaymentMethodRelatedRepositoryInterface',
            'Hideyo\Backend\Repositories\SendingPaymentMethodRelatedRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\RecipeRepositoryInterface',
            'Hideyo\Backend\Repositories\RecipeRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\NewsRepositoryInterface',
            'Hideyo\Backend\Repositories\NewsRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ContentRepositoryInterface',
            'Hideyo\Backend\Repositories\ContentRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\FaqItemRepositoryInterface',
            'Hideyo\Backend\Repositories\FaqItemRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\HtmlBlockRepositoryInterface',
            'Hideyo\Backend\Repositories\HtmlBlockRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\GeneralSettingRepositoryInterface',
            'Hideyo\Backend\Repositories\GeneralSettingRepository'
        );

        $this->app->bind(
            'Hideyo\Backend\Repositories\ExceptionRepositoryInterface',
            'Hideyo\Backend\Repositories\ExceptionRepository'
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

}
