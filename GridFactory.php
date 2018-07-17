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
use Cwd\GridBundle\Adapter\DoctrineAdapter;
use Cwd\GridBundle\Exception\UnexpectedTypeException;
use Cwd\GridBundle\Grid\GridInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Translation\TranslatorInterface;

class GridFactory
{
    /**
     * @var AdapterInterface[]
     */
    protected $adapters = [];

    /**
     * @var TranslatorInterface|null
     */
    protected $translator;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @param \Twig_environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param string $type
     * @param array  $options
     *
     * @return GridInterface
     */
    public function create($type = 'Cwd\GridBundle\Grid\AbstractGrid', array $options = array())
    {
        if (!is_string($type)) {
            throw new UnexpectedTypeException($type, 'string');
        }

        // @ToDo Make the Adapter Configurable
        $adapter = $this->getAdapter(DoctrineAdapter::class);
        $type = $this->getType($type, $adapter, $options);

        $builder = new GridBuilder($adapter, new EventDispatcher(), $options);

        $type->buildGrid($builder, array_merge($type->getOptions(), $options));
        $type->setChildren($builder->children);

        return $type;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return GridInterface
     */
    public function getType(string $name, AdapterInterface $adapter, array $options = [])
    {
        if (class_exists($name) && in_array('Cwd\GridBundle\Grid\GridInterface', class_implements($name))) {
            $type = new $name($this->translator, $options);
            $type->setAdapter($adapter);
            $type->setTwig($this->twig);
        } else {
            throw new \InvalidArgumentException(sprintf('Could not load type "%s"', $name));
        }

        return $type;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter(string $adapter): AdapterInterface
    {
        if ($this->hasAdapter($adapter)) {
            return $this->adapters[$adapter];
        }

        throw new \InvalidArgumentException(sprintf('Adapter %s is not known. Did you tag it with "cwd_grid.adapter"', $adapter));
    }

    /**
     * @param AdapterInterface $adapter
     *
     * @return GridFactory
     */
    public function addAdapter(AdapterInterface $adapter): GridFactory
    {
        $this->adapters[get_class($adapter)] = $adapter;

        return $this;
    }

    public function hasAdapter(string $adapter): bool
    {
        return isset($this->adapters[$adapter]);
    }

    /**
     * @return null|TranslatorInterface
     */
    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param null|TranslatorInterface $translator
     *
     * @return GridFactory
     */
    public function setTranslator(?TranslatorInterface $translator): GridFactory
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig(): \Twig_Environment
    {
        return $this->twig;
    }

    /**
     * @param \Twig_Environment $twig
     *
     * @return GridFactory
     */
    public function setTwig(\Twig_Environment $twig): GridFactory
    {
        $this->twig = $twig;

        return $this;
    }
}
