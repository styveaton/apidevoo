<?php
// api/src/Controller/CreateMediaObjectAction.php

namespace App\Controller;

use App\Entity\Vitrine\VitrineObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Persistence\ManagerRegistry;

#[AsController]
final class PatchVitrineObjectAction extends AbstractController
{
    public function __invoke(Request $request, ManagerRegistry $doctrine): VitrineObject
    {
        $VitrineEntityManager = $doctrine->getManager('Vitrine');

        $uploadedFile = $request->get('file');
    //    dd(explode('/', $request->getPathInfo()));
// dd($request);
        $id = explode('/', $request->getPathInfo())[3];
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }
        $v =
            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['id' => $id]);
        $v->file = $uploadedFile;
        $v->setFileA($uploadedFile);
        $VitrineEntityManager->persist($uploadedFile);
        $VitrineEntityManager->flush();
        // dd($VitrineObject);
        return $v;
    }
}
