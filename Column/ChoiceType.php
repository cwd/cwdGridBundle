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
use Twig\Environment;

class ChoiceType extends AbstractColumn
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
            'operator' => 'eq',
            'data' => [],
        ));

        $resolver->setAllowedTypes('data', 'array');
    }

    public function renderFilter(Environment $twig)
    {
        $value = (null !== $this->getFilter() && '' != isset($this->getFilter()->value)) ? $this->getFilter()->value : '';

        return $twig->render('@CwdGrid/filter/choice.html.twig', [
            'data' => $this->getOption('data'),
            'value' => $value,
            'column' => $this,
        ]);
    }
}
