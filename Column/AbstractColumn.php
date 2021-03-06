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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class AbstractColumn.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
abstract class AbstractColumn implements ColumnInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $field;
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    protected $isSorted = false;

    protected $sortDir = null;

    protected $filter = null;

    /**
     * AbstractColumn constructor.
     *
     * @param string $name
     * @param string $field
     * @param array  $options
     */
    public function __construct($name, $field, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
        $this->name = $name;
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getSqlField(): ?string
    {
        if (null === $this->getOption('sqlField')) {
            return $this->getField();
        }

        return $this->getOption('sqlField');
    }

    public function setIsSorted(bool $state): ColumnInterface
    {
        $this->isSorted = $state;

        return $this;
    }

    public function setSortDir(?string $dir = null): ColumnInterface
    {
        $this->sortDir = $dir;

        return $this;
    }

    public function isSorted(): bool
    {
        return $this->isSorted;
    }

    public function getSortDir(): ?string
    {
        return $this->sortDir;
    }

    /**
     * @param mixed             $value
     * @param mixed             $object
     * @param mixed             $primary
     * @param Environment $twig
     *
     * @return mixed
     */
    public function render($value, $object, $primary, Environment $twig)
    {
        if (is_callable($this->getOption('render'))) {
            $callable = $this->getOption('render');
            $value = $callable($value, $object, $primary);
        }

        if ($this->getOption('translatable')) {
            $value = $this->translate($value, $this->getOption('translatable_domain'));
        }

        /* dont use twig if no template is provided */
        if (null === $this->getOption('template')) {
            return $value;
        }

        /** Special case for count(*) */
        if (is_array($object)) {
            $object = $object[0];
        }

        return $this->renderTemplate(
            $twig,
            $this->getOption('template'),
            [
                'value' => $value,
                'object' => $object,
                'primary' => $primary,
            ]
        );
    }

    /**
     * @param Environment $twig
     *
     * @return string
     */
    public function renderFilter(Environment $twig)
    {
        $value = (null !== $this->getFilter() && '' != isset($this->getFilter()->value)) ? $this->getFilter()->value : '';

        return $twig->render('@CwdGrid/filter/text.html.twig', [
            'column' => $this,
            'value' => $value,
        ]);
    }

    /**
     * @param Environment $twig
     * @param string            $template
     * @param array             $options
     *
     * @return string
     */
    protected function renderTemplate(Environment $twig, $template, $options)
    {
        $options = array_merge($options, $this->getOptions());

        return $twig->render($template, $options);
    }

    /**
     * set defaults options.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'identifier' => false,
            'label' => null,
            'render' => null,
            'align' => 'left',
            'cellAlign' => 'left',
            'visible' => true,
            'sortable' => true,
            'searchable' => true,
            'width' => 'auto',
            'minWidth' => 'auto',
            'maxWidth' => 'auto',
            'ellipsis' => true,
            'translation_domain' => null,
            'translatable' => false,
            'attr' => [],
            'template' => null,
            'operator' => 'like',
            'sqlField' => null,
        ));

        $resolver->setAllowedTypes('attr', 'array');
    }

    public function getHeaderStyleOptions()
    {
        $options = [
            'align' => 'text-align',
            'width' => 'width',
            'minWidth' => 'min-width',
            'maxWidth' => 'max-width',
        ];

        $optionMap = [];
        foreach ($options as $key => $cssName) {
            $value = $this->getOption($key);
            if (null !== $cssName) {
                $key = $cssName;
            }

            if (!empty($value) && 'auto' !== $value) {
                $optionMap[$key] = $value;
            }
        }

        if (!isset($optionMap['width']) && isset($optionMap['max-width'])) {
            $optionMap['width'] = $optionMap['max-width'];
        }

        return $optionMap;
    }

    public function getColumnStyleOptions()
    {
        $options = [
            'cellAlign' => 'text-align',
            'width' => 'width',
            'minWidth' => 'min-width',
            'maxWidth' => 'max-width',
        ];

        $optionMap = [];
        foreach ($options as $key => $cssName) {
            $value = $this->getOption($key);
            if (null !== $cssName) {
                $key = $cssName;
            }

            if (!empty($value) || 'auto' !== $value) {
                $optionMap[$key] = $value;
            }
        }

        return $optionMap;
    }

    /**
     * @return array
     */
    public function buildColumnOptions(): array
    {
        $printOptions = [
            // Fancy grid doesnt like . in index name
            'index' => str_replace('.', '_', $this->getName()),
        ];

        // Legacy Mapping
        if ($this->getOption('visible')) {
            $printOptions['hidden'] = $this->getOption('visible');
        }

        if ($this->getOption('label')) {
            $printOptions['title'] = $this->getOption('label');
        }

        $options = $this->options;

        foreach ($options as $key => $value) {
            // Ignore this options they are used differently
            if (in_array($key, ['attr', 'template', 'header_align', 'label', 'translation_domain', 'translatable', 'visible', 'identifier', 'index', 'class', 'em', 'query_builder', 'choice_loader'])) {
                continue;
            }

            // if null we dont need to print the option
            if (null === $value) {
                continue;
            }

            $printOptions[$key] = $value;
        }

        return $printOptions;
    }

    /**
     * @param mixed            $object
     * @param string           $field
     * @param string           $primary
     * @param PropertyAccessor $accessor
     *
     * @return mixed
     */
    public function getValue($object, $field, $primary, $accessor)
    {
        /* Special case handling for e.g. count() */
        if (is_array($object) && isset($object[$field])) {
            return $object[$field];
        } elseif (is_array($object)) {
            $object = $object[0];
        }

        if (!$accessor->isReadable($object, $field)) {
            // if not found, try to strip alias.
            if (strstr($field, '.')) {
                $field = substr($field, strpos($field, '.') + 1);
            }
        }

        if (!$accessor->isReadable($object, $field)) {
            return null;
        }

        return $accessor->getValue($object, $field);
    }

    public function viewToData($value)
    {
        return $value;
    }

    public function dataToView($value)
    {
        return $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasOption($name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @param string      $name
     * @param string|null $default
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function translate($key, $domain = null)
    {
        if (null === $this->getTranslator()) {
            return $key;
        }

        return $this->getTranslator()->trans($key, $domain);
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter($filter): ColumnInterface
    {
        $this->filter = $filter;

        return $this;
    }
}
