<?php


namespace App\Controller\Api\Auth;


use App\Entity\Auth\Client;
use App\Repository\Auth\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class MeController extends AbstractController
{
    /**
     * @var ClientRepository
     */
    private ClientRepository $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(): ?UserInterface
    {
        $user = $this->getUser();
        if (!is_null($user) && $user instanceof Client) {
            /** @var Client $user */
            $user = $this->repository->find($user->getId());
        }
        return $user;
    }
}