<?php
// api/src/Controller/ClientObjectAction.php

namespace App\Controller;

use App\Entity\Auth\Client;
use App\Entity\Auth\ClientObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


#[AsController]
final class ClientObjectAction extends AbstractController
{

    public function __invoke(Request
    $request, EntityManagerInterface $em): ClientObject
    {

        $uploadedFile = $request->files->get('file');
        // id du clinet concerne, type (type == 1) ? =>patch profile : patch couverture
        $id = $request->get('id');
        $type = $request->get('type');

        if (
            !$uploadedFile
            || !$id
            || !$type
        ) {
            throw new BadRequestHttpException('"file,id,type" is required');
        }

        $clientObject = new clientObject();
        $clientObject->file = $uploadedFile;
        $client =    $em->getRepository(Client::class)->findOneBy(['id' => $id]);
        $em->persist($clientObject);
        $em->flush();
        if ($type == 1) {


            $client->setProfile($clientObject);
        } else {
            $client->setCouverture($clientObject);
        }
        $em->persist($client);
        $em->flush();
        // dd($clientObject);
        return $clientObject;
    }
}
