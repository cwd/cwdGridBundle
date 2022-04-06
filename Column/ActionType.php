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

class ActionType extends AbstractColumn
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'align' => 'right',
            'searchable' => false,
            'sortable' => false,
            'actions' => [],
            'actions_params' => [],
            'filter' => false,
            'template' => '@CwdGrid/column/actions.html.twig',
            'width' => 200,
            'attr' => [
                'class' => 'grid-action',
            ],
        ]);

        $resolver->setAllowedTypes('actions', 'array');
        $resolver->setAllowedTypes('actions_params', 'array');
    }
}
