<?php

/*
 * This file is part of the <LKO label generator>
 *
 * ©2025 cwd.at GmbH <office@cwd.at>
 *
 * Unauthorized copying or modification of this file, via any medium is strictly prohibited
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace App\Infrastructure\Ui\Admin\Grid\Column;

use Cwd\GridBundle\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class EnumType extends AbstractColumn
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'align' => 'left',
            'cellAlign' => 'left',
            'operator' => 'eq',
            'class' => '',
            'allOptionsLabel' => 'all',
            'sortable' => true,
            'searchable' => true,
            'translatable' => true,
        ]);

        $resolver->setAllowedTypes('class', 'string');
        $resolver->setAllowedTypes('allOptionsLabel', 'string');
        $resolver->setAllowedValues('class', enum_exists(...));
    }

    public function render(mixed $value, mixed $object, string|int $primary, Environment $twig): mixed
    {
        if ($this->getOption('translatable') && null !== $value) {
            if ($value instanceof \BackedEnum) {
                $value = $this->translate((string)$value->value, $this->getOption('translatable_domain'));
            }
        }

        return parent::render($value, $object, $primary, $twig);
    }

    public function renderFilter(Environment $twig): string
    {
        $enumClassName = $this->getOption('class');
        if (!class_exists($enumClassName) || !\enum_exists($enumClassName)) {
            return '';
        }

        return $twig->render('@CwdGrid/filter/enum.html.twig', [
            'cases' => $enumClassName::cases(),
            'value' => $this->getFirstFilterValue(),
            'column' => $this,
            'allOptionsLabel' => $this->getOption('allOptionsLabel'),
        ]);
    }
}
