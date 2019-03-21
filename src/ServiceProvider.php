<?php

namespace PhpSoft\Base;

use Illuminate\Support\ServiceProvider as Provider;

class ServiceProvider extends Provider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->app->singleton('phpsoft.arrayview', function ($app) {
            $finder = $app['view']->getFinder();
            return new class($finder) extends \PhpSoft\ArrayView\Providers\ArrayView {
                public function __construct(\Illuminate\View\ViewFinderInterface $finder)
                {
                    parent::__construct($finder);
                    $this->factory = new \PhpSoft\Base\FactoryViewHelper($this->finder->getPaths(), $this->finder);
                }
            };
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
    }
}
