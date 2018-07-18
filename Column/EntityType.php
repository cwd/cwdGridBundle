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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityType extends ChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $choiceLoader = function (Options $options) {
            // Unless the choices are given explicitly, load them on demand
            if (null === $options['data']) {
                if (null !== $options['query_builder']) {
                    $entityLoader = $this->getLoader($options['em'], $options['query_builder'], $options['class']);
                } else {
                    $queryBuilder = $options['em']->getRepository($options['class'])->createQueryBuilder('e');
                    $entityLoader = $this->getLoader($options['em'], $queryBuilder, $options['class']);
                }

                $doctrineChoiceLoader = new DoctrineChoiceLoader(
                    $options['em'],
                    $options['class'],
                    $options['id_reader'],
                    $entityLoader
                );

                return $doctrineChoiceLoader;
            }
        };

        $emNormalizer = function (Options $options, $em) {
            /* @var ManagerRegistry $registry */
            if (null !== $em && $em instanceof ObjectManager) {
                return $em;
            }

            if (null === $this->getOption('em')) {
                throw new \RuntimeException(sprintf(
                    'Object Manager not set'.
                    'Did you forget to set it? "em"'
                ));
            }

            $em = $this->getOption('em')->getManagerForClass($options['class']);

            if (null === $em) {
                throw new \RuntimeException(sprintf(
                    'Class "%s" seems not to be a managed Doctrine entity. '.
                    'Did you forget to map it?',
                    $options['class']
                ));
            }

            return $em;
        };

        // Invoke the query builder closure so that we can cache choice lists
        // for equal query builders
        $queryBuilderNormalizer = function (Options $options, $queryBuilder) {
            if (is_callable($queryBuilder)) {
                $queryBuilder = call_user_func($queryBuilder, $options['em']->getRepository($options['class']));
            }

            return $queryBuilder;
        };

        // Set the "id_reader" option via the normalizer. This option is not
        // supposed to be set by the user.
        $idReaderNormalizer = function (Options $options) {
            $classMetadata = $options['em']->getClassMetadata($options['class']);

            return new IdReader($options['em'], $classMetadata);
        };

        $resolver->setDefaults(array(
            'data' => null,
            'class' => null,
            'query_builder' => null,
            'choice_loader' => $choiceLoader,
            'choice_label' => null,
            'id_reader' => null,
            'em' => null,
        ));
        $resolver->setRequired(['class']);

        $resolver->setNormalizer('em', $emNormalizer);
        $resolver->setNormalizer('query_builder', $queryBuilderNormalizer);
        $resolver->setNormalizer('id_reader', $idReaderNormalizer);

        $resolver->setAllowedTypes('attr', 'array');
        $resolver->setAllowedTypes('data', ['array', 'null']);
    }

    public function buildColumnOptions(): array
    {
        $printOptions = parent::buildColumnOptions();

        if (is_array($this->getOption('data')) && count($this->getOption('data')) > 0) {
            return $printOptions;
        }

        $list = $this->getOption('choice_loader');
        $viewFactory = new DefaultChoiceListFactory();
        $views = $viewFactory->createView($list->loadChoiceList(), null, $this->getOption('choice_label'));

        $printOptions = [];

        foreach ($views->choices as $view) {
            $printOptions[] = [
                'id' => $view->value,
                'label' => $view->label,
                'value' => $view->data->getName(),
            ];
        }

        return $printOptions;
    }

    /**
     * @param \Twig_Environment $twig
     *
     * @return string
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderFilter(\Twig_Environment $twig)
    {
        $value = (null !== $this->getFilter() && '' != isset($this->getFilter()->value)) ? $this->getFilter()->value : '';

        return $twig->render('@CwdGrid/filter/choice.html.twig', [
            'data' => $this->buildColumnOptions(),
            'value' => $value,
            'column' => $this,
        ]);
    }

    /**
     * We consider two query builders with an equal SQL string and
     * equal parameters to be equal.
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return array
     *
     * @internal This method is public to be usable as callback. It should not
     *           be used in user code.
     */
    public function getQueryBuilderPartsForCachingHash($queryBuilder)
    {
        return array(
            $queryBuilder->getQuery()->getSQL(),
            array_map(array($this, 'parameterToArray'), $queryBuilder->getParameters()->toArray()),
        );
    }

    /**
     * Converts a query parameter to an array.
     *
     * @return array The array representation of the parameter
     */
    private function parameterToArray(Parameter $parameter)
    {
        return array($parameter->getName(), $parameter->getType(), $parameter->getValue());
    }

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param QueryBuilder  $queryBuilder
     * @param string        $class
     *
     * @return ORMQueryBuilderLoader
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new ORMQueryBuilderLoader($queryBuilder);
    }
}
