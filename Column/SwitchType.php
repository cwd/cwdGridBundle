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

class SwitchType extends CheckboxType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'route' => null,
            'align' => 'center',
            'cellAlign' => 'center',
            'attr' => [
                'class' => 'bootstrap-switch',
                'data-on-text' => 'generic.yes',
                'data-off-text' => 'generic.no',
                'data-size' => 'mini',
            ],
            'template' => '@CwdGrid/column/switch.html.twig',
        ]);

        $resolver->setRequired('route');
    }

    /**
     * @param mixed             $value
     * @param mixed             $object
     * @param mixed             $primary
     * @param \Twig_Environment $twig
     *
     * @return mixed
     */
    public function render($value, $object, $primary, \Twig_Environment $twig)
    {
        return $this->renderTemplate(
            $twig,
            $this->getOption('template'),
            [
                'value' => $value,
                'object' => $object,
                'primary' => $primary,
                'attributes' => $this->getOption('attr'),
                'route' => $this->getOption('route'),
            ]
        );
    }
}
