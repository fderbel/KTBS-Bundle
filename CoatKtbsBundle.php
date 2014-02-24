<?php

namespace Coat\KtbsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;


class CoatKtbsBundle extends Bundle
{
 public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'coat_ktbs');
    }
}
