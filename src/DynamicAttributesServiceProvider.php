<?php

namespace AwStudio\DynamicAttributes;

use Illuminate\Support\ServiceProvider;

class DynamicAttributesServiceProvider extends ServiceProvider
{
    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../migrations/2021_00_00_000000_create_attributes_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_attributes_table.php'),
        ], 'attributes:migrations');
    }
}
