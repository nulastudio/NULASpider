<?php

namespace nulastudio\Spider\ServiceProviders;

use nulastudio\Spider\Contracts\ServiceProviderContract;
use nulastudio\Spider\Kernel;
use nulastudio\Spider\Services\HookService;

class HookServiceProvider implements ServiceProviderContract
{
    private $hookService;

    public function __construct(array $hooks = [])
    {
        $this->hookService = new HookService($hooks);
    }

    public function register(Kernel $kernel)
    {
        $hookService = $this->hookService;
        $kernel->bind('getHookPoints', function ($application, ...$args) use ($hookService) {
            return $hookService->getHooks();
        });

        $kernel->bind('hooks', $hookService);
        $kernel->bind('triggerHook', function ($application, string $group, $params, ...$args) use ($hookService) {
            return $hookService->triggerHook($group, ...$params);
        });
        $kernel->bind('addHook', function ($application, string $group, $callback, ...$args) use ($hookService) {
            if (($callable = Util\resolveCallable($callback, true)) !== false) {
                return $hookService->addHook($group, $callable);
            }
            return false;
        });
        $kernel->bind('cleanHook', function ($application, string $group = null, ...$args) use ($hookService) {
            return $hookService->cleanHook($group);
        });
    }
}
