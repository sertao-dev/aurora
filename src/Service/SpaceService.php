<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\SpaceDto;
use App\Entity\Space;
use App\Exception\Space\SpaceResourceNotFoundException;
use App\Exception\ValidatorException;
use App\Repository\Interface\SpaceRepositoryInterface;
use App\Service\Interface\FileServiceInterface;
use App\Service\Interface\SpaceServiceInterface;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class SpaceService extends AbstractEntityService implements SpaceServiceInterface
{
    public function __construct(
        private SpaceRepositoryInterface $repository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private FileServiceInterface $fileService,
        private ParameterBagInterface $parameterBag,
        private Security $security,
    ) {
        parent::__construct($security);
    }

    public function create(array $space): Space
    {
        $space = self::validateInput($space, SpaceDto::CREATE);

        $spaceObj = $this->serializer->denormalize($space, Space::class);

        return $this->repository->save($spaceObj);
    }

    public function get(Uuid $id): Space
    {
        $space = $this->repository->findOneBy([
            ...['id' => $id],
            ...$this->getDefaultParams(),
        ]);

        if (null === $space) {
            throw new SpaceResourceNotFoundException();
        }

        return $space;
    }

    public function findOneBy(array $params): Space
    {
        return $this->repository->findOneBy(
            [...$params, ...$this->getDefaultParams()]
        );
    }

    public function list(int $limit = 50): array
    {
        return $this->repository->findBy(
            $this->getDefaultParams(),
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function count(): int
    {
        return $this->repository->count(
            $this->getDefaultParams()
        );
    }

    public function findBy(array $params = [], int $limit = 50): array
    {
        return $this->repository->findBy(
            [...$params, ...$this->getUserParams()],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function remove(Uuid $id): void
    {
        $space = $this->repository->findOneBy(
            [...['id' => $id], ...$this->getUserParams()]
        );

        if (null === $space) {
            throw new SpaceResourceNotFoundException();
        }

        $space->setDeletedAt(new DateTime());

        $this->repository->save($space);
    }

    public function update(Uuid $identifier, array $space): Space
    {
        $spaceFromDB = $this->get($identifier);

        $spaceDto = self::validateInput($space, SpaceDto::UPDATE);

        $spaceObj = $this->serializer->denormalize($spaceDto, Space::class, context: [
            'object_to_populate' => $spaceFromDB,
        ]);

        $spaceObj->setUpdatedAt(new DateTime());

        return $this->repository->save($spaceObj);
    }

    /**
     * @todo: Analizar capacidade de abstrair código duplicado
     */
    private function validateInput(array $space, string $group): array
    {
        $spaceDto = self::denormalizeDto($space);

        $violations = $this->validator->validate($spaceDto, groups: $group);

        if ($violations->count() > 0) {
            if ($spaceDto->image instanceof File) {
                $this->fileService->deleteFile($spaceDto->image->getRealPath());
            }

            throw new ValidatorException(violations: $violations);
        }

        if ($spaceDto->image instanceof File) {
            $space = array_merge($space, ['image' => $spaceDto->image]);
        }

        return $space;
    }

    private function denormalizeDto(array $data): SpaceDto
    {
        return $this->serializer->denormalize($data, SpaceDto::class, context: [
            AbstractNormalizer::CALLBACKS => [
                'image' => function () use ($data): ?File {
                    if (false === isset($data['image'])) {
                        return null;
                    }

                    return $this->fileService->uploadImage($this->parameterBag->get('app.dir.space.profile'), $data['image']);
                },
            ],
        ]);
    }
}
