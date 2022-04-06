<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
 */

declare(strict_types=1);

namespace Cwd\GridBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class CwdGridExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');
    }
}
