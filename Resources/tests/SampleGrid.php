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

namespace Cwd\GridBundle\Resources\tests;

use App\Entity\Sample;
use Cwd\GridBundle\Column\TextType;
use Cwd\GridBundle\Grid\AbstractGrid;
use Cwd\GridBundle\GridBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
            ->add(new TextType('id', 'sample.id', ['label' => 'id', 'identifier' => true]))
            ->add(new TextType('firstname', 'sample.firstname', ['label' => 'Firstname']))
            ->add(new TextType('lastname', 'sample.lastname', ['label' => 'Lastname']))
            ->add(new TextType('email', 'sample.email', ['label' => 'Email']));
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
            ->createQueryBuilder('sample');

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
