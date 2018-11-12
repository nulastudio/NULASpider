<?php

namespace nulastudio\Spider\Contracts;

use nulastudio\Spider\Kernel;

interface ServiceProviderContract
{
    public function register(Kernel $kernel);
}
