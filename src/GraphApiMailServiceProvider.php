<?php

namespace LaravelGraphApiMailDriver;

use Illuminate\Mail\MailServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GraphApiMailServiceProvider extends MailServiceProvider{

    /**
     * @return void
     * @throws Exceptions\ConfigException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void{

        $this->app->get('mail.manager')->extend('microsoft-graph-api', function(array $config){

            return new GraphApiTransportManager($config);
        });
    }
}
