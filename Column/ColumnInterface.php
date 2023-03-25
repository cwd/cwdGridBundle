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

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Environment;

interface ColumnInterface
{
    public function buildColumnOptions(): array;
    public function getOption(string $name, mixed $default = null): mixed;
    public function getField(): ?string;
    public function getSqlField(): ?string;
    public function getName(): ?string;
    public function render(mixed $value, mixed $object, string|int $primary, Environment $twig): string|int;
    public function getValue(mixed $object, string $field, string $primary, PropertyAccessorInterface $accessor, mixed $parentField = null): mixed;
    public function setIsSorted(bool $state): ColumnInterface;
    public function setSortDir(?string $dir = null): ColumnInterface;
    public function isSorted(): bool;
    public function getSortDir(): ?string;
    public function addFilter(iterable $filter): ColumnInterface;
    public function viewToData(mixed $value): mixed;
}
