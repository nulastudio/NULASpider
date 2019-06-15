<?php

namespace nulastudio\Spider;

use nulastudio\Spider\Contracts\ServiceProviderContract;
use nulastudio\Spider\Exceptions\KernelException;

class Kernel
{
    private $application;
    private $providers = [];
    private $binds     = [];

    public function __construct($application, array $providers = [])
    {
        if (!is_object($application)) {
            $type = gettype($application);
            throw new KernelException("Trying to inject a non-object({$type} detected) into the kernel.");
        }
        $this->application = $application;
        foreach ($providers as $provider) {
            check:
            if (is_object($provider) && $provider instanceof ServiceProviderContract) {
                $this->providers[] = $provider;
            } else if (is_string($provider) && class_exists($provider)) {
                $provider = new $provider;
                goto check;
            } else {
                throw new KernelException('Trying to register an invalid service provider.');
            }
        }
        $this->bind('bind', function ($application, string $name, $stuff) {
            $this->bind($name, $stuff);
        });
        $this->bind('unbind', function ($application, string $name) {
            $this->unbind($name);
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
            $this->register($provider);
        }
    }

    public function register(ServiceProviderContract $provider)
    {
        $provider->register($this);
    }

    public function bind(string $name, $stuff)
    {
        if (array_key_exists($name, $this->binds)) {
            throw new KernelException("Service exists: {$name}");
        } else {
            $this->binds[$name] = $stuff;
        }
    }

    public function unbind(string $name)
    {
        // may not release resource
        unset($this->binds[$name]);
    }

    // public function singleton() {}

    // public function instance() {}

    public function getService(string $service)
    {
        if (!array_key_exists($service, $this->binds)) {
            throw new KernelException("Service not found: {$service}");
        }
        return $this->binds[$service];
    }

    public function getApplication()
    {
        return $this->application;
    }
}
