<?php

namespace App\Controller\Bulk;

use App\Entity\Account\TransactionCompte;
use App\Entity\Auth\Client;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FFI\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Entity\Bulk\Calendrier;
use App\Entity\Bulk\Contact;
use App\Entity\Bulk\Exception as BulkException;
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListLotCalendrier;
use App\Entity\Bulk\ListSenderIdExcepte;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsExcepte;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Bulk\SenderId;
use App\Entity\Bulk\Sms;
use App\Entity\Route\Operateur;
use App\Entity\Route\Pays;
use App\Entity\Route\Route as RouteRoute;
use App\Entity\Route\SenderApi;
use Proxies\__CG__\App\Entity\Bulk\Exception as EntityBulkException;
use Proxies\__CG__\App\Entity\Bulk\Sms as BulkSms;
use Proxies\__CG__\App\Entity\Pub\Publication;

class ExceptionController extends AbstractController
{
    private $em;
    private $client;
    private $apiKey;
    public function __construct(EntityManagerInterface $em, HttpClientInterface $client,  ManagerRegistry $doctrine)
    {
        $this->em = $em;
        $this->doctrine = $doctrine;
        $this->client = $client;
    }



    /**
     * @Route("/exception/senderIdContact/create", name="ExceptionSenderIdContactCreate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     * 
     * @param array $data doit contenir le senderId et le numero du client en string
     * 
     * 
     */
    public function ExceptionSenderIdContactCreate(Request $request)
    {
        $BulkEntityManager = $this->doctrine->getManager('Bulk');

        $data = $request->toArray();
        $possible = false;
        if (empty($data['senderId']) || empty($data['contact']) || empty($data['codePhone'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser senderId et  contact  '
            ], 400);
        }

        $exception = new BulkException();

        $exception->setContact($data['contact']);
        $exception->setCodePhone($data['codePhone']);
        $BulkEntityManager->persist($exception);

        $lsE = new ListSenderIdExcepte();
        $lsE->setSenderId($data['senderId']);
        $lsE->setException($exception);
        $BulkEntityManager->persist($lsE);
        $BulkEntityManager->flush();

        return
            new JsonResponse([
                'message'
                =>
                'OK',

            ], 200);
    }




    /**
     * @Route("/exception/senderIdContact/verif", name="ExceptionSenderIdContactVerif", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     * 
     * @param array $data doit contenir le senderId et le codePhone de la route a utiliser , la list des contact
     * 
     * 
     */
    public function ExceptionSenderIdContactVerif(Request $request)
    {
        $BulkEntityManager = $this->doctrine->getManager('Bulk');

        $data = $request->toArray();
        $possible = false;
        if (empty($data['senderId']) || empty($data['codePhone']) || empty($data['destinataire'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser senderId,codePhone,destinataire'
            ], 400);
        }


        $lse =    $BulkEntityManager->getRepository(ListSenderIdExcepte::class)->findBy(['senderId' => $data['senderId']]);
        $lContExcep = [];
        $destCorrect = [];
        $destExcept = [];
        $LCFinal = [];
        if ($lse) {

            foreach ($lse as $l) {

                array_push($lContExcep, $l->getException()->getCodePhone() . $l->getException()->getContact());
            }
            foreach ($data['destinataire'] as $dest) {
                if (in_array($data['codePhone'] .
                    $dest, $lContExcep)) {
                    array_push($destExcept, $dest);
                } else {
                    array_push($destCorrect, $dest);
                }
            }


            return
                new JsonResponse([
                    'status'
                    =>
                    true,
                    'destinataire'
                    =>
                    $destCorrect,
                    "excepte" => $destExcept
                ], 200);
        } else {
            return
                new JsonResponse([
                    'status'
                    =>
                    true,
                    'destinataire'
                    =>
                    $data['destinataire'],
                    "excepte" => []
                ], 200);
        }


        // $exception->setContact($data['contact']);
        // $exception->setContact($data['codePhone']);
        // $BulkEntityManager->persist($exception);

        // $lsE = new ListSenderIdExcepte();
        // $lsE->setSenderId($data['senderId']);
        // $lsE->setException($exception);
        // $BulkEntityManager->persist($lsE);
        // $BulkEntityManager->flush();
    }

