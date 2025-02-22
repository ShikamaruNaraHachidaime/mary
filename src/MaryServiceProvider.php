<?php

namespace Mary;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mary\View\Components\Alert;
use Mary\View\Components\Badge;
use Mary\View\Components\Button;
use Mary\View\Components\Calendar;
use Mary\View\Components\Card;
use Mary\View\Components\Checkbox;
use Mary\View\Components\Choices;
use Mary\View\Components\DatePicker;
use Mary\View\Components\DateTime;
use Mary\View\Components\Drawer;
use Mary\View\Components\Dropdown;
use Mary\View\Components\Form;
use Mary\View\Components\Header;
use Mary\View\Components\Icon;
use Mary\View\Components\Input;
use Mary\View\Components\ListItem;
use Mary\View\Components\Main;
use Mary\View\Components\Menu;
use Mary\View\Components\MenuItem;
use Mary\View\Components\MenuSeparator;
use Mary\View\Components\Modal;
use Mary\View\Components\Nav;
use Mary\View\Components\Radio;
use Mary\View\Components\Select;
use Mary\View\Components\Stat;
use Mary\View\Components\Tab;
use Mary\View\Components\Table;
use Mary\View\Components\Tabs;
use Mary\View\Components\Toast;
use Mary\View\Components\Toggle;

class MaryServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->registerComponents();
        $this->registerBladeDirectives();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function registerComponents()
    {
        // Just rename <x-icon> provided by BladeUI Icons to <x-svg> to not collide with ours
        Blade::component('BladeUI\Icons\Components\Icon', 'svg');

        // Blade
        Blade::component('alert', Alert::class);
        Blade::component('badge', Badge::class);
        Blade::component('button', Button::class);
        Blade::component('calendar', Calendar::class);
        Blade::component('card', Card::class);
        Blade::component('checkbox', Checkbox::class);
        Blade::component('choices', Choices::class);
        Blade::component('drawer', Drawer::class);
        Blade::component('datetime', DateTime::class);
        Blade::component('datepicker', DatePicker::class);
        Blade::component('dropdown', Dropdown::class);
        Blade::component('form', Form::class);
        Blade::component('header', Header::class);
        Blade::component('input', Input::class);
        Blade::component('icon', Icon::class);
        Blade::component('list-item', ListItem::class);
        Blade::component('modal', Modal::class);
        Blade::component('menu', Menu::class);
        Blade::component('menu-item', MenuItem::class);
        Blade::component('menu-separator', MenuSeparator::class);
        Blade::component('main', Main::class);
        Blade::component('nav', Nav::class);
        Blade::component('radio', Radio::class);
        Blade::component('select', Select::class);
        Blade::component('stat', Stat::class);
        Blade::component('table', Table::class);
        Blade::component('tab', Tab::class);
        Blade::component('tabs', Tabs::class);
        Blade::component('toast', Toast::class);
        Blade::component('toggle', Toggle::class);
    }

    public function registerBladeDirectives()
    {
        /**
         * All credits from this blade directive goes to Konrad Kalemba.
         * Just copied and modified for my very specifc use case.
         *
         * https://github.com/konradkalemba/blade-components-scoped-slots
         */
        Blade::directive('scope', function ($expression) {

            // Split the expression by `top-level` commas (not in parentheses)
            $directiveArguments = preg_split("/,(?![^\(\(]*[\)\)])/", $expression);
            $directiveArguments = array_map('trim', $directiveArguments);

            [$name, $functionArguments] = $directiveArguments;

            /**
             *  Slot names can`t contains dot , eg: `user.city`.
             *  So we convert `user.city` to `user___city`
             *
             *  Later, on component you must replace it back.
             */
            $name = str_replace('.', '___', $name);

            return "<?php \$__env->slot({$name}, function({$functionArguments}) use (\$__env) { ?>";
        });

        Blade::directive('endscope', function () {
            return '<?php }); ?>';
        });
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mary.php', 'mary');

        // Register the service the package provides.
        $this->app->singleton('mary', function ($app) {
            return new Mary;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['mary'];
    }

    /**
     * Console-specific booting.
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/mary.php' => config_path('mary.php'),
        ], 'mary.config');
    }
}
