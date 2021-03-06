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

namespace Cwd\GridBundle\Grid;

use Cwd\GridBundle\Adapter\AdapterInterface;
use Cwd\GridBundle\Column\AbstractColumn;
use Cwd\GridBundle\Column\ColumnInterface;
use Cwd\GridBundle\GridBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class AbstractGrid.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
abstract class AbstractGrid implements GridInterface, \IteratorAggregate
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $children = [];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected $accessor;

    /**
     * @var null|string
     */
    protected $primary = null;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * AbstractGrid constructor.
     *
     * @param array $options
     */
    public function __construct(TranslatorInterface $translator, array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->translator = $translator;
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param Environment $twig
     */
    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @param EntityManagerInterface $objectManager
     *
     * @return $this
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;

        return $this;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * generate gridid.
     *
     * @return string
     */
    public function getId()
    {
        $data = [
            get_class($this),
            $this->getOption('data_route_options'),
            $this->getOption('template'),
        ];

        return md5(serialize($data));
    }

    public function setSortField(ColumnInterface $field, $sortDir = 'ASC'): GridInterface
    {
        foreach ($this->all() as $column) {
            $column->setIsSorted(false);
            $column->setSortDir(null);
        }

        $field->setIsSorted(true);
        $field->setSortDir($sortDir);

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildGrid(GridBuilderInterface $builder, array $options)
    {
    }

    /**
     * @return array
     */
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

    /**
     * @param array|\Traversable $rows
     *
     * @return array
     */
    protected function parseData($rows)
    {
        $data = [];
        foreach ($rows as $row) {
            $rowData = [];

            foreach ($this->all() as $column) {
                /** @var ColumnInterface $column */
                $value = $column->getValue($row, $column->getField(), $this->findPrimary(), $this->accessor);
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

    /**
     * Get value of primary column.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    public function getPrimaryValue($object)
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

    /**
     * @return null|string
     *
     * @throws \InvalidArgumentException
     */
    public function findPrimary()
    {
        foreach ($this->all() as $column) {
            if (true === $column->getOption('identifier')) {
                return $column->getName();
            }
        }

        throw new \InvalidArgumentException('no column marked as identifier!');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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

    public function getColumnDefinition()
    {
        $columns = [];
        /** @var AbstractColumn $column */
        foreach ($this->children as $column) {
            $column->setTranslator($this->translator);
            $columns[] = $column->buildColumnOptions();
        }

        return $columns;
    }

    public function getQueryBuilder(EntityManagerInterface $objectManager, array $params = []): QueryBuilder
    {
        throw new \InvalidArgumentException('This method is only allowed when using DoctrineAdapter');
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
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @param string $name
     *
     * @return ColumnInterface
     */
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

    /**
     * @param string $name
     *
     * @return $this
     */
    public function remove(string $name): GridInterface
    {
        unset($this->children[$name]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
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
     *
     * @return $this
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
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
     * @return AbstractGrid
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }
}
