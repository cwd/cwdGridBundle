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

namespace Cwd\GridBundle;

use Cwd\GridBundle\Column\ColumnInterface;

/**
 * Interface GridBuilderInterface.
 */
interface GridBuilderInterface extends \Countable
{
    /**
     * @param ColumnInterface $child
     *
     * @return $this
     */
    public function add(ColumnInterface $child);

    /**
     * Returns a child by name.
     *
     * @param string $name The name of the child
     *
     * @return ColumnInterface
     *
     * @throws Cwd\GridBundle\Grid\Exception\InvalidArgumentException if the given child does not exist
     */
    public function get(string $name): ColumnInterface;

    /**
     * Removes the field with the given name.
     *
     * @param string $name
     *
     * @return GridBuilderInterface The builder object
     */
    public function remove(string $name);

    /**
     * Returns whether a field with the given name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name);

    /**
     * Returns the children.
     *
     * @return array
     */
    public function all();
}
