<?php

namespace App\Tests\Controller;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SortieControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $sortieRepository;
    private string $path = '/sortie/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->sortieRepository = $this->manager->getRepository(Sortie::class);

        foreach ($this->sortieRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sortie index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'sortie[nom]' => 'Testing',
            'sortie[datedebut]' => 'Testing',
            'sortie[duree]' => 'Testing',
            'sortie[datecloture]' => 'Testing',
            'sortie[nbInscriptionsMax]' => 'Testing',
            'sortie[descriptionInfos]' => 'Testing',
            'sortie[etatsortie]' => 'Testing',
            'sortie[urlPhoto]' => 'Testing',
            'sortie[etat]' => 'Testing',
            'sortie[organisateur]' => 'Testing',
            'sortie[Lieu]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->sortieRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sortie();
        $fixture->setNom('My Title');
        $fixture->setDatedebut('My Title');
        $fixture->setDuree('My Title');
        $fixture->setDatecloture('My Title');
        $fixture->setNbInscriptionsMax('My Title');
        $fixture->setDescriptionInfos('My Title');
        $fixture->setEtatsortie('My Title');
        $fixture->setUrlPhoto('My Title');
        $fixture->setEtat('My Title');
        $fixture->setOrganisateur('My Title');
        $fixture->setLieu('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sortie');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sortie();
        $fixture->setNom('Value');
        $fixture->setDatedebut('Value');
        $fixture->setDuree('Value');
        $fixture->setDatecloture('Value');
        $fixture->setNbInscriptionsMax('Value');
        $fixture->setDescriptionInfos('Value');
        $fixture->setEtatsortie('Value');
        $fixture->setUrlPhoto('Value');
        $fixture->setEtat('Value');
        $fixture->setOrganisateur('Value');
        $fixture->setLieu('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'sortie[nom]' => 'Something New',
            'sortie[datedebut]' => 'Something New',
            'sortie[duree]' => 'Something New',
            'sortie[datecloture]' => 'Something New',
            'sortie[nbInscriptionsMax]' => 'Something New',
            'sortie[descriptionInfos]' => 'Something New',
            'sortie[etatsortie]' => 'Something New',
            'sortie[urlPhoto]' => 'Something New',
            'sortie[etat]' => 'Something New',
            'sortie[organisateur]' => 'Something New',
            'sortie[Lieu]' => 'Something New',
        ]);

        self::assertResponseRedirects('/sortie/');

        $fixture = $this->sortieRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getDatedebut());
        self::assertSame('Something New', $fixture[0]->getDuree());
        self::assertSame('Something New', $fixture[0]->getDatecloture());
        self::assertSame('Something New', $fixture[0]->getNbInscriptionsMax());
        self::assertSame('Something New', $fixture[0]->getDescriptionInfos());
        self::assertSame('Something New', $fixture[0]->getEtatsortie());
        self::assertSame('Something New', $fixture[0]->getUrlPhoto());
        self::assertSame('Something New', $fixture[0]->getEtat());
        self::assertSame('Something New', $fixture[0]->getOrganisateur());
        self::assertSame('Something New', $fixture[0]->getLieu());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sortie();
        $fixture->setNom('Value');
        $fixture->setDatedebut('Value');
        $fixture->setDuree('Value');
        $fixture->setDatecloture('Value');
        $fixture->setNbInscriptionsMax('Value');
        $fixture->setDescriptionInfos('Value');
        $fixture->setEtatsortie('Value');
        $fixture->setUrlPhoto('Value');
        $fixture->setEtat('Value');
        $fixture->setOrganisateur('Value');
        $fixture->setLieu('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/sortie/');
        self::assertSame(0, $this->sortieRepository->count([]));
    }
}
