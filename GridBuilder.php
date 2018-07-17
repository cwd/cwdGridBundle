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

use Cwd\GridBundle\Adapter\AdapterInterface;
use Cwd\GridBundle\Column\ColumnInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GridBuilder implements GridBuilderInterface, \IteratorAggregate
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var array */
    public $options;

    /**
     * @var ColumnInterface[]
     */
    public $children = array();

    /** @var AdapterInterface */
    protected $adapter;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param AdapterInterface         $adapter
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     * @param array                    $options    The form options
     *
     * @throws InvalidArgumentException if the data class is not a valid class or if
     *                                  the name contains invalid characters
     */
    public function __construct(AdapterInterface $adapter, EventDispatcherInterface $dispatcher, array $options = array())
    {
        $this->dispatcher = $dispatcher;
        $this->options = $options;
        $this->adapter = $adapter;
    }

    /**
     * @param ColumnInterface $child
     *
     * @return $this
     */
    public function add(ColumnInterface $child): GridBuilder
    {
        $this->children[$child->getName()] = $child;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ColumnInterface
     */
    public function get(string $name): ColumnInterface
    {
        if ($this->has($name)) {
            return $this->children[$name];
        }

        throw new \InvalidArgumentException(sprintf('The child with the name "%s" does not exist.', $name));
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function remove(string $name)
    {
        unset($this->children[$name]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        return isset($this->children[$name]);
    }

    /**
     * @return ColumnInterface[]
     */
    public function all()
    {
        return $this->children;
    }

    /**
     * @return GridBuilder
     */
    protected function getGridConfig(): GridBuilder
    {
        $config = clone $this;

        return $config;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->children);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasOption(string $name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @param string      $name
     * @param string|null $default
     *
     * @return misc
     */
    public function getOption(string $name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * @param AdapterInterface $adapter
     *
     * @return GridBuilder
     */
    public function setAdapter(AdapterInterface $adapter): GridBuilder
    {
        $this->adapter = $adapter;

        return $this;
    }
}
