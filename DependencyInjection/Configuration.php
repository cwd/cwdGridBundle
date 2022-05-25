<?php
/*
 * This file is part of Whistleblower Plattform
 *
 * ©2021 cwd.at GmbH <office@cwd.at>
 *
 * Unauthorized copying or modification of this file, via any medium is strictly prohibited
 * Proprietary and confidential.
 */
declare(strict_types=1);

namespace Cwd\GridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('cwd_grid');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('template')->defaultValue('@CwdGrid/grid.html.twig')->cannotBeEmpty()->end()
            ->end()
        ;

        return $treeBuilder;
    }

}