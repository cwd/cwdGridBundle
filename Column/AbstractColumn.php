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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class AbstractColumn.
 *
 * @author Ludwig Ruderstaler <lr@cwd.at>
 */
abstract class AbstractColumn implements ColumnInterface
{
    protected string $name;
    protected string $field;
    protected array $options = [];
    protected TranslatorInterface $translator;
    protected bool $isSorted = false;
    protected ?string $sortDir = null;
    protected ?array $filter = null;

    public function __construct(string $name, string $field, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
        $this->name = $name;
        $this->field = $field;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

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

    public function render(mixed $value, mixed $object, string|int $primary, Environment $twig): string
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

        /* Special case for count(*) */
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

    public function renderFilter(Environment $twig): string
    {
        $value = (null !== $this->getFilter() && '' != isset($this->getFilter()->value)) ? $this->getFilter()->value : '';

        return $twig->render('@CwdGrid/filter/text.html.twig', [
            'column' => $this,
            'value' => $value,
        ]);
    }

    protected function renderTemplate(Environment $twig, string $template, array $options = []): string
    {
        $options = array_merge($options, $this->getOptions());

        return $twig->render($template, $options);
    }

    /**
     * set defaults options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
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
            'parentField' => null,
        ]);

        $resolver->setAllowedTypes('attr', 'array');
    }

    public function getHeaderStyleOptions(): array
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

    public function getColumnStyleOptions(): array
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

    public function getValue(mixed $object, string $field, string $primary, PropertyAccessorInterface $accessor, mixed $parentField = null): mixed
    {
        /* Special case handling for e.g. count() */
        if (is_array($object) && isset($object[$field])) {
            return $object[$field];
        } elseif (is_array($object)) {
            $object = $object[0];
        }

        if (null !== $parentField) {
            $field = $parentField.'.'.$field;
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

    public function viewToData(mixed $value): mixed
    {
        return $value;
    }

    public function dataToView(mixed $value): mixed
    {
        return $value;
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function translate(string $key, ?string $domain = null): string
    {
        if (null === $this->getTranslator()) {
            return $key;
        }

        return $this->getTranslator()->trans($key, [], $domain);
    }

    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function getFilter(): ?array
    {
        return $this->filter;
    }

    public function setFilter(array $filter): ColumnInterface
    {
        $this->filter = $filter;

        return $this;
    }
}
