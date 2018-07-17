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
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class GridExtension extends \Twig_Extension
{
    protected $jsOptions = [];
    protected $router;

    public function __construct(Router $router, $options = [])
    {
        if (!isset($options['js_options'])) {
            $options['js_options'] = [];
        }

        $this->jsOptions = $options['js_options'];
        $this->router = $router;
    }

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('grid', [$this, 'grid'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        );
    }

    public function grid(\Twig_Environment $twig, GridInterface $grid, array $options = [])
    {
        $options = array_merge($options, $this->jsOptions);

        return $twig->render('@CwdGrid/grid.html.twig', [
            'grid' => $grid,
            'options' => $options,
            'pager' => '',
        ]);
    }
}
