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

namespace Cwd\GridBundle\Adapter;

use Cwd\GridBundle\Column\ColumnInterface;
use Cwd\GridBundle\Exception\AdapterException;
use Cwd\GridBundle\Grid\GridInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class DoctrineAdapter implements AdapterInterface
{
    /** @var Registry|null */
    private $doctrine;

    public function getData(GridInterface $grid): Pagerfanta
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $grid->getQueryBuilder($this->getDoctrineRegistry()->getManager(), $grid->all());

        if (null !== $grid->getOption('sortField')) {
            $field = $grid->getOption('sortField');
            if ($grid->has($field)) {
                $column = $grid->get($field);
                $grid->setSortField($column, $grid->getOption('sortDir'));
                $queryBuilder->orderBy($column->getSqlField(), $grid->getOption('sortDir'));
            }
        }

        if ($grid->getOption('filter', false)) {
            $this->addSearch($queryBuilder, $grid);
        }

        return $this->getPager($queryBuilder, $grid);
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return Pagerfanta
     */
    public function getPager(QueryBuilder $queryBuilder, GridInterface $grid)
    {                                              // Not sure about this - was false
        $adapter = new QueryAdapter($queryBuilder, true);
        $pager = new Pagerfanta($adapter);

        $page = $grid->getOption('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $pager->setCurrentPage($page)
              ->setMaxPerPage($grid->getOption('limit', 10));

        return $pager;
    }

    /**
     * @param QueryBuilder      $queryBuilder
     * @param ColumnInterface[] $columns
     */
    protected function addSearch(QueryBuilder $queryBuilder, GridInterface $grid): void
    {
        $filter = $grid->getOption('filter');
        $where = $queryBuilder->expr()->andX();
        $i = 0;

        foreach ($filter as $filterSearch) {
            if (!$grid->has($filterSearch->property)) {
                continue;
            }

            $property = sprintf(':%s%s', $filterSearch->property, $i);

            $column = $grid->get($filterSearch->property);
            $filterSearch->value = $column->viewToData($filterSearch->value);

            switch ($filterSearch->operator) {
                case 'eq':
                    $where->add($queryBuilder->expr()->eq($column->getSqlField(), $property));
                    $queryBuilder->setParameter($property, $filterSearch->value);
                    break;
                case 'like':
                    $value = $filterSearch->value;

                    if ('true' == $value || 'false' == $value) {
                        $value = 'true' == $value ? 1 : 0;
                    }

                    $where->add($queryBuilder->expr()->like($column->getSqlField(), $property));
                    $queryBuilder->setParameter($property, sprintf('%%%s%%', $value));
                    break;
                case 'gteq':
                    $where->add($queryBuilder->expr()->gte($column->getSqlField(), $property));
                    $queryBuilder->setParameter($property, $filterSearch->value);
                    break;
                case 'lteq':
                    $where->add($queryBuilder->expr()->lte($column->getSqlField(), $property));
                    $queryBuilder->setParameter($property, $filterSearch->value);
                    break;
            }

            ++$i;
        }

        if (count($where->getParts()) > 0) {
            $queryBuilder->andWhere($where);
        }
    }

    /**
     * @return Registry|null
     */
    public function getDoctrineRegistry(): ?Registry
    {
        if (null === $this->doctrine) {
            throw AdapterException::dependencyNotFound(self::class, 'Doctrine');
        }

        return $this->doctrine;
    }

    /**
     * @param Registry|null $doctrine
     *
     * @return DoctrineAdapter
     */
    public function setDoctrineRegistry(?Registry $doctrine): DoctrineAdapter
    {
        $this->doctrine = $doctrine;

        return $this;
    }
}
