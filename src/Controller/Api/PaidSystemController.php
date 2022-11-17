<?php

namespace App\Controller\Api;

use App\Entity\Account\Commission;
use App\Entity\Account\Compte;
use App\Entity\Account\TransactionCompte;
use App\Entity\Account\TypeCompte;
use App\Entity\Account\TypeTransaction;
use App\Entity\Auth\Client;
use App\Entity\Licence\Facture;
use App\Entity\Licence\Licence;
use App\Entity\Licence\ListSMSAchette;
use App\Entity\Licence\TypeManipulation;
use App\Entity\Licence\ListSmsManipule;
use App\Entity\Account\ModePaiement;
use App\Entity\Route\Route as RouteRoute;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FFI\Exception;
use phpDocumentor\Reflection\Types\Integer;
use Proxies\__CG__\App\Entity\Account\ListCommissionTransaction;
use Proxies\__CG__\App\Entity\Licence\Licence as LicenceLicence;
use Proxies\__CG__\App\Entity\Route\Route as EntityRouteRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\WebLink\HttpHeaderSerializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaidSystemController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em,   ManagerRegistry $doctrine)
    {
        $this->em = $em;
        $this->doctrine = $doctrine;
    }


    public function getUniqueTransactionId()
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');

        $chaine = '';
        $listeCar = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = mb_strlen($listeCar, '8bit') - 1;
        for ($i = 0; $i < 7; ++$i) {
            $chaine .= $listeCar[random_int(0, $max)];
        }
        $ExistTransaction = $AccountEntityManager->getRepository(TransactionCompte::class)->findOneBy(['transactionId' => $chaine]);
        if ($ExistTransaction) {
            return
                $this->getUniqueTransactionId();
        } else {
            return $chaine;
        }
    }



    /**
     * @Route("/system/credit", name="systemCredit", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function systemCredit(Request $request)
    {

        /**
         * => pour un depot , la request doit contenir  le montant, address, city, countryCode, 
         *  codeZip, type, typeTransaction [ 1 =depot, 2 =retrait],clientId
         *  modedepaiement permet de recuper siteId
         * 
         * v
         */
        $data = $request->toArray();


        $AccountEntityManager = $this->doctrine->getManager('Account');


        if (
            empty($data['montant']) || empty($data['keySecret']) || empty($data['destinataire'])
            || empty($data['typeCompte'])
        ) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  '
            ], 400);
        }


        /**
         * $siteId doit etre recuperer online
         */
        $modeP =    $AccountEntityManager->getRepository(ModePaiement::class)->findOneBy(['id' => $data['typeCompte']]);
        $chaine =
            $this->getUniqueTransactionId();
        $amount = $data['montant'];
        $client =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        $clientId = $client->getId();
        $transactionId =  'DEVOO' . $chaine;

        $destinataireC =
            $this->em->getRepository(Client::class)->findOneBy(['id' => $data['destinataire']]);
        if ($client && $destinataireC) {


            foreach ($client->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '7') {
                    $possible = true;
                }
            }
            if ($possible) {
                $AccountEntityManager = $this->doctrine->getManager('Account');
                $typeCompte = $AccountEntityManager->getRepository(
                    TypeCompte::class
                )->findOneBy(['id' => $data['typeCompte']]);
                $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 4]);
                $recepteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $data['destinataire'], 'typeCompte' => $typeCompte]);
                $transaction = new TransactionCompte();
                $transaction->setClientId($data['destinataire']);
                $transaction->setRecepteur($recepteurCompte);
                $transaction->setTransactionId($transactionId);
                $transaction->setTypeTransaction($typeTransactionD);
                $transaction->setModePaiement($modeP);;
                $transaction->setMontant($amount);
                $transaction->setToken($transactionId);
                $transaction->setStatus(1);
                $transaction->setDescription('Depot Sur le Conpte de Mr ' . $destinataireC->getNom());

                $AccountEntityManager->persist($transaction);
                $AccountEntityManager->flush();
                $recepteurCompte->setSolde(
                    $recepteurCompte->getSolde() + $amount
                );
                $AccountEntityManager->persist($recepteurCompte);
                $AccountEntityManager->flush();
                $this->sendCommission($transaction->getId());
                return
                    new JsonResponse([

                        'message' => 'Succes',
                    ], 201);
            } else {
                return new JsonResponse(['message' => 'Vous n\'avez pas le droit d\'effectuer cette action'], 203);
            }
        } else {
            return new JsonResponse(['message' => 'Une erreur est survenue lors du deroulement de votre operation'], 400);
        }
    }



    /**
     * @Route("/system/credit/ask", name="systemAskCredit", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function systemAskCredit(Request $request)
    {


        $data = $request->toArray();


        $AccountEntityManager = $this->doctrine->getManager('Account');


        if (
            empty($data['montant'])  || empty($data['destinataire'])
            /*    || empty($data['typeCompte']) */
        ) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  '
            ], 400);
        }


        $modeP =  $AccountEntityManager->getRepository(ModePaiement::class)->findOneBy(['id' => 1]);


        $chaine =
            $this->getUniqueTransactionId();;
        $amount = $data['montant'];

        $transactionId =  'DEVOO' . $chaine;

        $destinataireC =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['destinataire']]);

        if ($destinataireC) {
            $AccountEntityManager = $this->doctrine->getManager('Account');
            $typeCompte = $AccountEntityManager->getRepository(
                TypeCompte::class
            )->findOneBy(['id' => 2]);
            $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 4]);
            $recepteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $destinataireC->getId(), 'typeCompte' => $typeCompte]);
            if ($recepteurCompte) {
                $transaction = new TransactionCompte();

                $transaction->setRecepteur($recepteurCompte);
                $transaction->setTransactionId($transactionId);
                $transaction->setClientId($destinataireC->getId());
                $transaction->setTypeTransaction($typeTransactionD);
                $transaction->setModePaiement($modeP);;
                $transaction->setMontant($amount);
                $transaction->setToken($transactionId);
                $transaction->setStatus(0);
                $transaction->setDescription('Demande de Depot Sur le Conpte de Mr ' . $destinataireC->getNom());

                $AccountEntityManager->persist($transaction);
                $AccountEntityManager->flush();
                return
                    new JsonResponse([

                        'message' => 'Succes',
                    ], 201);
            } else {
                return new JsonResponse(['message' => 'Une erreur est survenue'], 203);
            }
        } else {
            return new JsonResponse(['message' => 'Vous n\'avez pas le droit d\'effectuer cette action'], 203);
        }
    }









    /**
     * @Route("/system/credit/validate", name="systemValidateCredit", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function systemValidateCredit(Request $request)
    {

        /**
         * => pour un depot , la request doit contenir  le montant, address, city, countryCode, 
         *  codeZip, type, typeTransaction [ 1 =depot, 2 =retrait],clientId
         *  modedepaiement permet de recuper siteId
         * 
         * v
         */
        $data = $request->toArray();


        $AccountEntityManager = $this->doctrine->getManager('Account');


        if (
            empty($data['keySecret']) || empty($data['idTransaction'])
        ) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  '
            ], 400);
        }
 
        $client =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        $clientId = $client->getId();

        if ($client) {


            foreach ($client->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '7') {
                    $possible = true;
                }
            }
            if ($possible) {

                $transaction = $AccountEntityManager->getRepository(TransactionCompte::class)->findOneBy(['id' => $data['idTransaction']]);
                $recepteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['id' => $transaction->getRecepteur()->getId()]);

                $transaction->setStatus(1);

                $transaction->setDescription('Depot Sur le Conpte de Mr ' . $client->getNom());
                $AccountEntityManager->persist($transaction);
                $AccountEntityManager->flush();

                $recepteurCompte->setSolde(
                    $recepteurCompte->getSolde() +
                        $transaction->getMontant()
                );
                $AccountEntityManager->persist($recepteurCompte);
                $AccountEntityManager->flush();
                $this->sendCommission($transaction->getId());
                return
                    new JsonResponse([

                        'message' => 'Succes',
                    ], 201);
            } else {
                return new JsonResponse(['message' => 'Vous n\'avez pas le droit d\'effectuer cette action'], 203);
            }
        } else {
            return new JsonResponse(['message' => 'Une erreur est survenue lors du deroulement de votre operation'], 400);
        }
    }

    /**
     * @Route("/system/credit/read", name="systemCreditRead", methods={"POST"})
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
     * @param array $data doit contenur clientId
     * 
     * 
     */
    public function systemCreditRead(Request $request)
    {
        $ltransaction = [];

        $AccountEntityManager = $this->doctrine->getManager('Account');

        $data = $request->toArray();
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        if ($clientUser) {

            $serializer = $this->get('serializer');
            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->getId() == '1') {
                    $possible = true;
                }
            }


            if ($possible) {
                $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 4]);

                $listTransaction = $AccountEntityManager->getRepository(TransactionCompte::class)->findBy(['typeTransaction' => $typeTransactionD]);
                foreach ($listTransaction as $transaction) {
                    if (
                        $transaction->getRecepteur() &&
                        $clientUser
                    ) {
                        $clientUser =
                            $this->em->getRepository(Client::class)->findOneBy(['id' => $transaction->getRecepteur()->getClientId()]);

                        $transactionA = [
                            'id' => $transaction->getId(),
                            'date' =>
                            $transaction->getDateCreate()->format('d/m/Y H:i'),
                            'montant' => $transaction->getMontant(),
                            'status' => $transaction->getStatus() == 1 ? "Reussi" : "En attente",
                            'statusB' => $transaction->getStatus(),
                            'compte' => $transaction->getRecepteur()->getTypeCompte()->getLibelle(),
                            'recepteur' =>   $clientUser->getNom()
                        ];
                        array_push($ltransaction, $transactionA);
                    }
                }
                $ltransactionFinal = $serializer->serialize(array_reverse($ltransaction), 'json');



                return new JsonResponse(
                    [
                        'data' => JSON_DECODE(
                            $ltransactionFinal
                        )
                    ],
                    200
                );
            } else {

                return new JsonResponse([
                    'message' => 'Action impossible'
                ], 400);
            }
        }
    }

    public function sendCommission(int $transactionCompteId /* Request $request */)
    {

        // $data = $request->toArray();
        //   dd($transactionCompteId);
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $transaction = $AccountEntityManager->getRepository(TransactionCompte::class)->findOneBy(['id' =>   $transactionCompteId  /*  $data['idT'] */]);

        if ($transaction) {


            /**
             * commission sur les depots solde local
             */
            if ($transaction->getTypeTransaction()->getId() == 4) {

                $clientUser = $this->em->getRepository(Client::class)->findOneBy(['id' => $transaction->getRecepteur()->getClientId()]);
                $commission = $AccountEntityManager->getRepository(Commission::class)->findOneBy(['id' => 1]);

                if ($clientUser) {

                    if ($clientUser->getCodeParrain()) {
                        $typeCompte = $AccountEntityManager->getRepository(
                            TypeCompte::class
                        )->findOneBy(['id' => 2]);

                        $parrain = $this->em->getRepository(Client::class)->findOneBy(['id' => explode('@', $clientUser->getCodeParrain())[0]]);
                        if ($parrain) {




                            $parrain1 = $this->em->getRepository(Client::class)->findOneBy(['id' => explode('@', $parrain->getCodeParrain())[0]]);
                            if ($parrain1) {
                                $commissionParrain = $transaction->getMontant() * $commission->getPourcentageParrain() / 100;
                                $transaction->setMontantPartage($transaction->getMontant() * $commission->getPourcentagePartage() / 100);

                                $commissionParrain1 = $commissionParrain * $commission->getPourcentageParrain2() / 100;

                                $parrainCompte =
                                    $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $parrain->getId(), 'typeCompte' => $typeCompte]);
                                $parrainCompte->setSolde($parrainCompte->getSolde() +   $commissionParrain - $commissionParrain1);
                                $parrain1Compte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $parrain1->getId(), 'typeCompte' => $typeCompte]);
                                $parrain1Compte->setSolde($parrain1Compte->getSolde() +   $commissionParrain1);
                                // var_dump($commissionParrain - $commissionParrain1);
                                // var_dump($commissionParrain1);
                                $AccountEntityManager->persist($parrainCompte);
                                $AccountEntityManager->persist($parrain1Compte);


                                $transaction->setMontantParrain($commissionParrain - $commissionParrain1);
                                $transaction->setMontantParrain2($commissionParrain1);
                            } else {
                                $commissionParrain = $transaction->getMontant() * $commission->getPourcentagePartage() / 100;

                                $parrainCompte =
                                    $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $parrain->getId(), 'typeCompte' => $typeCompte]);
                                $parrainCompte->setSolde($parrainCompte->getSolde() + $commissionParrain);
                                $AccountEntityManager->persist($parrainCompte);

                                $transaction->setMontantParrain($commissionParrain);
                            }
                            $lct = new ListCommissionTransaction();
                            $lct->setTransaction(
                                $transaction
                            );
                            $lct->setCommission($commission);
                            $lct->setDate(new \DateTime());

                            $AccountEntityManager->persist($lct);
                            $AccountEntityManager->persist($transaction);
                            $AccountEntityManager->flush();

                            return new JsonResponse([
                                'message' => 'ok'
                            ], 201);
                        }
                    } else {
                        return new JsonResponse([
                            'message' => 'Aucun parrain '
                        ], 201);
                    }
                } else {
                    return new JsonResponse([
                        'message' => 'Invalid '
                    ], 201);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Aucune commision sur cette transaction'
                ], 201);
            }
        } else {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser une transaction correcte'
            ], 201);
        }
    }
}
