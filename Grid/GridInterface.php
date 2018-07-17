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

namespace Cwd\GridBundle\Grid;

use Cwd\GridBundle\Adapter\AdapterInterface;

interface GridInterface
{
    /**
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface;

    /**
     * @param AdapterInterface $adapter
     *
     * @return AbstractGrid
     */
    public function setAdapter(AdapterInterface $adapter);

    public function setTwig(\Twig_Environment $twig);

    public function getTwig(): \Twig_Environment;
}
