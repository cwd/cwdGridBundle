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

namespace Cwd\GridBundle\Tests\Twig;

use Cwd\GridBundle\GridFactory;
use Cwd\GridBundle\Tests\SampleGrid;
use Cwd\GridBundle\Twig\GridExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class GridExtensionTest extends WebTestCase
{
    /** @var \Twig_Environment */
    private $twig;

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();

        $container = self::$kernel->getContainer();

        $loader = new \Twig_Loader_Filesystem(__DIR__.'/templates');
        $loader->addPath(__DIR__.'/../../Resources/views', 'CwdGrid');
        $this->twig = new \Twig_Environment($loader);
        //$this->twig->setLoader(new \Twig_Loader_Filesystem(__DIR__.'/templates'));

        $this->twig->addExtension(new GridExtension());
        $this->twig->addExtension(new TranslationExtension($container->get('translator')));
        $this->twig->addExtension($container->get('test.twig.extension.pagerfanta'));
    }

    public function testExtension()
    {
        $factory = self::$kernel->getContainer()->get(GridFactory::class);
        $grid = $factory->create(SampleGrid::class, ['pagerfantaOptions' => ['routeName' => 'test'], 'sortDir' => 'ASC', 'sortField' => 'born_at']);

        $result = $this->twig->render('grid.html.twig', ['grid' => $grid]);

        $crawler = new Crawler($result);

        $this->assertEquals(1, $crawler->filter('table.grid')->count());
        $this->assertEquals(18, $crawler->filter('table.grid')->filter('thead tr th')->count());
        $this->assertEquals(4, $crawler->filter('table.grid')->filter('select')->count());

        // Rows
        $this->assertEquals($grid->getOption('limit'), $crawler->filter('table.grid')->filter('tbody tr')->count());

        // ListLength
        $this->assertEquals(1, $crawler->filter('table.grid')->filter('.listLengthSelector')->count());
        $this->assertEquals(count($grid->getOption('listLength')), $crawler->filter('table.grid')->filter('.listLengthSelector option')->count());

        // Paging
        $this->assertGreaterThanOrEqual(10, $crawler->filter('table.grid')->filter('.pagination li')->count());

        // Sortable
        $this->assertEquals(7, $crawler->filter('table.grid')->filter('th.sortable')->count());
        $this->assertEquals(1, $crawler->filter('table.grid')->filter('th.sorted')->count());
        $this->assertEquals(1, $crawler->filter('table.grid')->filter('th.ASC')->count());

        // Change Grid and test again:
        $grid = $factory->create(SampleGrid::class, ['pagerfantaOptions' => ['routeName' => 'test'], 'sortDir' => 'DESC', 'sortField' => 'born_at', 'limit' => 50]);
        $result = $this->twig->render('grid.html.twig', ['grid' => $grid]);
        $crawler = new Crawler($result);

        $this->assertEquals(1, $crawler->filter('table.grid')->filter('.listLengthSelector')->count());
        $this->assertEquals(count($grid->getOption('listLength')), $crawler->filter('table.grid')->filter('.listLengthSelector option')->count());
        $this->assertEquals(1, $crawler->filter('table.grid')->filter('th.sorted')->count());
        $this->assertEquals(0, $crawler->filter('table.grid')->filter('th.ASC')->count());
        $this->assertEquals(1, $crawler->filter('table.grid')->filter('th.DESC')->count());
    }
}
