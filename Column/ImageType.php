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
 * Class ImageType.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
class ImageType extends AbstractColumn
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'cellAlign' => 'center',
            'sortable' => false,
            'searchable' => false,
            'template' => '@CwdGrid/column/image.html.twig',
            'maxWidth' => '100px',
        ));
    }
}
