<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
 */

declare(strict_types=1);

namespace Cwd\GridBundle;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AdapterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(GridFactory::class)) {
            return;
        }

        $factoryDefinition = $container->findDefinition(GridFactory::class);

        $adapters = $container->findTaggedServiceIds('cwd_grid.adapter');

        foreach ($adapters as $id => $tags) {
            $factoryDefinition->addMethodCall('addAdapter', [new Reference($id)]);
        }
    }
}
