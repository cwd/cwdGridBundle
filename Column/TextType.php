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
 * Class TextType.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
class TextType extends AbstractColumn
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'align' => 'left',
            'cellAlign' => 'left',
            'type' => 'string',
        ));

        $resolver->setAllowedTypes('attr', 'array');
    }
}
