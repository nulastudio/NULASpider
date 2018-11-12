<?php

namespace nulastudio\Spider\ServiceProviders;

use nulastudio\Spider\Contracts\ServiceProviderContract;
use nulastudio\Spider\Exceptions\ServiceProviderRegisterException;
use nulastudio\Spider\Kernel;
use nulastudio\Spider\Services\HookService;
use nulastudio\Util;

class HookServiceProvider implements ServiceProviderContract
{
    public function register(Kernel $kernel)
    {
        $kernel->bind('getHookPoints', function ($application, ...$args) {
            return $application->hook_points;
        });

        $hookService = Util\resolveCallable(new HookService($kernel->getApplication()->getHookPoints()), true);
        if ($hookService !== false) {
            $kernel->bind('hooks', $hookService);
            $kernel->bind('triggerHook', function ($application, string $group, $params, ...$args) {
                if (isset($application->hooks[$group])) {
                    foreach ($application->hooks->getHooks($group) as $hook) {
                        // $hook($application, ...$params);
                        call_user_func_array($hook, array_merge([$application], $params));
                    }
                }
            });
            $kernel->bind('addHook', function ($application, string $group, $callback, ...$args) {
                if (isset($application->hooks[$group])) {
                    if (($return = Util\resolveCallable($callback, true)) !== false) {
                        $application->hooks[$group] += [$return];
                        return true;
                    }
                }
                return false;
            });
            $kernel->bind('cleanHook', function ($application, string $group = null, ...$args) {
                $groups = [];
                if ($group === null) {
                    $groups = array_keys($application->getHookPoints());
                } elseif (isset($application->hooks[$group])) {
                    $groups[] = $group;
                }
                foreach ($groups as $g) {
                    $application->hooks[$g] = null;
                }
            });
        } else {
            throw new ServiceProviderRegisterException('Cannot register ' . HookService::class);
        }
    }
}
