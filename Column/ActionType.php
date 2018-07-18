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

class ActionType extends AbstractColumn
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
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
        ));

        $resolver->setAllowedTypes('actions', 'array');
        $resolver->setAllowedTypes('actions_params', 'array');
    }
}
