<?php

namespace App\Controller\Api\Auth;




use App\Entity\Auth\Client;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientCreateController
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function __invoke(Client $data)
    {
        $password = $this->passwordHasher->hashPassword(
            $data,
            $data->getPassword()
        );
        $data->setPassword($password);

        return $data;
    }
}