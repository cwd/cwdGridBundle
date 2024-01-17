<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
 */

declare(strict_types=1);

namespace Cwd\GridBundle\Exception;

class AdapterException extends \InvalidArgumentException
{
    public static function dependencyNotFound(string $class, string $dependency): self
    {
        return new self(sprintf('Missing dependency "%s" for %s', $dependency, $class));
    }
}
