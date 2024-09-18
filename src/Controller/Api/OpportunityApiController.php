<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Helper\EntityIdNormalizerHelper;
use App\Service\Interface\OpportunityServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;

class OpportunityApiController extends AbstractApiController
{
    public function __construct(
        private readonly OpportunityServiceInterface $service,
    ) {
    }

    public function get(Uuid $id): JsonResponse
    {
        $opportunity = $this->service->get($id);

        return $this->json($opportunity, context: ['groups' => 'opportunity.get']);
    }

    public function remove(?Uuid $id): JsonResponse
    {
        $this->service->remove($id);

        return $this->json(data: [], status: Response::HTTP_NO_CONTENT);
    }

    public function list(): JsonResponse
    {
        return $this->json($this->service->list(), context: [
            'groups' => 'opportunity.get',
            AbstractNormalizer::CALLBACKS => [
                'parent' => [EntityIdNormalizerHelper::class, 'normalizeEntityId'],
            ],
        ]);
    }
}