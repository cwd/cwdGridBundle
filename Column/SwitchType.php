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

class SwitchType extends CheckboxType
{
    public function configureOptions(OptionsResolver $resolver): void
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

    public function render(mixed $value, mixed $object, mixed $primary, Environment $twig): string
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
