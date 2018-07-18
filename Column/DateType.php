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

use Cwd\GridBundle\Grid\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    /**
     * @param mixed             $value
     * @param mixed             $object
     * @param mixed             $primary
     * @param \Twig_Environment $twig
     *
     * @return string
     */
    public function render($value, $object, $primary, \Twig_Environment $twig)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof \DateTime) {
            throw new InvalidArgumentException('%s is not of expected \DateTime', $this->getName());
        }

        return $value->format($this->getOption('format')['read']);
    }

    /**
     * @param \Twig_Environment $twig
     *
     * @return string
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderFilter(\Twig_Environment $twig)
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

    public function setFilter($filter): AbstractColumn
    {
        $this->filter[] = $filter;

        return $this;
    }
}
