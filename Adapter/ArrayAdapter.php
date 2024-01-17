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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter as PagerfantaArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ArrayAdapter implements AdapterInterface
{
    private ArrayCollection $data;
    private PropertyAccessor $accessor;

    public function __construct()
    {
        $this->accessor = new PropertyAccessor();
        $this->data = new ArrayCollection();
    }

    public function getData(GridInterface $grid): Pagerfanta
    {
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

    private function sortBy(string $field, string $dir = 'ASC'): void
    {
        $iterator = $this->data->getIterator();
        $accessor = $this->accessor;
        /** @phpstan-ignore-next-line */
        $iterator->uasort(function ($a, $b) use ($accessor, $field, $dir) {
            if ('ASC' === $dir) {
                return ($accessor->getValue($a, $field) < $accessor->getValue($b, $field)) ? -1 : 1;
            } elseif ('DESC' === $dir) {
                return ($accessor->getValue($a, $field) > $accessor->getValue($b, $field)) ? -1 : 1;
            } else {
                throw new \Exception('Unknown direction for sorting - (ASC or DESC)');
            }
        });

        $this->data = new ArrayCollection(iterator_to_array($iterator));
    }

    public function getPager(Collection $data, GridInterface $grid): Pagerfanta
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
                        return false !== strpos($accessor->getValue($row, $field), $value);
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

    public function setData(array $data): ArrayAdapter
    {
        $this->data = new ArrayCollection($data);

        return $this;
    }
}
