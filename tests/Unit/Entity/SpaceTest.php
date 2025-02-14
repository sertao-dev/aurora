<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ActivityArea;
use App\Entity\Agent;
use App\Entity\Space;
use App\Entity\SpaceAddress;
use App\Helper\DateFormatHelper;
use App\Tests\AbstractWebTestCase;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class SpaceTest extends AbstractWebTestCase
{
    public function testGettersAndSettersFromSpaceEntityShouldBeSuccessful(): void
    {
        $space = new Space();

        $spaceParent = new Space();
        $spaceParent->setId(Uuid::v4()::fromString('8e3e976d-0fc0-443e-bdd2-2b4d83da004f'));

        $spaceAddress = $this->createMock(SpaceAddress::class);

        $agent = new Agent();
        $agent->setId(Uuid::v4()::fromString('95f91eb5-cb62-4a7b-b677-8486d2a0763a'));

        $this->assertNull($space->getId());
        $this->assertNull($space->getName());
        $this->assertNull($space->getImage());
        $this->assertNull($space->getAddress());
        $this->assertNull($space->getParent());
        $this->assertNull($space->getUpdatedAt());
        $this->assertNull($space->getDeletedAt());

        $id = new Uuid('8d74efa8-fd92-4d4d-9e5f-7fafa674cf55');
        $extraField = [
            'type' => 'Instituição Cultural',
            'description' => 'A Secretaria da Cultura (SECULT) é responsável por fomentar a arte e a cultura no estado, organizando eventos e oferecendo apoio a iniciativas locais.',
            'location' => 'Complexo Estação das Artes - R. Dr. João Moreira, 540 - Centro, Fortaleza - CE, 60030-000',
            'accessibility' => ['Banheiros adaptados', 'Rampa de acesso', 'Elevador adaptado', 'Sinalização tátil'],
        ];
        $createdAt = new DateTimeImmutable();
        $updatedAt = new DateTime();
        $deletedAt = new DateTime();

        $activityArea1 = new ActivityArea();
        $activityArea1->setId(Uuid::v4());
        $activityArea1->setName('Teatro');

        $activityArea2 = new ActivityArea();
        $activityArea2->setId(Uuid::v4());
        $activityArea2->setName('Música');

        $space->setId($id);
        $space->setName('Casa do Cantador');
        $space->setImage('https://url-image.com.br');
        $space->setCreatedBy($agent);
        $space->setParent($spaceParent);
        $space->setAddress($spaceAddress);
        $space->setExtraFields($extraField);
        $space->setCreatedAt($createdAt);
        $space->setUpdatedAt($updatedAt);
        $space->setDeletedAt($deletedAt);
        $space->addActivityArea($activityArea1);
        $space->addActivityArea($activityArea2);

        $this->assertCount(2, $space->getActivityAreas());
        $this->assertContains($activityArea1, $space->getActivityAreas());
        $this->assertContains($activityArea2, $space->getActivityAreas());

        $space->removeActivityArea($activityArea1);
        $this->assertCount(1, $space->getActivityAreas());
        $this->assertNotContains($activityArea1, $space->getActivityAreas());

        $this->assertEquals([
            'id' => $id->toString(),
            'name' => 'Casa do Cantador',
            'createdBy' => '95f91eb5-cb62-4a7b-b677-8486d2a0763a',
            'parent' => '8e3e976d-0fc0-443e-bdd2-2b4d83da004f',
            'address' => $spaceAddress->toArray(),
            'extraFields' => $extraField,
            'activityAreas' => array_map(fn (ActivityArea $area) => $area->toArray(), $space->getActivityAreas()->toArray()),
            'createdAt' => $createdAt->format(DateFormatHelper::DEFAULT_FORMAT),
            'updatedAt' => $updatedAt->format(DateFormatHelper::DEFAULT_FORMAT),
            'deletedAt' => $deletedAt->format(DateFormatHelper::DEFAULT_FORMAT),
        ], $space->toArray());
    }
}
