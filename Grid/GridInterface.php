<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
 */

declare(strict_types=1);

namespace Cwd\GridBundle\Grid;

use Cwd\GridBundle\Adapter\AdapterInterface;
use Cwd\GridBundle\Column\ColumnInterface;
use Cwd\GridBundle\GridBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Twig\Environment;

interface GridInterface
{
    public function buildGrid(GridBuilderInterface $builder, array $options): void;

    public function getAdapter(): AdapterInterface;

    /**
     * @return AbstractGrid
     */
    public function setAdapter(AdapterInterface $adapter);

    public function setTwig(Environment $twig): void;

    public function getTwig(): Environment;

    public function get(string $name): ColumnInterface;

    /**
     * @return $this
     */
    public function remove(string $name): GridInterface;

    public function has(string $name): bool;

    /**
     * @return \Cwd\GridBundle\Column\ColumnInterface[]
     */
    public function all(): array;

    public function getData(): array;

    public function hasOption(string $name): bool;

    public function getOption(string $name, mixed $default = null): mixed;

    public function getOptions(): array;

    public function setSortField(ColumnInterface $field, string $sortDir = 'ASC'): GridInterface;

    public function setChildren(array $children): self;

    public function getQueryBuilder(ObjectManager $objectManager, array $params = []): QueryBuilder;
}
