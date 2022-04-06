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
 * Class CheckboxType.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
class CheckboxType extends ChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'align' => 'right',
            'cellAlign' => 'right',
            'data' => [['value' => true, 'label' => 'generic.yes'], ['value' => false, 'label' => 'generic.no']],
        ]);
    }

    public function render(mixed $value, mixed $object, string|int $primary, Environment $twig): string
    {
        if (null === $this->getOption('template')) {
            return ($value || !empty($value)) ? '<i class="fad fa-check-circle text-success"></i>' : '<i class="fad fa-circle text-danger"></i>';
        }

        return parent::render($value, $object, $primary, $twig);
    }

    public function viewToData(mixed $value): mixed
    {
        return ('1' === $value) ? true : false;
    }

    public function dataToView(mixed $value): mixed
    {
        return ($value) ? '1' : '0';
    }
}
