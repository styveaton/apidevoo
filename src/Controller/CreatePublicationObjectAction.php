<?php
// api/src/Controller/CreateMediaObjectAction.php

namespace App\Controller;

use App\Entity\Pub\PublicationObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final class CreatePublicationObjectAction extends AbstractController
{
    public function __invoke(Request $request): PublicationObject
    {

        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $PublicationObject = new PublicationObject();
        $PublicationObject->file = $uploadedFile;
        // $PublicationObject->setPublication();
        // dd($PublicationObject);
        return $PublicationObject;
    }
}
