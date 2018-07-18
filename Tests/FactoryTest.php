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

namespace Cwd\GridBundle\Tests;

use Cwd\GridBundle\GridFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FactoryTest extends WebTestCase
{
    public function testGridCreating()
    {
        self::bootKernel();

        // returns the real and unchanged service container
        //$container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        // $container = self::$container;

        $factory = self::$kernel->getContainer()->get(GridFactory::class);

        $this->assertInstanceOf(GridFactory::class, $factory);

        $grid = $factory->create(SampleGrid::class, []);
        $this->assertInstanceOf(SampleGrid::class, $grid);
    }
}
