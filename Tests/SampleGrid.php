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

namespace Cwd\GridBundle\Tests;

use Devopsial\Entity\Company;
use Devopsial\Entity\Sample;
use Cwd\GridBundle\Column\SwitchType;
use Cwd\GridBundle\Column\ActionType;
use Cwd\GridBundle\Column\CheckboxType;
use Cwd\GridBundle\Column\ChoiceType;
use Cwd\GridBundle\Column\DateType;
use Cwd\GridBundle\Column\EntityType;
use Cwd\GridBundle\Column\ImageType;
use Cwd\GridBundle\Column\NumberType;
use Cwd\GridBundle\Column\TextType;
use Cwd\GridBundle\Grid\AbstractGrid;
use Cwd\GridBundle\GridBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SampleGrid extends AbstractGrid
{
    /**
     * @param GridBuilderInterface $builder
     * @param array                $options
     */
    public function buildGrid(GridBuilderInterface $builder, array $options)
    {
        $builder
            ->add(new NumberType('id', 'sample.id', ['label' => 'ID', 'identifier' => true, 'searchable' => false, 'minWidth' => '50px']))
            //->add(new ImageType('profile_image', 'sample.profile_image', ['label' => 'Image']))
            ->add(new TextType('firstname', 'sample.firstname', ['label' => 'Firstname']))
            ->add(new TextType('lastname', 'sample.lastname', ['label' => 'Lastname']))
            ->add(new TextType('email', 'sample.email', ['label' => 'Email', 'sortable' => true, 'ellipsis' => true, 'maxWidth' => '50px']))
            ->add(new DateType('born_at', 'sample.bornAt', ['label' => 'Born', 'format' => ['read' => 'd.m.Y']]))
            ->add(new EntityType(
                'company_name',
                'company.name',
                [
                    'label' => 'Company',
                    'class' => Company::class,
                    'em' => $this->getAdapter()->getDoctrineRegistry()->getManager(),
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('c')
                            ->orderBy('c.name', 'ASC');
                    },
                    'choice_label' => function ($category) {
                        return $category->getName();
                    },
                ]
            ))
            ->add(new ChoiceType('status', 'sample.status', [
                'label' => 'State',
                'cellAlign' => 'center',
                'data' => [['value' => 'active'], ['value' => 'inactive']],
            ]))
            //->add(new CheckboxType('active', 'sample.active', ['label' => 'Active']))
            ->add(new SwitchType('active', 'sample.active', [
                'label' => 'Active',
                'route' => 'test',
            ]))
            ->add(new ActionType(
                'actions',
                'sample.id',
                [
                    'label' => '',
                    'width' => '320px',
                    'actions' => [
                        [
                            'route' => 'test',
                            'class' => 'btn-warning',
                            'icon' => 'fa-copy',
                            'title' => 'generic.duplicate',
                        ],
                        [
                            'route' => 'test',
                            'class' => 'btn-primary',
                            'icon' => 'fa-edit',
                            'title' => 'generic.edit',
                        ],
                        [
                            'route' => 'test',
                            'class' => 'btn-danger deleterow',
                            'icon' => 'fa-trash-o',
                            'title' => 'generic.delete',
                        ],
                    ],
                ]
            ));
    }

    /**
     * @param ObjectManager $objectManager
     * @param array         $params
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(ObjectManager $objectManager, array $params = []): QueryBuilder
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $objectManager
            ->getRepository(Sample::class)
            ->createQueryBuilder('sample')
            ->select('sample', 'company')
            ->leftJoin('sample.company', 'company')
        ;

        return $queryBuilder;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_route' => 'app_admin_infrastructure_web_machine_ajaxdata',
            'sortField' => 'lastname',
        ]);
    }
}
