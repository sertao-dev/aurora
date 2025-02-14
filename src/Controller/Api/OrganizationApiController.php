<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Helper\EntityIdNormalizerHelper;
use App\Service\Interface\OrganizationServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;

class OrganizationApiController extends AbstractApiController
{
    public function __construct(
        private readonly OrganizationServiceInterface $service,
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        $organization = $this->service->create($request->toArray());

        return $this->json($organization, status: Response::HTTP_CREATED, context: ['groups' => ['organization.get', 'organization.get.item']]);
    }

    public function get(?Uuid $id): JsonResponse
    {
        $organization = $this->service->get($id);

        return $this->json($organization, context: ['groups' => ['organization.get', 'organization.get.item']]);
    }

    public function list(): JsonResponse
    {
        return $this->json($this->service->list(), context: [
            'groups' => 'organization.get',
            AbstractNormalizer::CALLBACKS => [
                'parent' => [EntityIdNormalizerHelper::class, 'normalizeEntityId'],
            ],
        ]);
    }

    public function remove(?Uuid $id): JsonResponse
    {
        $this->service->remove($id);

        return $this->json(data: [], status: Response::HTTP_NO_CONTENT);
    }

    public function update(?Uuid $id, Request $request): JsonResponse
    {
        $organization = $this->service->update($id, $request->toArray());

        return $this->json($organization, Response::HTTP_OK, context: ['groups' => ['organization.get', 'organization.get.item']]);
    }

    public function updateImage(Uuid $id, Request $request): JsonResponse
    {
        $organization = $this->service->updateImage($id, $request->files->get('image'));

        return $this->json($organization, Response::HTTP_OK, context: ['groups' => ['organization.get', 'organization.get.item']]);
    }
}
