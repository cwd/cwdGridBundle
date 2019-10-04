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

use Cwd\GridBundle\Exception\AdapterException;
use Cwd\GridBundle\Grid\GridInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter as PagerfantaArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ArrayAdapter implements AdapterInterface
{
    /** @var ArrayCollection */
    private $data;
    private $accessor;

    public function __construct()
    {
        $this->accessor = new PropertyAccessor();
    }

    public function getData(GridInterface $grid): Pagerfanta
    {
        //$queryBuilder = $grid->getQueryBuilder($this->getDoctrineRegistry()->getManager(), $grid->all());


        if (null !== $grid->getOption('sortField')) {
            $field = $grid->getOption('sortField');
            if ($grid->has($field)) {
                $column = $grid->get($field);
                $grid->setSortField($column, $grid->getOption('sortDir'));
                /* @todo */
                $this->sortBy($column->getField(), $grid->getOption('sortDir'));
            }
        }

        if ($grid->getOption('filter', false)) {
            $this->addSearch($grid);
        }

        return $this->getPager($this->data, $grid);
    }

    private function sortBy($field, $dir = 'ASC')
    {
        $iterator = $this->data->getIterator();
        $accessor = $this->accessor;
        $iterator->uasort(function ($a, $b) use ($accessor, $field, $dir) {
            if ($dir === 'ASC') {
                return ($accessor->getValue($a, $field) < $accessor->getValue($b, $field)) ? -1 : 1;
            } elseif ($dir === 'DESC') {
                return ($accessor->getValue($a, $field) > $accessor->getValue($b, $field)) ? -1 : 1;
            } else {
                throw new \Exception('Unknown direction for sorting - (ASC or DESC)');
            }
        });

        $this->data = new ArrayCollection(iterator_to_array($iterator));
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return Pagerfanta
     */
    public function getPager(Collection $data, GridInterface $grid)
    {
        $adapter = new PagerfantaArrayAdapter(iterator_to_array($data));
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
    protected function addSearch(GridInterface $grid): void
    {
        $filter = $grid->getOption('filter');
        $data = $this->data;
        $accessor = $this->accessor;

        foreach ($filter as $filterSearch) {
            if (!$grid->has($filterSearch->property)) {
                continue;
            }

            $column = $grid->get($filterSearch->property);
            $filterSearch->value = $column->viewToData($filterSearch->value);

            $field = $column->getField();
            $value = $filterSearch->value;

            switch ($filterSearch->operator) {
                case 'eq':
                    $data = $data->filter(function ($row) use ($accessor, $field, $value) {
                        return $accessor->getValue($row, $field) == $value;
                    });
                    break;
                case 'like':
                    $data = $data->filter(function ($row) use ($accessor, $field, $value) {
                        return strpos($accessor->getValue($row, $field), $value) !== false;
                    });
                    break;
                case 'gteq':
                    $data = $data->filter(function ($row) use ($accessor, $field, $value) {
                        $fieldValue = $accessor->getValue($row, $field);
                        if ($fieldValue instanceof \DateTimeInterface) {
                            $value = new \DateTime($value);
                        }
                        return $fieldValue >= $value;
                    });
                    break;
                case 'lteq':
                    $data = $data->filter(function ($row) use ($accessor, $field, $value) {
                        $fieldValue = $accessor->getValue($row, $field);
                        if ($fieldValue instanceof \DateTimeInterface) {
                            $value = new \DateTime($value);
                        }
                        return $fieldValue <= $value;
                    });
                    break;
            }
        }

        $this->data = $data;
    }

    private function filterEquals($field, $value, Collection $data)
    {
        $accessor = $this->accessor;

    }

    /**
     * @param array $data
     * @return ArrayAdapter
     */
    public function setData(array $data): ArrayAdapter
    {
        $this->data = new ArrayCollection($data);
        return $this;
    }
}
