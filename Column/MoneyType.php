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
class MoneyType extends AbstractColumn
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
            'currency' => '€',
            'format' => '%s %s',
            'number_format' => [
                'thousands_seperator' => '.',
                'dec_point' => ',',
                'decimals' => 2,
            ],
            'width' => 150,
            'operator' => 'eq',
        ));
    }

    public function render($value, $object, $primary, Environment $twig)
    {
        if (null === $value) {
            return null;
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
