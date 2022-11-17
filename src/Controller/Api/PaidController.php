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

class PaidController extends AbstractController
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

    // *
    //  * @Route("/sendCommission", name="sendCommission", methods={"POST"})
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @throws ClientExceptionInterface
    //  * @throws DecodingExceptionInterface
    //  * @throws RedirectionExceptionInterface
    //  * @throws ServerExceptionInterface
    //  * @throws TransportExceptionInterface
    //  * @throws \Exception
    //  * 

    public function sendCommission(int $transactionCompteId /* Request $request */)
    {

        // $data = $request->toArray();
        // dd($data);
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $transaction = $AccountEntityManager->getRepository(TransactionCompte::class)->findOneBy(['id' =>   $transactionCompteId  /*  $data['idT'] */]);

        if ($transaction) {

            /**
             * commission sur l'achat d'sms
             */
            if ($transaction->getTypeTransaction()->getId() == 3) {
                $clientUser = $this->em->getRepository(Client::class)->findOneBy(['id' => $transaction->getClientId()]);
                $commission = $AccountEntityManager->getRepository(Commission::class)->findOneBy(['id' => 1]);

                if ($clientUser) {

                    if ($clientUser->getCodeParrain()) {
                        $typeCompte = $AccountEntityManager->getRepository(
                            TypeCompte::class
                        )->findOneBy(['id' => 3]);

                        $parrain = $this->em->getRepository(Client::class)->findOneBy(['id' => explode('@', $clientUser->getCodeParrain())[0]]);
                        if ($parrain) {




                            $parrain1 = $this->em->getRepository(Client::class)->findOneBy(['id' => explode('@', $parrain->getCodeParrain())[0]]);
                            if ($parrain1) {
                                $commissionParrain = $transaction->getMontant() * $commission->getPourcentageParrain() / 100;
                                $transaction->setMontantPartage(round($transaction->getMontant() * $commission->getPourcentagePartage() / 100));

                                $commissionParrain1 = $commissionParrain * $commission->getPourcentageParrain2() / 100;

                                $parrainCompte =
                                    $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $parrain->getId(), 'typeCompte' => $typeCompte]);
                                $parrainCompte->setSolde(round($parrainCompte->getSolde() +   $commissionParrain - $commissionParrain1));
                                $parrain1Compte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $parrain1->getId(), 'typeCompte' => $typeCompte]);
                                $parrain1Compte->setSolde(round($parrain1Compte->getSolde() +   $commissionParrain1));
                                var_dump($commissionParrain - $commissionParrain1);
                                var_dump($commissionParrain1);
                                $AccountEntityManager->persist($parrainCompte);
                                $AccountEntityManager->persist($parrain1Compte);


                                $transaction->setMontantParrain(round($commissionParrain - $commissionParrain1));
                                $transaction->setMontantParrain2(round($commissionParrain1));
                            } else {
                                $commissionParrain = $transaction->getMontant() * $commission->getPourcentagePartage() / 100;

                                $parrainCompte =
                                    $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $parrain->getId(), 'typeCompte' => $typeCompte]);
                                $parrainCompte->setSolde(round($commissionParrain));
                                $AccountEntityManager->persist($parrainCompte);

                                $transaction->setMontantParrain(round($commissionParrain));
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
    /**
     * @Route("/licence/paid", name="paidlicence", methods={"POST"})
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
     * l'achat d'une licence peut se faire par moyen de paiement soit par le compte local
     * 
     */
    public function paidLicenceIndex(Request $request)
    {
        /**
         * request doit contenir 
         * si sourcePaiement 1 => compteLocal: [clientId,recepteurId,idLicence,routeId,quantite, montant Total de la transaction];
         *  si sourcePaiement 2 => electronique paiement :[ address, city, countryCode,  codeZip, type
         * quantiteSms , routeId, clientId,recepteurId,idLicence,  montant Total de la transaction]
         */
        $data = $request->toArray();
        $quantiteSms = $data['quantite'];

        $content = [];
        $statusCode = 400;
        $apikey = "27139936162a84bbe3f5ad5.24286892";
        if (empty($data['sourcePaiement'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser sourcePaiement'
            ], 400);
        }
        $licenceManager = $this->doctrine->getManager('Licence');
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $routeManager = $this->doctrine->getManager('Route');
        $clientId = $data['clientId'];

        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['id' => $clientId]);
        if ($clientUser) {
            $serializer = $this->get('serializer');

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '5') {
                    $possible = true;
                }
            }
            $recepteurId = $data['recepteurId'];
            $chaine =
                $this->getUniqueTransactionId();;


            $transactionId =  'DEVOO' . $chaine;
            $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 3]);
            if ($data['sourcePaiement'] == '2') {

                if (empty($data['montant']) || empty($data['quantite']) || empty($data['type']) || empty($data['modePaiement'])) {
                    return new JsonResponse([
                        'message' => 'Mauvais parametre de requete  montant ,clientId, type,mode de paiement sont requis'
                    ], 400);
                }

                if ($data['type'] !== 'MOBILE_MONEY') {
                    if (empty($data['montant'])   || empty($data['quantite']) || empty($data['type']) || empty($data['city']) || empty($data['modePaiement']) || empty($data['address']) || empty($data['codeZip']) || empty($data['countryCode'])) {
                        return new JsonResponse([
                            'message' => 'Pour un paiement par carte bancaire le  montant, facture ,clientId,mode de paiement, type, city, address, codeZip, countryCode sont requis'
                        ], 400);
                    }
                }
                if ($data['quantite'] < 10) {
                    return new JsonResponse([
                        'message' => 'Mauvais parametre de requete  quantite minimale d\'sms a achetr 10 sms'
                    ], 400);
                }

                // $abonnement = $this->em->getRepository(Abonnement::class)->find($data['abonnementId']);


                $modeP
                    =  $AccountEntityManager->getRepository(ModePaiement::class)->findOneBy(['id' => $data['modePaiement']]);
                /**
                 * $siteId doit etre recuperer online
                 */

                $siteId =     $modeP->getSiteId();


                $routeId = $data['routeId'];
                $amount = $data['montant'];
                $client =
                    $this->em->getRepository(Client::class)->findOneBy(['id' => $clientId]);

                $customerName = $client->getNom();
                $customerSurname
                    = $client->getPrenom();
                $customerId = $data['clientId'];
                $currency = "XAF";
                $alternative_currency = "USD";
                $description = "Transaction clientId " . $clientId . " en cour";
                $notifyUrl = "https://dashboard.pubx.cm";
                $returnUrl = "https://dashboard.pubx.cm";
                $channels = $data['type'] !== 'MOBILE_MONEY' ? 'CREDIT_CARD' : $data['type'];

                if ($data['type'] !== 'MOBILE_MONEY') {
                    $customer_phone_number =
                        $client->getPhone();
                    $customer_email
                        = $client->getEmail();
                    $customer_address = $data['address'];
                    $customer_city = $data['city'];
                    $customer_country = $data['countryCode'];
                    $customer_zip_code = $data['codeZip'];

                    if (!empty($data['stateUser'])) {
                        $customer_state = $data['stateUser'];
                        $response = $this->client->request(
                            'POST',
                            'https://api-checkout.cinetpay.com/v2/payment',
                            [
                                "json" => [
                                    "amount" => $amount,
                                    "apikey" => $apikey,
                                    "site_id" => $siteId,
                                    "currency" => $currency,
                                    "transaction_id" => $transactionId,
                                    "return_url" => $returnUrl,
                                    "notify_url" => $notifyUrl,
                                    "description" => $description,
                                    "customer_id" => $customerId,
                                    "customer_name" => $customerName,
                                    "customer_surname" => $customerSurname,
                                    "channels" => $channels,
                                    "customer_phone_number" => $customer_phone_number,
                                    "customer_email" => $customer_email,
                                    "customer_address" => $customer_address,
                                    "customer_city" => $customer_city,
                                    "customer_country" => $customer_country,
                                    "customer_state" => $customer_state,
                                    "customer_zip_code" => $customer_zip_code,

                                ]
                            ]
                        );
                    } else {
                        $response = $this->client->request(
                            'POST',
                            'https://api-checkout.cinetpay.com/v2/payment',
                            [
                                "json" => [
                                    "amount" => $amount,
                                    "apikey" => $apikey,
                                    "site_id" => $siteId,
                                    "currency" => $currency,
                                    "transaction_id" => $transactionId,
                                    "return_url" => $returnUrl,
                                    "notify_url" => $notifyUrl,
                                    "description" => $description,
                                    "customer_id" => $customerId,
                                    "customer_name" => $customerName,
                                    "customer_surname" => $customerSurname,
                                    "channels" => $channels,
                                    "customer_phone_number" => $customer_phone_number,
                                    "customer_email" => $customer_email,
                                    "customer_address" => $customer_address,
                                    "customer_city" => $customer_city,
                                    "customer_country" => $customer_country,
                                    "customer_zip_code" => $customer_zip_code,

                                ]
                            ]
                        );
                    }
                } else {
                    $dataRequest
                        = [
                            "apikey" => $apikey,
                            "transaction_id" => $transactionId,
                            "site_id" => $siteId,
                            "amount" => $amount,
                            "currency" => $currency,
                            "description" => $description,
                            "customer_id" => $customerId,
                            "customer_name" => $customerName,
                            "customer_surname" => $customerSurname,
                            "metadata" => "user" . $customerId,
                            "return_url" => $returnUrl,
                            "notify_url" => $notifyUrl,
                            "channels" => $channels
                        ];
                    //  dd($dataRequest);
                    $response = $this->client->request(
                        'POST',
                        'https://api-checkout.cinetpay.com/v2/payment',
                        [
                            "json" => $dataRequest
                        ]
                    );
                }

                if ($response) {
                    // var_dump($dataRequest);
                    if ($response->toArray()["code"] == "201") {
                        $statusCode = $response->getStatusCode();
                        $content0 = $response->toArray();





                        $typeCompte = $AccountEntityManager->getRepository(
                            TypeCompte::class
                        )->findOneBy(['id' => 3]);

                        $recepteur = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $recepteurId, 'typeCompte' => $typeCompte]);
                        $licence =
                            /* $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 3]) */ 3;
                        $factureR = $licenceManager->getRepository(Facture::class)->findBy(['clientId' => $recepteurId, 'licence' => $licence]);
                        $listSMSAcheteId = 0;
                        if ($factureR) {

                            foreach ($factureR as $key => $facture) {
                                # code...
                                $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['routeId' => $data['routeId'], 'facture' => $facture]);
                                if ($lsa) {
                                    $listSMSAcheteId =
                                        $lsa->getId();
                                    break;
                                }

                                // $lsa->setQuantite($lsa->getQuantite() + $quantiteSms);
                                // $licenceManager->persist($lsa);
                                // $licenceManager->flush();



                            }
                        } else {
                            //    $licence =
                            // $licenceManager->getRepository(Licence::class)->findOneBy(['id' => $data['idLicence']]);
                            $facture = new Facture();
                            $facture->setLicence($licence);
                            $facture->setMontant($amount);
                            $facture->setClientId($recepteurId);
                            $listSMSAchete = new ListSMSAchette();
                            $listSMSAchete->setFacture($facture);
                            $listSMSAchete->setRouteId($routeId);
                            $licenceManager->persist($facture);
                            $licenceManager->persist($listSMSAchete);
                            $licenceManager->flush();
                            $listSMSAcheteId =
                                $listSMSAchete->getId();
                        }

                        $content =
                            [
                                'code' =>
                                $content0['code'],
                                'message' =>
                                $content0['message'],
                                'payment_token' =>
                                $content0['data']['payment_token'],
                                'payment_url' =>
                                $content0['data']['payment_url'],
                                'idListSmsAchete' =>
                                $listSMSAcheteId,
                            ];
                        $transaction = new TransactionCompte();
                        $transaction->setClientId($clientId);
                        $transaction->setRecepteur($recepteur);
                        $transaction->setTransactionId($transactionId);
                        $transaction->setTypeTransaction($typeTransactionD);
                        $transaction->setModePaiement($modeP);;
                        $transaction->setMontant($amount);
                        $transaction->setToken($content["payment_token"]);
                        $transaction->setDescription('Achat de sms ');
                        $transaction->setStatus(0);
                        $AccountEntityManager->persist($transaction);
                        $AccountEntityManager->flush();
                    } else {
                        $content = [
                            'message' => 'Une erreur est survenue durant l\'operation'
                        ];
                        $statusCode = 400;
                    }
                } else {
                    $content = [
                        'message' => 'Une erreur est survenue durant l\'operation'
                    ];
                    $statusCode = 400;
                }
            } else  if ($data['sourcePaiement'] == '1') {

                $modeP
                    =  $AccountEntityManager->getRepository(ModePaiement::class)->findOneBy(['id' => $data['modePaiement']]);
                // dd($data);
                if (empty($data['montant'])  || empty($data['quantite']) || empty($data['clientId']) || empty($data['idLicence']) || empty($data['routeId'])) {
                    return new JsonResponse([
                        'message' => 'Mauvais parametre de requete  montant ,clientId,routeId,idLicence sont requis'
                    ], 400);
                }

                $amount = $data['montant'];
                $clientId = $data['clientId'];
                $licence =
                    /* $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 3]) */ 3;
                // $route =
                //     $routeManager->getRepository(RouteRoute::class)->findOneBy(['id' => $data['routeId']]);
                $typeCompteLocal = $AccountEntityManager->getRepository(
                    TypeCompte::class
                )->findOneBy(['id' => 2]);
                $typeCompteSms = $AccountEntityManager->getRepository(
                    TypeCompte::class
                )->findOneBy(['id' => 1]);
                $compteLocal =
                    $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $clientId, 'typeCompte' =>   $typeCompteLocal]);
                $compteSms =
                    $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $recepteurId, 'typeCompte' =>   $typeCompteSms]);
                $presentF = true;
                if ($compteLocal != null && $compteSms != null) {
                    if ($compteLocal->getSolde() >= $amount) {
                        $licence =
                            /* $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 3]) */ 3;
                        $factureE = $licenceManager->getRepository(Facture::class)->findBy(['clientId' => $recepteurId, 'licence' => $licence]);
                        if ($factureE) {

                            foreach ($factureE as $key => $facture) {

                                $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['routeId' => $data['routeId'], 'facture' => $facture]);
                                if ($lsa) {
                                    $lsa->setQuantite($lsa->getQuantite() + $quantiteSms);
                                    $licenceManager->persist($lsa);
                                    $licenceManager->flush();

                                    $presentF = false;
                                    break;
                                }
                            }
                        }
                        // dd($presentF);
                        if ($presentF) {
                            $facture = new Facture();
                            $facture->setLicence($licence);
                            $facture->setMontant($amount);
                            $facture->setStatus(1);

                            $facture->setClientId($recepteurId);
                            $routeId = $data['routeId'];
                            $listSMSAchete = new ListSMSAchette();
                            $listSMSAchete->setFacture($facture);
                            $listSMSAchete->setRouteId($routeId);

                            $listSMSAchete->setStatus(1);
                            $listSMSAchete->setQuantite($quantiteSms);


                            $licenceManager->persist($facture);
                            $licenceManager->persist($listSMSAchete);
                            $licenceManager->flush();
                            $transaction = new TransactionCompte();
                            $transaction->setClientId($clientId);
                            $transaction->setRecepteur($compteSms);
                            $transaction->setTransactionId($transactionId);
                            $transaction->setTypeTransaction($typeTransactionD);
                            $transaction->setModePaiement($modeP);;
                            $transaction->setMontant($amount);
                            $transaction->setToken('00000');
                            $transaction->setDescription('Achat de sms ');
                            $transaction->setStatus(0);
                            $AccountEntityManager->persist($transaction);
                            $compteLocal->setSolde($compteLocal->getSolde() - $amount);
                            $AccountEntityManager->flush();

                            $compteSms->setSolde($compteSms->getSolde() + $quantiteSms);
                            $AccountEntityManager->persist($compteSms);
                            $AccountEntityManager->persist($compteLocal);
                            $AccountEntityManager->flush();
                        } else {
                            $facture = new Facture();
                            $facture->setLicence($licence);
                            $facture->setMontant($amount);
                            $facture->setStatus(1);

                            $facture->setClientId($recepteurId);
                            $routeId = $data['routeId'];

                            $licenceManager->persist($facture);
                            $licenceManager->flush();
                            $transaction = new TransactionCompte();
                            $transaction->setClientId($clientId);
                            $transaction->setRecepteur($compteSms);
                            $transaction->setTransactionId($transactionId);
                            $transaction->setTypeTransaction($typeTransactionD);
                            $transaction->setModePaiement($modeP);;
                            $transaction->setMontant($amount);
                            $transaction->setToken('00000');
                            $transaction->setDescription('Achat de sms ');
                            $transaction->setStatus(0);
                            $AccountEntityManager->persist($transaction);
                            $compteLocal->setSolde($compteLocal->getSolde() - $amount);
                            $AccountEntityManager->flush();

                            $compteSms->setSolde($compteSms->getSolde() + $quantiteSms);
                            $AccountEntityManager->persist($compteSms);
                            $AccountEntityManager->persist($compteLocal);
                            $AccountEntityManager->flush();
                        }

                        $content = [

                            'message' => 'L\'operation s\'est deroule avec success'
                        ];
                        $statusCode = 201;
                    } else {

                        $content = [

                            'message' => 'Solde Compte Local inssufisant pour effectuer ce paiement'
                        ];
                        $statusCode = 203;
                    }
                } else {

                    $content = [
                        'message' => 'Une erreur est survenue durant l\'operation'
                    ];
                    $statusCode = 400;
                }
            }
        }
        return new JsonResponse($content, $statusCode);
    }

    /**
     * @Route("/licence/notify", name="notifyLicence", methods={"POST", "GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function notifyLicenceIndex(Request $request)
    {
        /**
         * request doit contenir  modePaiement, token,idListSmsAchete, quantite
         */
        $data = $request->toArray();

        if (empty($data['token']) || empty($data['modePaiement']) || empty($data['idListSmsAchete']) || empty($data['quantite'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez contacter le developpeur'
            ], 400);
        }
        $licenceManager = $this->doctrine->getManager('Licence');

        $AccountEntityManager = $this->doctrine->getManager('Account');
        $modeP
            =  $AccountEntityManager->getRepository(ModePaiement::class)->findOneBy(['id' => $data['modePaiement']]);
        $siteId =
            $modeP->getSiteId();
        $data = $request->toArray();

        $token = $data['token'];
        $listSMSAcheteId = $data['idListSmsAchete'];
        $quantiteSms = $data['quantite'];
        $apikey = "27139936162a84bbe3f5ad5.24286892";

        #----------- Verification de la transaction -------------------#
        $dataVerif = [
            "apikey" => $apikey,
            "site_id" => $siteId,
            "token" => $token
        ];
        dd($dataVerif);
        $response = $this->client->request(
            'POST',
            'https://api-checkout.cinetpay.com/v2/payment/check',
            [
                'json' => $dataVerif
            ]
        );

        $statusCode = $response->getStatusCode();
        $content = $response->toArray();

        if ($content["code"]  === "00") {
            $AccountEntityManager = $this->doctrine->getManager('Account');

            $listSMSAchete
                = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['id' => $listSMSAcheteId]);
            $typeCompteSms = $AccountEntityManager->getRepository(
                TypeCompte::class
            )->findOneBy(['id' => 1]);
            if ($listSMSAchete) {


                if (!$listSMSAchete->getStatus()) {
                    $listSMSAchete->setStatus(0);
                    $listSMSAchete->setQuantite($listSMSAchete->getQuantite() + $quantiteSms);
                    $licenceManager->persist($listSMSAchete);
                    $licenceManager->flush();
                    $Transaction =
                        $AccountEntityManager->getRepository(TransactionCompte::class)->findOneBy(['token' => $token]);

                    if ($Transaction) {
                        $this->sendCommission($Transaction->getId);
                        $compteSms =
                            $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' =>   $Transaction->getRecepteur(), 'typeCompte' =>   $typeCompteSms]);
                        if (!$Transaction->getStatus()) {

                            $montantFinalR = $Transaction->getRecepteur()->getSolde() + $Transaction->getMontant();
                            $Transaction->getRecepteur()->setSolde($montantFinalR);
                            $Transaction->setStatus(1);
                            $compteSms->setSolde($compteSms->getSolde() + $quantiteSms);
                            $AccountEntityManager->persist($compteSms);
                            $AccountEntityManager->flush();
                            $AccountEntityManager->persist($Transaction);
                            $AccountEntityManager->flush();
                            $AccountEntityManager->persist($Transaction);
                            $AccountEntityManager->flush();
                        }
                    } else {
                        // $paid->setDateUpdated((new \DateTime()));    

                    }
                }
            } else {

                // $this->em->persist($paid);
                // $this->em->flush();
            }

            return new JsonResponse($content, $statusCode);
        } else {
            return new JsonResponse($content, $statusCode);
        }
    }


    /**
     * @Route("/licence/read", name="licenceRead", methods={"POST"})
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
    public function licenceRead(Request $request)
    {

        $licenceUser = [];
        $BulkManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');
        $licenceManager = $this->doctrine->getManager('Licence');

        // $AuthEntityManager = $this->doctrine->getManager('Auth');
        $data = $request->toArray();
        $possible = false;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        if ($clientUser) {
            $serializer = $this->get('serializer');

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '6') {
                    $possible = true;
                }
            }


            if ($possible) {
                $factures =
                    $licenceManager->getRepository(Facture::class)->findBy(['clientId' => $clientUser->getId()]);

                if ($factures) {
                    foreach ($factures  as $facture) {
                        $lsa =
                            $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['facture' => $facture]);
                        if ($lsa) {
                            $nomroute =
                                $routeManager->getRepository(RouteRoute::class)->findOneBy(['id' => $lsa->getRouteId()])->getNom();
                            $lse = [
                                'id' => $lsa->getId(),
                                'quantite' => $lsa->getQuantite(),
                                'route' => $nomroute,
                                'date' => $lsa->getDate(),
                            ];
                            array_push($licenceUser,  $lse);
                        }
                    }



                    $licenceUserFinal = $serializer->serialize(array_reverse($licenceUser), 'json');


                    return
                        new JsonResponse([
                            'data'
                            =>
                            JSON_DECODE($licenceUserFinal),

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
                'message' => 'Client introuvable'
            ], 400);
        }
    }


    /**
     * @Route("/moneytransaction", name="moneyTransaction", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function moneyTransaction(Request $request)
    {

        /**
         * => pour un depot , la request doit contenir  le montant, address, city, countryCode, 
         *  codeZip, type, typeTransaction [ 1 =depot, 2 =retrait],clientId
         *  modedepaiement permet de recuper siteId
         * 
         * => pour un retrait, la request doit contenir le montant, clientId, la liste des contacts à ajouter
         */
        $data = $request->toArray();

        $apikey = "27139936162a84bbe3f5ad5.24286892";
        // $abonnement = $this->em->getRepository(Abonnement::class)->find($data['abonnementId']);
        $licenceManager = $this->doctrine->getManager('Licence');
        $AccountEntityManager = $this->doctrine->getManager('Account');
        // dd($data);
        if ($data['typeTransaction']  == '/api/type_transactions/1') {

            if (empty($data['montant']) || empty($data['clientId']) ||   empty($data['type'])  || empty($data['modePaiement'])) {
                return new JsonResponse([
                    'message' => 'Mauvais parametre de requete  '
                ], 400);
            }

            if ($data['type'] !== 'MOBILE_MONEY') {
                if (empty($data['montant']) ||   empty($data['type']) || empty($data['city']) || empty($data['modePaiement']) || empty($data['address']) || empty($data['codeZip']) || empty($data['countryCode'])) {
                    return new JsonResponse([
                        'message' => 'Pour un paiement par carte bancaire le  montant, facture ,clientId,mode de paiement, type, city, address, codeZip, countryCode sont requis'
                    ], 400);
                }
            }
            $channels = $data['type'] !== 'MOBILE_MONEY' ? 'CREDIT_CARD' : $data['type'];
        } else if ($data['typeTransaction']  == '/api/type_transactions/2') {
            if (empty($data['montant']) || empty($data['clientId'])  || empty($data['modePaiement'])) {
                return new JsonResponse([
                    'message' => 'Mauvais parametre de requete  '
                ], 400);
            }
        } else {
            return new JsonResponse([
                'message' => 'Preciser type transaction'
            ], 400);
        }



        /**
         * $siteId doit etre recuperer online
         */
        $modeP =    $AccountEntityManager->getRepository(ModePaiement::class)->findOneBy(['id' => $data['modePaiement']]);
        $siteId  = $modeP->getSiteId();

        $chaine =
            $this->getUniqueTransactionId();;


        $transactionId =  'DEVOO' . $chaine;
        $clientId = $data['clientId'];
        $client =
            $this->em->getRepository(Client::class)->findOneBy(['id' => $clientId]);
        if ($client) {


            foreach ($client->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '7' || $lr->getFonction()->getId() == '8' || $lr->getFonction()->getId() == '9' || $lr->getFonction()->getId() == '10' || $lr->getFonction()->getId() == '11') {
                    $possible = true;
                }
            }
            if ($possible) {
                $amount = $data['montant'];

                $customerName = $client->getNom();
                $customerSurname
                    = $client->getPrenom();
                $customerId = $data['clientId'];
                $currency = "XAF";
                $alternative_currency = "USD";
                $description = "Transaction clientId " . $clientId . " en cour";
                $notifyUrl = "https://webhook.site/d1dbbb89-52c7-49af-a689-b3c412df820d";
                $returnUrl = "https://webhook.site/d1dbbb89-52c7-49af-a689-b3c412df820d";

                /**
                 * depot sur le compte locale
                 */
                if ($data['typeTransaction'] == '/api/type_transactions/1') {

                    if ($data['type'] !== 'MOBILE_MONEY') {
                        $customer_phone_number =
                            $client->getPhone();
                        $customer_email
                            = $client->getEmail();
                        $customer_address = $data['address'];
                        $customer_city = $data['city'];
                        $customer_country = $data['countryCode'];
                        $customer_zip_code = $data['codeZip'];

                        if (!empty($data['stateUser'])) {
                            $customer_state = $data['stateUser'];

                            $response = $this->client->request(
                                'POST',
                                'https://api-checkout.cinetpay.com/v2/payment',
                                [
                                    "json" => [
                                        "amount" => $amount,
                                        "apikey" => $apikey,
                                        "site_id" => $siteId,
                                        "currency" => $currency,
                                        "transaction_id" => $transactionId,
                                        "return_url" => $returnUrl,
                                        "notify_url" => $notifyUrl,
                                        "description" => $description,
                                        "customer_id" => $customerId,
                                        "customer_name" => $customerName,
                                        "customer_surname" => $customerSurname,
                                        "channels" => $channels,
                                        "customer_phone_number" => $customer_phone_number,
                                        "customer_email" => $customer_email,
                                        "customer_address" => $customer_address,
                                        "customer_city" => $customer_city,
                                        "customer_country" => $customer_country,
                                        "customer_state" => $customer_state,
                                        "customer_zip_code" => $customer_zip_code,

                                    ]
                                ]
                            ); //code...

                        } else {
                            $response = $this->client->request(
                                'POST',
                                'https://api-checkout.cinetpay.com/v2/payment',
                                [
                                    "json" => [
                                        "amount" => $amount,
                                        "apikey" => $apikey,
                                        "site_id" => $siteId,
                                        "currency" => $currency,
                                        "transaction_id" => $transactionId,
                                        "return_url" => $returnUrl,
                                        "notify_url" => $notifyUrl,
                                        "description" => $description,
                                        "customer_id" => $customerId,
                                        "customer_name" => $customerName,
                                        "customer_surname" => $customerSurname,
                                        "channels" => $channels,
                                        "customer_phone_number" => $customer_phone_number,
                                        "customer_email" => $customer_email,
                                        "customer_address" => $customer_address,
                                        "customer_city" => $customer_city,
                                        "customer_country" => $customer_country,
                                        "customer_zip_code" => $customer_zip_code,

                                    ]
                                ]
                            );
                        }
                    } else {
                        $dataRequest
                            = [
                                "apikey" => $apikey,
                                "transaction_id" => $transactionId,
                                "site_id" => $siteId,
                                "amount" => $amount,
                                "currency" => $currency,
                                "description" => $description,
                                "customer_id" => $customerId,
                                "customer_name" => $customerName,
                                "customer_surname" => $customerSurname,
                                "metadata" => "user" . $customerId,
                                "return_url" => $returnUrl,
                                "notify_url" => $notifyUrl,
                                "channels" => $channels
                            ];
                        // dd($dataRequest);
                        $response = $this->client->request(
                            'POST',
                            'https://api-checkout.cinetpay.com/v2/payment',
                            [
                                "json" => $dataRequest
                            ]
                        );
                    }

                    $statusCode = $response->getStatusCode();
                    $content0 = $response->toArray();

                    if ($content0["code"] === "201") {

                        $content =
                            [
                                'code' =>
                                $content0['code'],
                                'message' =>
                                $content0['message'],
                                'payment_token' =>
                                $content0['data']['payment_token'],
                                'payment_url' =>
                                $content0['data']['payment_url'],

                            ];
                        $AccountEntityManager = $this->doctrine->getManager('Account');

                        $typeCompte = $AccountEntityManager->getRepository(
                            TypeCompte::class
                        )->findOneBy(['id' => 2]);
                        $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 1]);
                        $recepteur = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $clientId, 'typeCompte' => $typeCompte]);
                        $transaction = new TransactionCompte();
                        $transaction->setClientId($clientId);
                        $transaction->setRecepteur($recepteur);
                        $transaction->setTransactionId($transactionId);

                        $transaction->setTypeTransaction($typeTransactionD);
                        $transaction->setModePaiement($modeP);;
                        $transaction->setMontant($amount);
                        $transaction->setToken($content["payment_token"]);
                        $transaction->setDescription('Depot Sur le Conpte de Mr ' . $customerName);

                        $AccountEntityManager->persist($transaction);
                        $AccountEntityManager->flush();
                    } else {
                        return new JsonResponse(['data' => 'Une erreur est survenue lors du deroulement de votre operation'], $statusCode);
                    }
                    return new JsonResponse($content, $statusCode);
                } else  if ($data['typeTransaction'] == '/api/type_transactions/2') {
                    $typeCompte = $AccountEntityManager->getRepository(
                        TypeCompte::class
                    )->findOneBy(['id' => 3]);
                    $recepteur = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $clientId, 'typeCompte' => $typeCompte]);


                    if ($recepteur->getSolde() > $amount) {


                        try {
                            $data
                                = [
                                    "apikey" => '27139936162a84bbe3f5ad5.24286892',
                                    "password" => '/Or@nge2014*',

                                ];

                            $responseLogin = $this->client->request(
                                'POST',
                                'https://client.cinetpay.com/v1/auth/login',
                                [
                                    "body" =>
                                    $data,
                                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                                ],
                            );
                            $statusCode = $responseLogin->getStatusCode();

                            $dataLogin =   $responseLogin->toArray();

                            // $contact
                            //     =;
                            // $balance = $this->client->request(
                            //     'GET',
                            //     'https://client.cinetpay.com/v1/transfer/check/balance?token='
                            //     . $dataLogin['data']['token'],
                            // );
                            // dd($balance->toArray());

                            // dd('https://client.cinetpay.com/v1/transfer/contact?token='
                            //     . $dataLogin['data']['token'],);
                            $contact
                                =
                                [

                                    "data"
                                    =>    json_encode(
                                        [
                                            [
                                                "prefix" => "237",
                                                "phone" => $client->getPhone(),
                                                "name" =>  $client->getNom(),
                                                "surname" => $client->getPrenom(),
                                                "email" => $client->getEmail()
                                            ]
                                        ]
                                    )
                                ];

                            $responseAddContact = $this->client->request(
                                'POST',
                                'https://client.cinetpay.com/v1/transfer/contact?token='
                                    . $dataLogin['data']['token'],
                                [
                                    'body' =>
                                    $contact,
                                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],

                                ]
                            );

                            // dd($contact);

                            if ($responseAddContact->toArray()['code'] == '0') {


                                $dataRequest
                                    = [

                                        "data"
                                        =>    json_encode(
                                            [
                                                [
                                                    "amount" => $amount,
                                                    "phone" => $client->getPhone(),
                                                    "prefix" => "237",
                                                    "notify_url" => $notifyUrl
                                                ]
                                            ]
                                        )
                                    ];
                                // dd($dataRequest);
                                $response = $this->client->request(
                                    'POST',
                                    'https://client.cinetpay.com/v1/transfer/money/send/contact?token=' . $dataLogin['data']['token'],

                                    [
                                        'body' =>
                                        $dataRequest,
                                        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],

                                        // "body" => $dataRequest
                                    ]
                                );

                                $statusCodeSend = $response->getStatusCode();

                                if ($statusCodeSend == 422) {
                                    // var_dump($response);

                                    return new JsonResponse(['message' => 'Une erreur s\'est produite'], 422);
                                }
                            }
                        } catch (ServerExceptionInterface  $th) {
                            return new JsonResponse(['data' => $th], 400);
                        }

                        $content = $response->toArray();
                        // dd($response->toArray());
                        if ($content["code"] === "201") {

                            $AccountEntityManager = $this->doctrine->getManager('Account');


                            $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 1]);
                            $transaction = new TransactionCompte();
                            $recepteur->setSolde($recepteur->getSolde() - $amount);
                            $transaction->setClientId($clientId);
                            $transaction->setRecepteur($recepteur);
                            $transaction->setEmetteur($recepteur);
                            $transaction->setTransactionId($transactionId);

                            $transaction->setTypeTransaction($typeTransactionD);
                            $transaction->setModePaiement($modeP);
                            $transaction->setMontant($amount);
                            $transaction->setToken($content["data"]["payment_token"]);
                            $transaction->setDescription('Depot Sur le Conpte de Mr ' . $customerName);

                            $AccountEntityManager->persist($transaction);
                            $AccountEntityManager->flush();
                        } else {
                            return new JsonResponse(['data' => 'Une erreur est survenue lors du deroulement de votre operation'], $statusCodeSend);
                        }
                    } else {
                        return new JsonResponse(['message' => 'Solde Insuffisant'], 203);
                    }
                }
            } else {
                return new JsonResponse(['data' => 'Une erreur est survenue lors du deroulement de votre operation'], 400);
            }
        } else {
            return new JsonResponse(['data' => 'Une erreur est survenue lors du deroulement de votre operation'], 400);
        }
    }

    /**
     * @Route("/moneytransaction/notify", name="notifymoneytransaction", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function notifymoneytransaction(Request $request)
    {

        /**
         * Dans cette fonction je verifie l'etat  de la transaction , si celle ci a ete valide , je patch une fois le compte utilisateur concernee
         * 
         * 
         * 
         * 
         * 
         */
        /**
         * request doit contenir  modePaiement,  token transaction
         */
        $data = $request->toArray();
        $licenceManager = $this->doctrine->getManager('Licence');
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $modeP
            =  $AccountEntityManager->getRepository(ModePaiement::class)->findOneBy(['id' => $data['modePaiement']]);
        $siteId = $modeP->getSiteId();
        $data = $request->toArray();
        if (empty($data['token'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete le token de la transaction est requis'
            ], 400);
        }
        $token = $data['token'];


        $apikey = "27139936162a84bbe3f5ad5.24286892";

        #----------- Verification de la transaction -------------------#
        $dataVerif = [
            "apikey" => $apikey,
            "site_id" => $siteId,
            "token" => $token
        ];
        $response = $this->client->request(


            'POST',
            'https://api-checkout.cinetpay.com/v2/payment/check',
            [
                'json' => $dataVerif
            ]
        );

        $statusCode = $response->getStatusCode();
        $content = $response->toArray();

        if ($content["code"]  === "00") {
            $AccountEntityManager = $this->doctrine->getManager('Account');
            $Transaction =
                $AccountEntityManager->getRepository(TransactionCompte::class)->findOneBy(['token' => $token]);
            if ($Transaction) {
                if (!$Transaction->getStatus()) {

                    $montantFinalR = $Transaction->getRecepteur()->getSolde() + $Transaction->getMontant();
                    $Transaction->getRecepteur()->setSolde($montantFinalR);
                    $Transaction->setStatus(1);
                    $AccountEntityManager->persist($Transaction);
                    $AccountEntityManager->flush();
                    $this->em->persist($Transaction);
                    $this->em->flush();
                }
            } else {
                // $paid->setDateUpdated((new \DateTime()));    

            }

            return new JsonResponse($content, $statusCode);
        } else {
            return new JsonResponse($content, $statusCode);
        }
    }



    /**
     * @Route("/transaction/sms", name="transactionSms", methods={"POST"})
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
     */
    public function transactionSms(Request $request)
    {
        /**
         * request doit contenir 
         * typeTransfert : 1=> transfert sms, 2=>transfert d'argent 
         * l'id du client emetteur[emetteurId]
         * l'id du client recepteur[recepteurId]
         * l'id de la route[routeId] si typeTransfert ==1
         * le nombre d'sms a transferer[quantite]
         * 
         */
        $data = $request->toArray();
        if (empty($data['typeTransfert'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete typeTransfert, emetteurId,recepteurId,routeId,quantite sont requis'
            ], 400);
        } else if ($data['typeTransfert'] == 1) {
            // dd($data);
            if (empty($data['emetteurId']) || empty($data['recepteurId']) || empty($data['routeId']) || empty($data['quantite'])) {
                return new JsonResponse([
                    'message' => 'Mauvais parametre de requete emetteurId,recepteurId,routeId,quantite sont requis'
                ], 400);
            }
        } else if ($data['typeTransfert'] == 2) {



            if (empty($data['emetteurId']) || empty($data['recepteurId']) ||  empty($data['quantite'])) {
                return new JsonResponse([
                    'message' => 'Mauvais parametre de requete emetteurId,recepteurId,quantite sont requis'
                ], 400);
            }
        }
        $licenceManager = $this->doctrine->getManager('Licence');
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $routeManager = $this->doctrine->getManager('Route');
        $typeTransfert = $data['typeTransfert'];
        $clientId = $data['clientId'];
        $emetteurId = $data['emetteurId'];
        $recepteurId = $data['recepteurId'];
        if ($data['typeTransfert'] == 1) {
            $routeId = $data['routeId'];
        }
        $quantite = $data['quantite'];
        if ($typeTransfert == 1) {
            $typeCompte = $AccountEntityManager->getRepository(
                TypeCompte::class
            )->findOneBy(['id' => 1]);
            $recepteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $recepteurId, 'typeCompte' => $typeCompte]);
            $emetteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $emetteurId, 'typeCompte' => $typeCompte]);

            $listSMSAcheteEmetteur = null;
            $listSMSAcheteRecepteur = null;
            $idsLisa = [];
            $startSend = false;
            /**
             * reduction des sms de la route concernee de l'emetteur [on reduit dans un solde listsmsachette existant  ]
             */
            $licence =
                /* $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 3]) */ 3;
            $factureE = $licenceManager->getRepository(Facture::class)->findOneBy(['clientId' => $emetteurId, 'licence' => $licence]);


            if ($factureE) {


                $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['routeId' => $routeId, 'facture' => $factureE]);


                if ($lsa) {
                    //  dd($lsa->getId());
                    if ($quantite <= $lsa->getQuantite()) {


                        $lsa->setQuantite(
                            $lsa->getQuantite() - $quantite
                        );
                        $emetteurCompte->setSolde(
                            $emetteurCompte->getSolde() - $quantite
                        );
                        $AccountEntityManager->persist($emetteurCompte);
                        $AccountEntityManager->flush();
                        $licenceManager->persist($lsa);
                        $licenceManager->flush();

                        $listSMSAcheteEmetteur = $lsa;
                    } else {
                        return new JsonResponse([
                            'message' => 'Vous n\'avez pas assez d\'sms pour effectuer cette transaction'
                        ], 203);
                    }
                } else {

                    return new JsonResponse([
                        'message' => 'Vous n\'avez aucune licence sms pour cette route'
                    ], 400);
                }
            } else {

                return new JsonResponse([
                    'message' => 'L\'emetteur n\'avez aucun achat sms pour cette route'
                ], 400);
            }
            /**
             * attribution des sms de la route concernee au recepteur [on cree une listsmsachette ]
             */

            $factureR = $licenceManager->getRepository(Facture::class)->findBy(['clientId' => $recepteurId, 'licence' => $licence]);

            if ($factureR) {


                $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['routeId' => $routeId, 'facture' => $factureR]);

                if ($lsa) {
                    $lsa->setQuantite($lsa->getQuantite() + $quantite);
                    $licenceManager->persist($lsa);
                    $licenceManager->flush();
                    $listSMSAcheteRecepteur = $lsa;
                } else {
                    $licence =
                        /* $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 3]) */ 3;
                    $facture = new Facture();
                    $facture->setLicence($licence);
                    $facture->setMontant($quantite);
                    $facture->setClientId($recepteurId);
                    $listSMSAchete = new ListSMSAchette();
                    $listSMSAchete->setFacture($facture);
                    $listSMSAchete->setRouteId($routeId);
                    $listSMSAchete->setQuantite($quantite);

                    $licenceManager->persist($facture);
                    $licenceManager->persist($listSMSAchete);
                    $licenceManager->flush();
                    $listSMSAcheteRecepteur = $listSMSAchete;
                }
            } else {
                $licence =
                    /* $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 3]) */ 3;
                $facture = new Facture();
                $facture->setLicence($licence);
                $facture->setMontant($quantite);
                $facture->setClientId($recepteurId);
                $listSMSAchete = new ListSMSAchette();
                $listSMSAchete->setFacture($facture);
                $listSMSAchete->setRouteId($routeId);
                $listSMSAchete->setQuantite($quantite);
                $licenceManager->persist(
                    $listSMSAchete
                );
                $licenceManager->persist($facture);
                $licenceManager->flush();
                $listSMSAcheteRecepteur = $listSMSAchete;
            }
            $typeManiPu = $data['typeManiPulation'] ?? 1;

            $typeManiPulation =
                $licenceManager->getRepository(TypeManipulation::class)->findOneBy(['id' => $typeManiPu]);
            $manipulation = new ListSmsManipule();

            $manipulation->setEmetteur($listSMSAcheteEmetteur);
            $manipulation->setRecepteur($listSMSAcheteRecepteur);
            $manipulation->setClientId($clientId);
            $manipulation->setQuantite($quantite);
            $manipulation->setStatus(true);
            $manipulation->setTypemanipulation($typeManiPulation);

            $licenceManager->persist($manipulation);
            $licenceManager->flush();


            $recepteurCompte->setSolde(
                $recepteurCompte->getSolde() + $quantite
            );
            $AccountEntityManager->persist($recepteurCompte);
            $AccountEntityManager->flush();

            return new JsonResponse([
                'message' => 'Transfert d\'sms effectue avec success'
            ], 201);
        } else if ($typeTransfert == 2) {



            $typeCompte = $AccountEntityManager->getRepository(
                TypeCompte::class
            )->findOneBy(['id' => 2]);
            $recepteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $recepteurId, 'typeCompte' => $typeCompte]);
            $emetteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $emetteurId, 'typeCompte' => $typeCompte]);
            $message = '';
            $statusCode = 201;

            if (
                $quantite <=
                $emetteurCompte->getSolde()
            ) {



                $emetteurCompte->setSolde(
                    $emetteurCompte->getSolde() - $quantite
                );
                $recepteurCompte->setSolde(
                    $recepteurCompte->getSolde() + $quantite
                );

                $AccountEntityManager->persist($emetteurCompte);
                $AccountEntityManager->persist($recepteurCompte);
                $AccountEntityManager->flush();
                $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 5]);
                $transaction = new TransactionCompte();
                $transaction->setClientId($emetteurId);
                $transaction->setRecepteur($recepteurCompte);
                $transaction->setEmetteur($emetteurCompte);
                // $transaction->setTransactionId($transactionId);
                $transaction->setTypeTransaction($typeTransactionD);
                $transaction->setMontant($quantite);
                $transaction->setDescription('Transfert Sur le Conpte local');
                $transaction->setStatus(0);
                $AccountEntityManager->persist($transaction);
                $statusCode = 201;
                $message =
                    'Transfert d\'sms effectue avec success';
                $AccountEntityManager->flush();
            } else {
                $statusCode = 203;
                $message = 'Solde Compte inssufisant pour effectuer cette transaction';;
            }
        } else {
            $statusCode = 400;
            $message =
                'Une Erreur est survenur lors de la transaction';
        }




        return new JsonResponse([
            'message' => $message
        ], $statusCode);
    }






    /**
     * @Route("/transaction/locale", name="transactionLocale", methods={"POST"})
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
     */
    public function transactionLocale(Request $request)
    {
        /**
         * request doit contenir 
         *   2=>transfert d'argent pour le solde locale
         * l'id du client emetteur[emetteurId] ,
         */
        $data = $request->toArray();



        if (empty($data['clientId']) || empty($data['montant'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete clientId,montant sont requis'
            ], 400);
        }

        if ($data['montant'] < 500) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete preciser un montant superieur a 500f'
            ], 400);
        }


        $AccountEntityManager = $this->doctrine->getManager('Account');
        $clientId = $data['clientId'];
        $montant = $data['montant'];

        $typeComptePorteF = $AccountEntityManager->getRepository(
            TypeCompte::class
        )->findOneBy(['id' => 3]);
        $typeCompteLocal = $AccountEntityManager->getRepository(
            TypeCompte::class
        )->findOneBy(['id' => 2]);
        $recepteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $clientId, 'typeCompte' => $typeCompteLocal]);
        $emetteurCompte = $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $clientId, 'typeCompte' => $typeComptePorteF]);


        if (
            $montant <=
            $emetteurCompte->getSolde()
        ) {



            $emetteurCompte->setSolde(
                $emetteurCompte->getSolde() - $montant
            );
            $recepteurCompte->setSolde(
                $recepteurCompte->getSolde() + $montant
            );
            $transactionId
                = $this->getUniqueTransactionId();
            $AccountEntityManager->persist($emetteurCompte);
            $AccountEntityManager->persist($recepteurCompte);
            $AccountEntityManager->flush();
            $typeTransactionD = $AccountEntityManager->getRepository(TypeTransaction::class)->findOneBy(['id' => 5]);
            $transaction = new TransactionCompte();
            $transaction->setClientId($clientId);
            $transaction->setRecepteur($recepteurCompte);
            $transaction->setToken(
                $transactionId
            );
            $transaction->setEmetteur($emetteurCompte);
            $transaction->setTransactionId($transactionId);
            $transaction->setTypeTransaction($typeTransactionD);
            $transaction->setMontant($montant);
            $transaction->setDescription('Transfert Sur le Conpte local');
            $transaction->setStatus(0);
            $AccountEntityManager->persist($transaction);
            $statusCode = 201;
            $message =
                'Transfert d\'argent effectue avec success';
            $AccountEntityManager->flush();
        } else {
            $statusCode = 203;
            $message =
                'Solde sms insuffisant pour effectuer la transaction';
        }




        return new JsonResponse([
            'message' => $message
        ], $statusCode);
    }



    /**
     * @Route("/transaction/read", name="transactionRead", methods={"POST"})
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
    public function transactionRead(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $data = $request->toArray();
        $possible = true;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser) {
            $serializer = $this->get('serializer');

            // foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            //     if ($lr->isStatus() && $lr->getFonction()->getId() == '2') {
            //         $possible = true;
            //     }
            // }

            $json = $serializer->serialize($clientUser, 'json');
            if ($possible) {
                $lA = $AccountEntityManager->getRepository(TransactionCompte::class)->findAll();

                $lD = [];
                $lY = [];
                $lT = [];
                $lF = [];

                foreach ($lA  as $tu) {
                    if (
                        $tu->getEmetteur()

                    ) {
                        if (
                            $tu->getEmetteur()->getClientId() == $clientUser->getId()

                        ) {


                            array_push($lT, $tu);
                            if (date_format($tu->getDateCreate(), 'Y-m-d')   == date_format(new \DateTime(), 'Y-m-d')) {
                                array_push($lD, $tu);
                            } else if (date_format($tu->getDateCreate(), 'Y-m-d')   == date_format(new \DateTime('yesterday'), 'Y-m-d')) {
                                array_push($lY, $tu);
                            }
                        }
                    } else  if (
                        $tu->getRecepteur()
                    ) {


                        if (
                            $tu->getRecepteur()->getClientId() == $clientUser->getId()
                        ) {
                            array_push($lT, $tu);
                            if (date_format($tu->getDateCreate(), 'Y-m-d')   == date_format(new \DateTime(), 'Y-m-d')) {
                                array_push($lD, $tu);
                            } else if (date_format($tu->getDateCreate(), 'Y-m-d')   == date_format(new \DateTime('yesterday'), 'Y-m-d')) {
                                array_push($lY, $tu);
                            }
                        }
                    }
                }

                // $lF = [
                //     '0' => $lY,
                //     '1' => $lD,
                //     '2' => $lT,
                // ];

                return
                    new JsonResponse([

                        'yesterday'
                        =>
                        JSON_DECODE($serializer->serialize(array_reverse($lY), 'json')), 'toDay'
                        =>
                        JSON_DECODE($serializer->serialize(array_reverse($lD), 'json')), 'all'
                        =>
                        JSON_DECODE($serializer->serialize(array_reverse($lT), 'json')),

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
                'message' => 'Client introuvable'
            ], 400);
        }
    }
    /**
     * @Route("/manipulation/read", name="manipulationRead", methods={"POST"})
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
    public function manipulationRead(Request $request)
    {
        $LicenceEntityManager = $this->doctrine->getManager('Licence');
        $data = $request->toArray();
        $possible = true;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser) {
            $serializer = $this->get('serializer');

            // foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            //     if ($lr->isStatus() && $lr->getFonction()->getId() == '2') {
            //         $possible = true;
            //     }
            // }

            $json = $serializer->serialize($clientUser, 'json');
            if ($possible) {
                $lM = $LicenceEntityManager->getRepository(ListSmsManipule::class)->findBy(['clientId' => $clientUser->getId(), 'status' => true]);

                $lD = [];
                $lY = [];
                $lT = [];
                $lF = [];

                foreach ($lM  as $tu) {
                    if (
                        $tu->getEmetteur() &&   $tu->getRecepteur()

                    ) {

                        $emetteur = $this->getUserInfo($tu->getEmetteur()->getFacture()->getClientId());
                        $recepteur = $this->getUserInfo($tu->getRecepteur()->getFacture()->getClientId());
                        $route = $this->getRouteInfo($tu->getEmetteur()->getRouteId());
                        $quantite = $tu->getQuantite();
                        $date = $tu->getDateCreated()  ? $tu->getDateCreated() : new DateTime();

                        if (
                            $emetteur && $recepteur
                            &&  $route && $quantite && $date && $tu->getEmetteur()->getRouteId() == $tu->getRecepteur()->getRouteId()
                        ) {
                            $instence = [
                                'emetteur' => $emetteur->getNom(),
                                'recepteur' => $recepteur->getNom(),
                                'route' => $route->getNom(),
                                'quantite' => $quantite,
                                'date' => $date->format('d/m/y'),
                                'typemanipulation' => $tu->getTypemanipulation()->getDescription()
                            ];

                            array_push($lT,   $instence);
                        }
                    }
                }


                return
                    new JsonResponse([

                        'data'
                        =>
                        JSON_DECODE($serializer->serialize(array_reverse($lT), 'json')),

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
                'message' => 'Client introuvable'
            ], 400);
        }
    }
    public function getRouteInfo($id)
    {
        $RouteEntityManager = $this->doctrine->getManager('Route');

        $route = $RouteEntityManager->getRepository(EntityRouteRoute::class)->findOneBy(['id' => $id]);
        return $route;
    }
    public function getUserInfo($id)
    {
        $user = $this->em->getRepository(Client::class)->findOneBy(['id' => $id]);
        return $user;
    }
    public function getLSAInfo($id)
    {
        $LicenceEntityManager = $this->doctrine->getManager('Licence');

        $lsa = $$LicenceEntityManager->getRepository(ListSMSAchette::class)->findOneBy(['id' => $id]);
        return $lsa;
    }




    /**
     * @Route("/retrait", name="retrait", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function retrait(Request $request)
    {




        $apikey = "27139936162a84bbe3f5ad5.24286892";


        try {
            $data
                = [
                    "apikey" => '27139936162a84bbe3f5ad5.24286892',
                    "password" => '/Or@nge2014*',

                ];

            $responseLogin = $this->client->request(
                'POST',
                'https://client.cinetpay.com/v1/auth/login',
                [
                    "body" =>
                    $data,
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                ],
            );
            $statusCode = $responseLogin->getStatusCode();

            $dataLogin =   $responseLogin->toArray();

            $contact
                =
                [

                    "data"
                    =>    json_encode(
                        [
                            [
                                "prefix" => "237",
                                "phone" => 690863838,
                                "name" =>  "Mouafo",
                                "surname" => "Randoll",
                                "email" => 'hari.randoll@gmail.com'
                            ]
                        ]
                    )
                ];

            $responseAddContact = $this->client->request(
                'POST',
                'https://client.cinetpay.com/v1/transfer/contact?token='
                    . $dataLogin['data']['token'],
                [
                    'body' =>
                    $contact,
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],

                ]
            );

            // dd($contact);

            if ($responseAddContact->toArray()['code'] == '0') {


                $dataRequest
                    =   [

                        "data"
                        => json_encode(

                            [
                                "amount" => 500,
                                "phone" => 690863838,
                                "client_transaction_id" => "TEST1",
                                "prefix" => 237,
                                "notify_url" =>  "https://dashboard.pubx.cm/test"
                            ]

                        )
                    ];
                // dd($dataRequest);
                // $response = $this->client->request(
                //     'GET',
                //     'https://client.cinetpay.com/v1/transfer/check/balance?token=' . $dataLogin['data']['token'],

                //     [
                //         'body' =>
                //         $dataRequest,
                //         'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],

                //         // "body" => $dataRequest
                //     ]
                // );

                $response = $this->client->request(
                    'POST',
                    'https://client.cinetpay.com/v1/transfer/money/send/contact?token=' . $dataLogin['data']['token'] . '&lang=fr'/* .'&transaction_id=X0988' */,

                    [
                        'body' =>
                        $dataRequest,
                        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],

                        // "body" => $dataRequest
                    ]
                );
                //    var_dump($response->toArray());
                $statusCodeSend = $response->getStatusCode();

                if ($statusCodeSend == 422) {
                    // var_dump($response);

                    return new JsonResponse(['message' => 'Une erreur s\'est produite', 'data' => $response->toArray()], 422);
                } else {
                    return new JsonResponse(['data' => $response->toArray()], 201);
                }
            }
        } catch (ServerExceptionInterface  $th) {
            return new JsonResponse(['data' => $th], 400);
        }

        // $content = $response->toArray();
        // // dd($response->toArray());
        // if ($content["code"] === "201") {
        // } else {
        //     return new JsonResponse(['data' => 'Une erreur est survenue lors du deroulement de votre operation'], $statusCodeSend);
        // }
    }
}
