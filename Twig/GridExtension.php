<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
 */

declare(strict_types=1);
/*
 * This file is part of cwdGridBundle
 *
 * (c)2018 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cwd\GridBundle\Twig;

use Cwd\GridBundle\Grid\GridInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GridExtension extends AbstractExtension
{
    protected array $jsOptions = [];

    public function __construct(array $options = [])
    {
        if (!isset($options['js_options'])) {
            $options['js_options'] = [];
        }

        $this->jsOptions = $options['js_options'];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('grid', [$this, 'grid'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function grid(Environment $twig, GridInterface $grid, array $options = []): string
    {
        $options = array_merge($options, $this->jsOptions);

        return $twig->render($grid->getOption('template'), [
            'grid' => $grid,
            'options' => $options,
            'pager' => '',
        ]);
    }
}
