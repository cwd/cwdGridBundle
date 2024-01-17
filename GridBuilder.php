<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
 */

declare(strict_types=1);

namespace Cwd\GridBundle;

use Cwd\GridBundle\Adapter\AdapterInterface;
use Cwd\GridBundle\Column\ColumnInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GridBuilder implements GridBuilderInterface, \IteratorAggregate
{
    /**
     * @var ColumnInterface[]
     */
    public array $children = [];

    protected array $data = [];

    public function __construct(
        protected AdapterInterface $adapter,
        protected EventDispatcherInterface $dispatcher,
        public array $options = []
    ) {
    }

    public function add(ColumnInterface $child): self
    {
        $this->children[$child->getName()] = $child;

        return $this;
    }

    public function get(string $name): ColumnInterface
    {
        if ($this->has($name)) {
            return $this->children[$name];
        }

        throw new \InvalidArgumentException(sprintf('The child with the name "%s" does not exist.', $name));
    }

    public function remove(string $name): self
    {
        unset($this->children[$name]);

        return $this;
    }

    public function has(string $name): bool
    {
        return isset($this->children[$name]);
    }

    public function all(): array
    {
        return $this->children;
    }

    protected function getGridConfig(): self
    {
        $config = clone $this;

        return $config;
    }

    public function count(): int
    {
        return count($this->children);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->all());
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    public function getOption(string $name, ?string $default = null): ?string
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    public function setAdapter(AdapterInterface $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }
}
