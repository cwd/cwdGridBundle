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

namespace Cwd\GridBundle\Exception;

class AdapterException extends \InvalidArgumentException
{
    public static function dependencyNotFound($class, $dependency)
    {
        return new self(sprintf('Missing dependency "%s" for %s', $dependency, $class));
    }
}
