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
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'cellAlign' => 'center',
            'sortable' => false,
            'searchable' => false,
            'template' => '@CwdGrid/column/image.html.twig',
            'maxWidth' => '100px',
        ]);
    }
}
