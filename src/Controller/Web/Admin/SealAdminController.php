<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\Exception\ValidatorException;
use App\Service\Interface\SealServiceInterface;
use DateTime;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SealAdminController extends AbstractAdminController
{
    public const VIEW_ADD = 'seal/add.html.twig';

    public function __construct(
        private SealServiceInterface $sealService,
        private readonly TranslatorInterface $translator,
        private Security $security,
    ) {
    }

    public function list(): Response
    {
        $seals = $this->sealService->list();

        return $this->render('seal/list.html.twig', [
            'seals' => $seals,
        ]);
    }

    public function getOne(int $id): Response
    {
        $seal = [
            'name' => 'Selo '.$id,
            'status' => 'Ativo',
            'createdAt' => new DateTime('2023-12-01 10:00:00'),
        ];

        return $this->render('seal/one.html.twig', [
            'seal' => $seal,
        ]);
    }

    public function add(Request $request, ValidatorInterface $validator): Response
    {
        if ('POST' !== $request->getMethod()) {
            return $this->render(self::VIEW_ADD);
        }

        try {
            $this->sealService->create([
                'id' => Uuid::v4(),
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'active' => true,
                'createdBy' => $this->security->getUser()->getAgents()->getValues()[0]->getId(),
            ]);
        } catch (ValidatorException $exception) {
            return $this->render(self::VIEW_ADD, [
                'errors' => $exception->getConstraintViolationList(),
            ]);
        } catch (Exception $exception) {
            return $this->render(self::VIEW_ADD, [
                'errors' => [
                    $exception->getMessage(),
                ],
            ]);
        }

        $this->addFlash('success', $this->translator->trans('view.seal.message.created'));

        return $this->redirectToRoute('admin_seal_list');
    }

    public function remove(?Uuid $id): Response
    {
        $this->sealService->remove($id);

        $this->addFlash('success', $this->translator->trans('view.seal.message.deleted'));

        return $this->redirectToRoute('admin_seal_list');
    }
}
