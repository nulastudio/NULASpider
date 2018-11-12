<?php

namespace nulastudio\Spider;

use nulastudio\Spider\Contracts\ServiceProviderContract;
use nulastudio\Spider\Exceptions\ApplicationNotObjectException;
use nulastudio\Spider\Exceptions\ServiceNotFoundException;

class Kernel
{
    private $application;
    private $providers = [];
    private $binds     = [];

    public function __construct($application, array $providers = [])
    {
        if (!is_object($application)) {
            $type = gettype($application);
            throw new ApplicationNotObjectException("Trying to inject a non-object({$type} detected) to the kernel.");
        }
        foreach ($providers as $provider) {
            $this->providers[] = $provider;
        }
        $this->application = $application;
        $this->bind('bind', function ($application, string $name, $provider) {
            $this->bind($name, $provider);
        });
    }

    public function bootstrap()
    {
        $this->registerProviders();
        return $this;
    }

    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider);
        }
    }

    public function register(ServiceProviderContract $provider)
    {
        $provider->register($this);
    }

    public function bind(string $name, $provider)
    {
        $this->binds[$name] = $provider;
    }

    // public function singleton() {}

    // public function instance() {}

    public function getService(string $service)
    {
        if (!array_key_exists($service, $this->binds)) {
            throw new ServiceNotFoundException("Service not found: {$service}");
        }
        return $this->binds[$service];
    }

    public function getApplication()
    {
        return $this->application;
    }
}
