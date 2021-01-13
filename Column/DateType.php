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

namespace Cwd\GridBundle\Column;

use Cwd\GridBundle\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class NumberType.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
class DateType extends AbstractColumn
{
    protected $filter = [];

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'align' => 'right',
            'cellAlign' => 'right',
            'format' => [
                'write' => 'Y-m-d H:i:s',
                'read' => 'Y-m-d H:i:s',
            ],
            'width' => 150,
            'type' => 'date',
        ));
    }

    public function render($value, $object, $primary, Environment $twig)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof \DateTimeInterface) {
            throw new UnexpectedTypeException($value, "\DateTime");
        }

        if ($this->getOption('template') !== null) {
            return $this->renderTemplate(
                $twig,
                $this->getOption('template'),
                [
                    'value' => $value,
                    'object' => $object,
                    'primary' => $primary,
                ]
            );
        }

        return $value->format($this->getOption('format')['read']);
    }

    public function renderFilter(Environment $twig)
    {
        $filters = $this->getFilter();
        $value = [
            'from' => '',
            'to' => '',
        ];

        foreach ($filters as $filter) {
            if (isset($filter->operator) && 'gteq' == $filter->operator) {
                $value['from'] = $filter->value;
            }

            if (isset($filter->operator) && 'lteq' == $filter->operator) {
                $value['to'] = $filter->value;
            }
        }

        return $twig->render('@CwdGrid/filter/date.html.twig', [
            'column' => $this,
            'value' => $value,
        ]);
    }

    public function setFilter($filter): ColumnInterface
    {
        $this->filter[] = $filter;

        return $this;
    }
}
