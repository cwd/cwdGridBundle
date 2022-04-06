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

class ChoiceType extends AbstractColumn
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'align' => 'left',
            'cellAlign' => 'left',
            'operator' => 'eq',
            'data' => [],
        ]);

        $resolver->setAllowedTypes('data', 'array');
    }

    public function renderFilter(Environment $twig): string
    {
        $value = (null !== $this->getFilter() && '' != isset($this->getFilter()->value)) ? $this->getFilter()->value : '';

        return $twig->render('@CwdGrid/filter/choice.html.twig', [
            'data' => $this->getOption('data'),
            'value' => $value,
            'column' => $this,
        ]);
    }
}
