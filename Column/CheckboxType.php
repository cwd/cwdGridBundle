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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class CheckboxType.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
class CheckboxType extends ChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'align' => 'right',
            'cellAlign' => 'right',
            'data' => [['value' => true, 'label' => 'generic.yes'], ['value' => false, 'label' => 'generic.no']],
        ));
    }

    public function render($value, $object, $primary, Environment $twig)
    {
        if (null === $this->getOption('template')) {
            return ($value || !empty($value)) ? '<i class="fad fa-check-circle text-success"></i>' : '<i class="fad fa-circle text-danger"></i>';
        }

        return parent::render($value, $object, $primary, $twig);
    }

    public function viewToData($value)
    {
        return ('1' === $value) ? true : false;
    }

    public function dataToView($value)
    {
        return ($value) ? '1' : '0';
    }
}
