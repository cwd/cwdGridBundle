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
use Cwd\GridBundle\Column\ColumnInterface;
use Cwd\GridBundle\GridBuilderInterface;
use Twig\Environment;

interface GridInterface
{
    public function buildGrid(GridBuilderInterface $builder, array $options);

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

    public function setTwig(Environment $twig);

    public function getTwig(): Environment;

    /**
     * @param string $name
     *
     * @return ColumnInterface
     */
    public function get(string $name): ColumnInterface;

    /**
     * @param string $name
     *
     * @return $this
     */
    public function remove(string $name): GridInterface;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * @return \Cwd\GridBundle\Column\ColumnInterface[]
     */
    public function all(): array;

    public function hasOption(string $name);

    public function getOption(string $name, $default = null);

    public function getOptions(): array;

    public function setSortField(ColumnInterface $field, $sortDir = 'ASC'): GridInterface;

    public function setChildren($children);
}
