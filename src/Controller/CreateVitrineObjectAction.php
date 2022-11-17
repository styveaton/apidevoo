<?php
// api/src/Controller/CreateVitrineObjectAction.php

namespace App\Controller;

use App\Entity\Vitrine\Contenu;
use App\Entity\Vitrine\Vitrine;
use App\Entity\Vitrine\VitrineObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

#[AsController]
final class CreateVitrineObjectAction extends AbstractController
{
    public function __invoke(Request
    $request, ManagerRegistry $doctrine): VitrineObject
    {
        $VitrineEntityManager = $doctrine->getManager('Vitrine');
        $uploadedFile = $request->files->get('file');

        $idContenu = $request->get('contenu');
        // $idVitrine = $request->get('vitirne');
        $idVO = $request->get('idVitrine');

        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }
        $Vitrine  =

            $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['id' => $idVO]);
        $VitrineObject =

            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['Vitrine' => $Vitrine]);

        if ($Vitrine != null) {
            if ($VitrineObject != null) {

                $VitrineObject->file = $uploadedFile;
                $VitrineObject->setFile($uploadedFile);
                $VitrineObject->setFilePath($uploadedFile->getClientOriginalName());
                $VitrineEntityManager->persist($VitrineObject);
                $VitrineEntityManager->flush();

                // var_dump($uploadedFile->getClientOriginalName());
                // var_dump($VitrineObject->getFilePathId());
            } else {

                $VitrineObject = new VitrineObject();
                $VitrineObject->file = $uploadedFile;
                // $VitrineObject->setFilePath($idContenu);
                if ($Vitrine) {
                    $VitrineObject->setVitrine($Vitrine);
                }
                $contenu =    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['id' => $idContenu]);
                if ($contenu) {
                    $VitrineObject->setContenu($contenu);
                }
                $VitrineEntityManager->persist($VitrineObject);
                $VitrineEntityManager->flush();
            }
        } else {
            $contenu =    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['id' => $idContenu]);
            if ($contenu != null) {
                $vitrineO =      $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $contenu]);
                if ($vitrineO != null) {

                    $vitrineO->file = $uploadedFile;
                    $vitrineO->setFile($uploadedFile);
                    $vitrineO->setContenu($contenu);
                    $vitrineO->setFilePath($uploadedFile->getClientOriginalName());
                    $VitrineEntityManager->persist($vitrineO);
                    $VitrineEntityManager->flush();
                } else {

                    $VitrineObjectA = new VitrineObject();
                    $VitrineObjectA->file = $uploadedFile;

                    $VitrineObjectA->setContenu($contenu);
                    $VitrineEntityManager->persist($VitrineObjectA);
                    $VitrineEntityManager->flush();
                }
            } else {




                $VitrineObject = new VitrineObject();
                $VitrineObject->file = $uploadedFile;
                // $VitrineObject->setFilePath($idContenu);
                if ($Vitrine) {
                    $VitrineObject->setVitrine($Vitrine);
                }
                $contenu =    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['id' => $idContenu]);
                if ($contenu) {
                    $VitrineObject->setContenu($contenu);
                }
                $VitrineEntityManager->persist($VitrineObject);
                $VitrineEntityManager->flush();
            }
        }


        // dd($VitrineObject);
        return $VitrineObject;
    }
}
