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

use Cwd\GridBundle\Column\ColumnInterface;

interface GridBuilderInterface extends \Countable
{
    public function add(ColumnInterface $child): self;

    public function get(string $name): ColumnInterface;

    public function remove(string $name): self;

    public function has(string $name): bool;

    public function all(): array;
}
