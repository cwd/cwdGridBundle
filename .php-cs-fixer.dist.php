<?php
/*
 * This file is part of the cwdGridBundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->notName('*.twig')
    ->in([__DIR__]);

$year = 2022;

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@PHP81Migration' => true,
        'declare_strict_types' => true,
        'header_comment' => [
            'header' => <<<EOF
This file is part of the cwd/grid-bundle

©{$year} cwd.at GmbH <office@cwd.at>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF
            ,
            'location' => 'after_open',
            'separate' => 'bottom',
        ],
    ])
    ->setFinder($finder);
