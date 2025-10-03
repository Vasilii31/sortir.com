<?php

namespace App\Tests\Unit\Service;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Service\SiteService;
use App\ServiceResult\Site\DeleteSiteResult;
use App\ServiceResult\Site\UpdateSiteResult;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SiteServiceTest extends TestCase
{
    private SiteService $siteService;
    private MockObject $siteRepository;

    protected function setUp(): void
    {
        $this->siteRepository = $this->createMock(SiteRepository::class);

        $this->siteService = new SiteService(
            $this->siteRepository
        );
    }

    public function testGetAllSites(): void
    {
        // Arrange
        $sites = [new Site(), new Site()];

        $this->siteRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($sites);

        // Act
        $result = $this->siteService->getAllSites();

        // Assert
        $this->assertSame($sites, $result);
        $this->assertCount(2, $result);
    }

    public function testCreateSite(): void
    {
        // Arrange
        $nomSite = 'Nouveau Site';

        $this->siteRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Site::class));

        // Act
        $result = $this->siteService->createSite($nomSite);

        // Assert
        $this->assertInstanceOf(Site::class, $result);
        $this->assertEquals($nomSite, $result->getNomSite());
    }

    public function testDeleteSiteSuccess(): void
    {
        // Arrange
        $site = $this->createMock(Site::class);
        $site->method('getParticipants')
            ->willReturn(new ArrayCollection());

        $this->siteRepository->expects($this->once())
            ->method('remove')
            ->with($site);

        // Act
        $result = $this->siteService->deleteSite($site);

        // Assert
        $this->assertEquals(DeleteSiteResult::SUCCESS, $result);
    }

    public function testDeleteSiteInUse(): void
    {
        // Arrange
        $site = $this->createMock(Site::class);
        $participant = $this->createMock(Participant::class);

        $participants = new ArrayCollection([$participant]);
        $site->method('getParticipants')
            ->willReturn($participants);

        $this->siteRepository->expects($this->never())
            ->method('remove');

        // Act
        $result = $this->siteService->deleteSite($site);

        // Assert
        $this->assertEquals(DeleteSiteResult::SITE_IN_USE, $result);
    }

    public function testUpdateSite(): void
    {
        // Arrange
        $site = $this->createMock(Site::class);
        $newNom = 'Nouveau Nom';

        $site->expects($this->once())
            ->method('setNomSite')
            ->with($newNom);

        $this->siteRepository->expects($this->once())
            ->method('save')
            ->with($site);

        // Act
        $result = $this->siteService->updateSite($site, $newNom);

        // Assert
        $this->assertEquals(UpdateSiteResult::SUCCESS, $result);
    }

    public function testSearchByName(): void
    {
        // Arrange
        $term = 'test';
        $expectedSites = [new Site(), new Site()];

        $query = $this->createMock(Query::class);
        $query->method('getResult')
            ->willReturn($expectedSites);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('where')
            ->with('s.nom_site LIKE :term')
            ->willReturnSelf();
        $queryBuilder->method('setParameter')
            ->with('term', '%test%')
            ->willReturnSelf();
        $queryBuilder->method('getQuery')
            ->willReturn($query);

        $this->siteRepository->method('createQueryBuilder')
            ->with('s')
            ->willReturn($queryBuilder);

        // Act
        $result = $this->siteService->searchByName($term);

        // Assert
        $this->assertSame($expectedSites, $result);
    }
}