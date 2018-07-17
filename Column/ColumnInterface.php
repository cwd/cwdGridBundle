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

use Symfony\Component\PropertyAccess\PropertyAccessor;

interface ColumnInterface
{
    /**
     * @return string
     */
    public function buildColumnOptions(): array;

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * @return string
     */
    public function getField(): ?string;

    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @param mixed             $value
     * @param mixed             $object
     * @param mixed             $primary
     * @param \Twig_Environment $twig
     *
     * @return mixed
     */
    public function render($value, $object, $primary, \Twig_Environment $twig);

    /**
     * @param mixed            $object
     * @param string           $field
     * @param string           $primary
     * @param PropertyAccessor $accessor
     *
     * @return mixed
     */
    public function getValue($object, $field, $primary, $accessor);

    public function setIsSorted(bool $state): ColumnInterface;

    public function setSortDir(?string $dir = null): ColumnInterface;

    public function isSorted(): bool;

    public function getSortDir(): ?string;
}
