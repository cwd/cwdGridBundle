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
            'data' => [['value' => true, 'label' => $this->translate('Yes')], ['value' => false, 'label' => $this->translate('No')]],
        ));
    }

    public function render($value, $object, $primary, \Twig_Environment $twig)
    {
        if (null === $this->getOption('template')) {
            return ($value || !empty($value)) ? '<i class="far fa-check-circle"></i>' : '<i class="far fa-circle"></i>';
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
