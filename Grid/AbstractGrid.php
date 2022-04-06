<?php
/*
 * This file is part of the cwd/grid-bundle
 *
 * ©2022 cwd.at GmbH <office@cwd.at>
 *
 * see LICENSE file for details
 */

declare(strict_types=1);

namespace Cwd\GridBundle\Grid;

use Cwd\GridBundle\Adapter\AdapterInterface;
use Cwd\GridBundle\Column\AbstractColumn;
use Cwd\GridBundle\Column\ColumnInterface;
use Cwd\GridBundle\GridBuilderInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class AbstractGrid.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
abstract class AbstractGrid implements GridInterface, \IteratorAggregate
{
    protected array $options = [];
    protected array $children = [];
    protected ObjectManager $objectManager;
    protected TranslatorInterface $translator;
    protected PropertyAccessorInterface $accessor;
    protected ?string $primary = null;
    protected Environment $twig;
    protected AdapterInterface $adapter;

    public function __construct(TranslatorInterface $translator, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->translator = $translator;
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

    public function setObjectManager(ObjectManager $objectManager): self
    {
        $this->objectManager = $objectManager;

        return $this;
    }

    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }

    public function getId(): string
    {
        $data = [
            get_class($this),
            $this->getOption('data_route_options'),
            $this->getOption('template'),
        ];

        return md5(serialize($data));
    }

    public function setSortField(ColumnInterface $field, string $sortDir = 'ASC'): GridInterface
    {
        foreach ($this->all() as $column) {
            $column->setIsSorted(false);
            $column->setSortDir(null);
        }

        $field->setIsSorted(true);
        $field->setSortDir($sortDir);

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildGrid(GridBuilderInterface $builder, array $options): void
    {
    }

    public function getData(): array
    {
        $pager = $this->getAdapter()->getData($this);

        return [
            'totalCount' => $pager->getNbResults(),
            'data' => $this->parseData($pager->getCurrentPageResults()),
            'success' => true,
            'pager' => $pager,
        ];
    }

    protected function parseData(array|\Traversable $rows): array
    {
        $data = [];
        foreach ($rows as $row) {
            $rowData = [];

            foreach ($this->all() as $column) {
                /** @var ColumnInterface $column */
                $value = $column->getValue(
                    $row,
                    $column->getField(),
                    $this->findPrimary(),
                    $this->accessor,
                    $column->getOption('parentField')
                );
                $value = $column->render($value, $row, $this->getPrimaryValue($row), $this->twig);

                if ($column->getOption('translatable', false)) {
                    $value = $this->translator->trans($value, [], $column->getOption('translation_domain'));
                }

                $name = $column->getName();
                $rowData[$name] = $value;
            }

            $data[] = $rowData;
        }

        return $data;
    }

    public function getPrimaryValue(mixed $object): mixed
    {
        if (null === $this->primary) {
            $this->primary = $this->findPrimary();
        }

        /* special case when counting */
        if (is_array($object)) {
            $object = $object[0];
        }

        return $this->accessor->getValue($object, $this->primary);
    }

    public function findPrimary(): ?string
    {
        foreach ($this->all() as $column) {
            if (true === $column->getOption('identifier')) {
                return $column->getName();
            }
        }

        throw new \InvalidArgumentException('no column marked as identifier!');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => '@CwdGrid/grid.html.twig',
            'filter' => [],
            'page' => 1,
            'limit' => 20,
            'sortField' => null,
            'sortDir' => 'ASC',
            'listLength' => [10, 20, 50, 100],
            'pagerfantaOptions' => [],
        ]);

        $resolver->setRequired([
            'template',
        ]);

        $resolver->setAllowedTypes('filter', 'array');
        $resolver->setAllowedTypes('pagerfantaOptions', 'array');
    }

    public function getColumnDefinition(): array
    {
        $columns = [];
        /** @var AbstractColumn $column */
        foreach ($this->children as $column) {
            $column->setTranslator($this->translator);
            $columns[] = $column->buildColumnOptions();
        }

        return $columns;
    }

    public function getQueryBuilder(ObjectManager $objectManager, array $params = []): QueryBuilder
    {
        throw new \InvalidArgumentException('This method is only allowed when using DoctrineAdapter');
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function get(string $name): ColumnInterface
    {
        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        $name = str_replace('.', '_', $name);
        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        throw new \InvalidArgumentException(sprintf('The child with the name "%s" does not exist.', $name));
    }

    public function remove(string $name): GridInterface
    {
        unset($this->children[$name]);

        return $this;
    }

    public function has(string $name): bool
    {
        return isset($this->children[$name]);
    }

    /**
     * @return \Cwd\GridBundle\Column\ColumnInterface[]
     */
    public function all(): array
    {
        return $this->children;
    }

    /**
     * @param array<ColumnInterface> $children
     */
    public function setChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->all());
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
