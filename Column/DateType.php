<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
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
    protected ?array $filter = [];

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'align' => 'right',
            'cellAlign' => 'right',
            'format' => [
                'write' => 'Y-m-d H:i:s',
                'read' => 'Y-m-d H:i:s',
            ],
            'width' => 150,
            'type' => 'date',
        ]);
    }

    public function render(mixed $value, mixed $object, string|int $primary, Environment $twig): string
    {
        if (null === $value) {
            return "";
        }

        if (!$value instanceof \DateTimeInterface) {
            throw new UnexpectedTypeException($value, "\DateTime");
        }

        if (null !== $this->getOption('template')) {
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

    public function renderFilter(Environment $twig): string
    {
        $filters = $this->getFilter();
        $value = [
            'from' => '',
            'to' => '',
        ];

        foreach ($filters as $filter) {
            if (isset($filter->operator) && 'gteq' == $filter->operator) {
                $value['from'] = $filter->value; // @phpstan-ignore-line
            }

            if (isset($filter->operator) && 'lteq' == $filter->operator) {
                $value['to'] = $filter->value; // @phpstan-ignore-line
            }
        }

        return $twig->render('@CwdGrid/filter/date.html.twig', [
            'column' => $this,
            'value' => $value,
        ]);
    }
}