    /**
     * @Route("/exception/all/read", name="ExceptionAll", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     * 
     * @param array $data doit contenir le senderId et le codePhone de la route a utiliser , la list des contact
     * 
     * 
     */
    public function ExceptionAll(Request $request)
    {
        $BulkEntityManager = $this->doctrine->getManager('Bulk');

        $data = $request->toArray();
        $possible = false;
       
        $lExF = [];
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser) {
            $serializer = $this->get('serializer');

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '30') {
                    $possible = true;
                }
            }

            $json = $serializer->serialize($clientUser, 'json');
            if ($possible) {
                $lException = $BulkEntityManager->getRepository(EntityBulkException::class)->findAll();

                foreach ($lException as $l) {
                    if ($l->getText()) {
                        array_push($lExF, ['id' => $l->getId(), 'status' => $l->isStatus() ? 'Active' : 'Desactive', 'title' => $l->getText()]);
                    }
                }



                return
                    new JsonResponse([
                        'status'
                        =>
                        true,
                        'esms'
                        =>
                    array_reverse(  $lExF),

                    ], 200);
            } else {
                return
                    new JsonResponse([
                        'status'
                        =>
                        true,

                        "esms" => []
                    ], 200);
            }
        }
    }




    /**
     * @Route("/exception/add", name="exceptionAdd", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function exceptionAdd(Request $request)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');

        $data = $request->toArray();
        if (empty($data['keySecret'])  || empty($data['typeE']) || empty($data['data'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete le keySecret,typeE ,idException et data est requis'
            ], 400);
        }

        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

        if (!$clientUser) {
            return new JsonResponse([
                'message' => 'Desolez l\'utilisateur en question n\'existe pas dans la base de donnée'
            ], 400);
        }
        $possible = false;
        foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            if ($lr->isStatus() && $lr->getFonction()->getId() == '30') {
                $possible = true;
            }
        }
        if ($possible) {
            $exception =   new EntityBulkException();

            if ($data['typeE'] == 'eSms') {
                $exception->setText($data['data']);
            }


            $bulkManager->persist($exception);
            $bulkManager->flush();
            $lExF = [];
            $lException = $bulkManager->getRepository(EntityBulkException::class)->findAll();

            foreach ($lException as $l) {
                if ($l->getText()) {
                    array_push($lExF, ['id' => $l->getId(), 'status' => $l->isStatus() ? 'Active' : 'Desactive', 'title' => $l->getText()]);
                }
            }




            return new JsonResponse([
                'status'
                =>
                true,
                'message' => 'Ajout d\'exeception sms Reussi', 'esms'
                =>
                $lExF,

            ], 200);
        } else {

            return new JsonResponse([
                'message' => 'error'
            ], 400);
        }
    }

    /**
     * @Route("/exception/modify", name="exceptionModify", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function exceptionModify(Request $request)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');

        $data = $request->toArray();
        if (empty($data['keySecret']) || empty($data['idException']) || empty($data['typeE']) || empty($data['data'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete le keySecret,typeE ,idException et data est requis'
            ], 400);
        }

        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

        if (!$clientUser) {
            return new JsonResponse([
                'message' => 'Desolez l\'utilisateur en question n\'existe pas dans la base de donnée'
            ], 400);
        }
        $possible = false;
        foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            if ($lr->isStatus() && $lr->getFonction()->getId() == '30') {
                $possible = true;
            }
        }
        if ($possible) {
            $exception = $bulkManager->getRepository(EntityBulkException::class)->findOneBy(['id' => $data['idException']]);

            if ($data['typeE'] == 'eSms') {
                $exception->setText($data['data']);
            }


            $bulkManager->persist($exception);
            $bulkManager->flush();
            $lExF = [];
            $lException = $bulkManager->getRepository(EntityBulkException::class)->findAll();

            foreach ($lException as $l) {
                if ($l->getText()) {
                    array_push($lExF, ['id' => $l->getId(), 'status' => $l->isStatus() ? 'Active' : 'Desactive', 'title' => $l->getText()]);
                }
            }




            return new JsonResponse([
                'status'
                =>
                true,
                'message'
                => 'Mise A jour Reussi', 'esms'
                =>
                $lExF,

            ], 200);
        } else {

            return new JsonResponse([
                'message' => 'error'
            ], 400);
        }
    }

    /**
     * @Route("/exception/desable", name="exceptionDesable", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function exceptionDesable(Request $request)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');

        $data = $request->toArray();
        if (empty($data['keySecret']) || empty($data['idException'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete le keySecret ,idException sont requis'
            ], 400);
        }

        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

        if (!$clientUser) {
            return new JsonResponse([
                'message' => 'Desolez l\'utilisateur en question n\'existe pas dans la base de donnée'
            ], 400);
        }
        $possible = false;
        foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            if ($lr->isStatus() && $lr->getFonction()->getId() == '30') {
                $possible = true;
            }
        }
        if ($possible) {
            $exception = $bulkManager->getRepository(EntityBulkException::class)->findOneBy(['id' => $data['idException']]);


            $exception->setStatus(!$exception->isStatus());
            $bulkManager->persist($exception);
            $bulkManager->flush();
            $lExF = [];
            $lException = $bulkManager->getRepository(EntityBulkException::class)->findAll();

            foreach ($lException as $l) {
                if ($l->getText()) {
                    array_push($lExF, ['id' => $l->getId(), 'status' => $l->isStatus() ? 'Active' : 'Desactive', 'title' => $l->getText()]);
                }
            }




            return new JsonResponse([
                'status'
                =>
                true,
                'message'
                => 'Mise A jour Reussi',
                'esms'
                =>
                $lExF,

            ], 200);
        } else {

            return new JsonResponse([
                'message' => 'error'
            ], 400);
        }
    }
}
