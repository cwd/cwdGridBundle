<?php

/*
 * This file is part of the Cwd Grid Bundle
 *
 * (c) 2018 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cwd\GridBundle;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AdapterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(GridFactory::class)) {
            return;
        }

        $factoryDefinition = $container->findDefinition(GridFactory::class);

        $adapters = $container->findTaggedServiceIds('cwd_grid.adapter');

        foreach ($adapters as $id => $tags) {
            $factoryDefinition->addMethodCall('addAdapter', array(new Reference($id)));
        }
    }
}
