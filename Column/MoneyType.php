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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class NumberType.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
class MoneyType extends AbstractColumn
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
            'currency' => '€',
            'format' => '%s %s',
            'number_format' => [
                'thousands_seperator' => '.',
                'dec_point' => ',',
                'decimals' => 2,
            ],
            'width' => 150,
            'operator' => 'eq',
        ]);
    }

    public function render(mixed $value, mixed $object, string|int $primary, Environment $twig): string
    {
        if (null === $value) {
            return "";
        }

        return sprintf(
            $this->getOption('format'),
            $this->getOption('currency'),
            number_format(
                $value,
                $this->getOption('number_format')['decimals'],
                $this->getOption('number_format')['dec_point'],
                $this->getOption('number_format')['thousands_seperator']
            )
        );
    }
}
