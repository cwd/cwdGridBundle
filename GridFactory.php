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
use Cwd\GridBundle\Adapter\DoctrineAdapter;
use Cwd\GridBundle\Exception\UnexpectedTypeException;
use Cwd\GridBundle\Grid\GridInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class GridFactory
{
    /**
     * @var AdapterInterface[]
     */
    protected array $adapters = [];

    protected ?TranslatorInterface $translator;

    public function __construct(protected Environment $twig, protected array $options = [])
    {
    }

    public function create(string $type = 'Cwd\GridBundle\Grid\AbstractGrid', array $options = [], string $adapter = DoctrineAdapter::class): GridInterface
    {
        if (!is_string($type)) {
            throw new UnexpectedTypeException($type, 'string');
        }

        if (!isset($options['template'])) {
            $options['template'] = $this->options['template'];
        }

        if ($this->translator !== null) {
            $options['pagerfantaOptions']['prev_message'] = $this->translator->trans(
                $this->options['pagerfantaOptions']['prev_message'],
                [],
                $this->options['pagerfantaOptions']['translation_domain']
            );
            $options['pagerfantaOptions']['next_message'] = $this->translator->trans(
                $this->options['pagerfantaOptions']['next_message'],
                [],
                $this->options['pagerfantaOptions']['translation_domain']
            );
        }

        $adapter = $this->getAdapter($adapter);
        $type = $this->getType($type, $adapter, $options);

        $builder = new GridBuilder($adapter, new EventDispatcher(), $options);

        $type->buildGrid($builder, array_merge($type->getOptions(), $options));
        $type->setChildren($builder->children);

        foreach ($type->getOption('filter') as $filter) {
            $type->get($filter->property)->setFilter((array) $filter);
        }

        return $type;
    }

    public function getType(string $name, AdapterInterface $adapter, array $options = []): GridInterface
    {
        if (class_exists($name) && in_array('Cwd\GridBundle\Grid\GridInterface', class_implements($name))) { // @phpstan-ignore-line
            /** @var GridInterface $type */
            $type = new $name($this->translator, $options);
            $type->setAdapter($adapter);
            $type->setTwig($this->twig);
        } else {
            throw new \InvalidArgumentException(sprintf('Could not load type "%s"', $name));
        }

        return $type;
    }

    public function getAdapter(string $adapter): AdapterInterface
    {
        if ($this->hasAdapter($adapter)) {
            return $this->adapters[$adapter];
        }

        throw new \InvalidArgumentException(sprintf('Adapter %s is not known. Did you tag it with "cwd_grid.adapter"', $adapter));
    }

    public function addAdapter(AdapterInterface $adapter): GridFactory
    {
        $this->adapters[get_class($adapter)] = $adapter;

        return $this;
    }

    public function hasAdapter(string $adapter): bool
    {
        return isset($this->adapters[$adapter]);
    }

    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    public function setTranslator(?TranslatorInterface $translator): GridFactory
    {
        $this->translator = $translator;

        return $this;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

    public function setTwig(Environment $twig): GridFactory
    {
        $this->twig = $twig;

        return $this;
    }
}
