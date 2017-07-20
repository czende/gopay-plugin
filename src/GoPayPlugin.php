<?php

namespace Czende\GoPayPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GoPayPlugin extends Bundle {
	// attach Sylius Plugin Trait
    use SyliusPluginTrait;
}