<?php

namespace App\Controller\Account;

use App\Entity\Account\Compte;
use App\Entity\Account\TransactionCompte;
use App\Entity\Account\TypeCompte;
use App\Entity\Account\TypeTransaction;
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

class AccountController extends AbstractController
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
     * @Route("/transaction/commission", name="transactioCommission", methods={"POST"})
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
     * 
     * recuperation des infos commision d'une transaction
     * 
     */
    public function transasctionCommissionInfo(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $data = $request->toArray();


        if (empty($data['clientId'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser l\'id du client'
            ], 400);
        }
        $listTransaction = [];
        $dataT = null;
        $clientId = $data['clientId'];
        $LTransaction =
            $AccountEntityManager->getRepository(TransactionCompte::class)->findAll();
        foreach ($LTransaction as  $Transaction) {


            $clientUser = $this->em->getRepository(Client::class)->findOneBy(['id' => $Transaction->getClientId()]);

            if ($clientUser) {

                if ($clientUser->getCodeParrain() !== null) {


                    $parrain = $this->em->getRepository(Client::class)->findOneBy(['id' => explode('@', $clientUser->getCodeParrain())[0]]);
                    if ($parrain) {


                        $parrain1 = $this->em->getRepository(Client::class)->findOneBy(['id' => explode('@', $parrain->getCodeParrain())[0]]);



                        if ($parrain->getId() == $clientId) {

                            $dataT = [
                                'nom' => $clientUser->getNom(),
                                'phone' => $clientUser->getPhone(),
                                'entreprise' => $clientUser->getNomEntreprise(),
                                'commissionT' => $Transaction->getMontantPartage(),
                                'commission' => $Transaction->getMontantParrain(),
                                'date' =>    $Transaction->getDateCreate()->format('d/m/Y'),
                            ];

                            array_push($listTransaction, $dataT);
                        } else if ($parrain1) {

                            if ($parrain1->getId() == $clientId) {



                                $dataT = [
                                    'nom' => $clientUser->getNom(),
                                    'phone' => $clientUser->getPhone(),
                                    'entreprise' => $clientUser->getNomEntreprise(),
                                    'commissionT' => $Transaction->getMontantPartage(),
                                    'commission' => $Transaction->getMontantParrain2(),
                                    'date' =>  $Transaction->getDateCreate()->format('d/m/Y'),

                                ];

                                array_push($listTransaction, $dataT);
                            } else {
                            }
                        }
                    }
                } else {
                }
            } else {
            }
        }
        return
            new JsonResponse([
                'data'
                =>   array_reverse($listTransaction),

            ], 200);
    }


    /**
     * @Route("/transaction/account/read", name="transactionAccountRead", methods={"POST"})
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
     * @param array $data doit contenir la cle secrete du client
     * 
     * 
     */
    public function transactionAccountRead(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $data = $request->toArray();
        $possible = true;
        if (empty($data['keySecret']) || empty($data['typeCompte'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret et le typeCompte '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser) {

            $listTransaction = [];
            if (
                $data['typeCompte'] == 2

            ) {
                $serializer = $this->get('serializer');

                $typeCompte = $AccountEntityManager->getRepository(
                    TypeCompte::class
                )->findOneBy(['id' => $data['typeCompte']]);
                $typeTransactionA = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 1]);
                $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 4]);
                $recepteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $clientUser->getId(), 'typeCompte' => $typeCompte]);


                if ($possible) {
                    $lA = $AccountEntityManager->getRepository(TransactionCompte::class)->findBy(['recepteur' => $recepteurCompte, 'typeTransaction' => $typeTransactionA]);

                    foreach ($lA  as $tu) {

                        array_push($listTransaction, $tu);
                    }
                    $lA = $AccountEntityManager->getRepository(TransactionCompte::class)->findBy(['recepteur' => $recepteurCompte, 'typeTransaction' => $typeTransactionD]);

                    foreach ($lA  as $ts) {

                        array_push($listTransaction, $ts);
                    }



                    return
                        new JsonResponse([

                            'data'
                            =>
                            JSON_DECODE($serializer->serialize(array_reverse($listTransaction), 'json')),

                        ], 200);
                } else {
                    return new JsonResponse([
                        'data'
                        => [],
                        'message' => 'Action impossible'
                    ], 200);
                }
            } else  if (
                $data['typeCompte'] == 3

            ) {
                $serializer = $this->get('serializer');

                $typeCompte = $AccountEntityManager->getRepository(
                    TypeCompte::class
                )->findOneBy(['id' => $data['typeCompte']]);
                $typeTransactionA = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 1]);
                $emetteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $clientUser->getId(), 'typeCompte' => $typeCompte]);


                if ($possible) {
                    $lA = $AccountEntityManager->getRepository(TransactionCompte::class)->findBy(['emetteur' => $emetteurCompte, 'typeTransaction' => $typeTransactionA]);

                    foreach ($lA  as $tu) {

                        array_push($listTransaction, $tu);
                    }



                    return
                        new JsonResponse([

                            'data'
                            =>
                            JSON_DECODE($serializer->serialize(array_reverse($listTransaction), 'json')),

                        ], 200);
                } else {
                    return new JsonResponse([
                        'data'
                        => [],
                        'message' => 'Action impossible'
                    ], 200);
                }
            } else {
                return new JsonResponse([
                    'data'
                    => [],
                    'message' => 'Action impossible'
                ], 200);
            }
        } else {
            return new JsonResponse([
                'data'
                => [],
                'message' => 'Client introuvable'
            ], 400);
        }
    }
}
