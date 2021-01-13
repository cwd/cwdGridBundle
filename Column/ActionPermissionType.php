<?php

/*
 * This file is part of the PowerUI Application.
 *
 * (c)2019 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cwd\GridBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionPermissionType extends ActionType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'permission' => '',
            'template' => '@CwdGrid/column/permission_actions.html.twig',
        ));

        $resolver->setAllowedTypes('permission', 'string');
    }
}
