<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
 */

declare(strict_types=1);

namespace Cwd\GridBundle\Adapter;

use Cwd\GridBundle\Grid\GridInterface;
use Pagerfanta\Pagerfanta;

interface AdapterInterface
{
    public function getData(GridInterface $grid): Pagerfanta;
}
