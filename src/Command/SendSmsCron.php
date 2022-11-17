<?php

// src/Command/SendSmsCron.php
namespace App\Command;

use App\Entity\Account\Compte;
use App\Entity\Account\TypeCompte;
use App\Entity\Auth\Client;
use App\Entity\Bulk\Contact;
use App\Entity\Bulk\Exception as BulkException;
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListLotCalendrier;
use App\Entity\Bulk\ListSenderIdExcepte;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Bulk\Sms;
use App\Entity\Licence\Facture;
use App\Entity\Licence\Licence;
use App\Entity\Licence\ListSMSAchette;
use App\Entity\Route\Operateur;
use App\Entity\Route\Route;
use App\Entity\Route\SenderApi;
use App\Entity\User\ListProjetClient;
use App\Entity\User\Projet;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

use Proxies\__CG__\App\Entity\Pub\Publication;

class SendSmsCron extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:sendsmscron';
    private $clientWeb;
    public    $doctrine;
    public function __construct(HttpClientInterface $clientWeb,  ManagerRegistry $doctrine)
    {
        $this->clientWeb = $clientWeb;
        $this->doctrine = $doctrine;
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->setDescription('Manage sending sms')
            ->setHelp('This command allows you to manage how the serveur do to send sms');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->SendSmsBuyLot($io);

        $io->success(sprintf('Operation finished.'));
        return Command::SUCCESS;
    }

    public function getUniqueKwy()
    {
        $PubEntityManager = $this->doctrine->getManager('Pub');

        $chaine = '';
        $listeCar = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = mb_strlen($listeCar, '8bit') - 1;
        for ($i = 0; $i < 5; ++$i) {
            $chaine .= $listeCar[random_int(0, $max)];
        }
        $AllPun = $PubEntityManager->getRepository(Publication::class)->findAll();
        $ExistTransaction = $PubEntityManager->getRepository(Publication::class)->findOneBy(['clef' => $chaine]);
        if ($ExistTransaction) {
            return
                $this->getUniqueKwy();
        } else {
            return $chaine .  strval((count($AllPun) + 1));
        }
    }

    /**
     * 
     */
    public function getMediaTiq($idSms)
    {
        $BulkEntityManager = $this->doctrine->getManager('Bulk');
        $PubEntityManager = $this->doctrine->getManager('Pub');
        $sms =
            $BulkEntityManager->getRepository(Sms::class)->findOneBy(['id' => $idSms]);
        if ($sms) {
            $clientId =
                $sms->getClientId();
            $pub =
                $PubEntityManager->getRepository(Publication::class)->findOneBy(['sms' => $idSms]);

            if ($sms && $pub) {
                $clef = $this->getUniqueKwy();
                $pub->setClef($clef);
                $PubEntityManager->persist($pub);
                $PubEntityManager->flush();

                $messageF
                    =
                    str_replace('pubx.cm/xxxxxx', 'pubx.cm/'

                        . $clef, $sms->getMessage());
                // $sms->setMessage($messageF );
                // $BulkEntityManager->persist($sms);
                // $BulkEntityManager->flush();
                // dd($messageF);
            } else {
                $messageF =
                    $sms->getMessage();
            }
        } else {
            $messageF = '';
        }
        // dd($messageF);
        return   $messageF;
    }

    public function SendSmsBuyLot(SymfonyStyle $io)
    {
        $tabeSmsAndDest = array();

        $dest = array();
        $contacts = [];
        $listsmscontact = [];
        $listClientId = [];
        $listSmsLotsEnvoye = [];
        $bulkManager = $this->doctrine->getManager('Bulk');
        $routeManager = $this->doctrine->getManager('Route');
        $userManager = $this->doctrine->getManager('User');
        $licenceManager = $this->doctrine->getManager('Licence');
        $findlistSmsLotsEnvoye = $bulkManager->getRepository(ListSmsLotsEnvoye::class);
        $findlistLotsCalendrier = $bulkManager->getRepository(ListLotCalendrier::class);
        $findlistSmsContact = $bulkManager->getRepository(ListSmsContact::class);
        $findContact = $bulkManager->getRepository(Contact::class);
        $findLot = $bulkManager->getRepository(Lot::class);
        $findSms = $bulkManager->getRepository(Sms::class);
        $i = 0;
        $tableFinal = [];
        $lContExcep = [];
        $statusSpecial = true;
        $listDestinataire = [];
        $listSmsLotsEnvoye  = []; // = $findlistSmsLotsEnvoye->findAll();
        $listSmsLotsEnvoye  = $findlistSmsLotsEnvoye->findAll();
        $lExceptionText = [];

        $lExceptioGn = $bulkManager->getRepository(BulkException::class)->findAll();
        foreach ($lExceptioGn as $eText) {
            if ($eText->getText() && $eText->isStatus()) {
                array_push($lExceptionText, strtoupper($eText->getText()));
            }
        }
        foreach ($listSmsLotsEnvoye as $listSmsLote) {

            if ($listSmsLote->getStatus() !== true) {
                $verififInllc = $findlistLotsCalendrier->findOneBy(['lot' => $listSmsLote->getLot()]);
                if ($verififInllc) {
                    //    var_dump(date_format($verififInllc->getCalendrier()->getDateProgramme(), 'Y-m-d H'));
                    // var_dump(date_format(new \DateTime(), 'Y-m-d H'));
                    if (
                       $verififInllc->getCalendrier()->getStatus()
                    ) {
                        if (
                            date_format($verififInllc->getCalendrier()->getDateProgramme(), 'Y-m-d H')   == date_format(new \DateTime(), 'Y-m-d H')
                        ) {
                        if ($listSmsLote->getSms()) {
                            if ($listSmsLote->getSms()->getProjetId() != null) {
                                $projet =
                                    $userManager->getRepository(Projet::class)->findOneBy(['id' => $listSmsLote->getSms()->getProjetId()]);
                                $lpc = $userManager->getRepository(ListProjetClient::class)->findOneBy(['projet' => $projet]);

                                $clientId =
                                    $lpc->getClientId();
                            } else {
                                $clientId =
                                    $listSmsLote->getSms()->getClientId();
                            }
                            $statusSpecial
                                = $listSmsLote->getSms()->isStatusSpecial() ?? true;
                        }
                    } else {
                        //  var_dump('cxc');
                        $clientId =
                            0;
                    }}
                } else {
                    if ($listSmsLote->getSms()) {
                        if ($listSmsLote->getSms()->getProjetId() != null) {
                            $projet =
                                $userManager->getRepository(Projet::class)->findOneBy(['id' => $listSmsLote->getSms()->getProjetId()]);
                            $lpc = $userManager->getRepository(ListProjetClient::class)->findOneBy(['projet' => $projet]);

                            $clientId =
                                $lpc->getClientId();
                        } else {
                            $clientId =
                                $listSmsLote->getSms()->getClientId();
                        }
                        $statusSpecial
                            = $listSmsLote->getSms()->isStatusSpecial() ?? true;
                    }
                }

                if ($clientId != 0) {

                    $listSmsLoteId
                        = $listSmsLote->getId();

                    if ($listSmsLoteId) {

                        if ($listSmsLote->getSms()) {
                            $message
                                = $this->getMediaTiq($listSmsLote->getSms()->getId());
                            if ($listSmsLote->getSms()->getSenderId()) {
                                $senderId =
                                    $listSmsLote->getSms()->getSenderId()->getSenderId();
                                // Message d'erreur à sauvegardez
                                $lse =    $bulkManager->getRepository(ListSenderIdExcepte::class)->findBy(['senderId' => $senderId]);
                                if ($lse) {

                                    foreach ($lse as $l) {

                                        array_push($lContExcep, $l->getException()->getCodePhone() . $l->getException()->getContact());
                                    }
                                }
                                $routeId = $bulkManager->getRepository(RouteListSmsLotsEnvoye::class)->findOneBy(['listSmsLotsEnvoye' => $listSmsLote->getId()])->getRouteId();
                                $route = $routeManager->getRepository(Route::class)->findOneBy(['id' => $routeId]);

                                $listDestinataires = [];

                                if (empty($listSmsLote->getGroupeContact())) {
                                    $lotContact = $bulkManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $listSmsLote->getId()]);
                                    foreach ($lotContact as $lt) {
                                        if (!in_array($route->getPays()->getCodePhone() . $lt->getContact()->getPhone(), $lContExcep)) {
                                            $listDestinataires[] = $lt->getContact()->getPhone();
                                        }
                                    }
                                    // recuperation des contact a partir de ListSmsContact
                                } else {
                                    $lotGroupe = $bulkManager->getRepository(ListGroupeContact::class)->findBy(['groupeContact' => $listSmsLote->getGroupeContact()->getId()]);

                                    foreach ($lotGroupe as $lg) {
                                        if (!in_array($route->getPays()->getCodePhone() . $lg->getContact()->getPhone(), $lContExcep)) {

                                            $listDestinataires[] = $lg->getContact()->getPhone();
                                        }
                                    }
                                    // recuperation des contact a partir du groupe de contact


                                }


                                $statusSpecial
                                    = $listSmsLote->getSms()->isStatusSpecial() ?? true;
                                array_push($listClientId, $clientId);
                                // var_dump($message,count($tabeSmsAndDest));
                                array_push($tabeSmsAndDest, [$clientId, $message, $listDestinataires, $listSmsLote, $routeId, $senderId]);
                            }
                        }
                    }
                }
            }
        }
        if (!empty($tabeSmsAndDest)) {

            $total = 0;
            $numBClient = 0;
            $moyenneSend = 0;
            $baeId = null;

            for ($i = 0; $i < count($tabeSmsAndDest); $i++) {
                $total += count($tabeSmsAndDest[$i][2]);
                // //var_dump($tabeSmsAndDest[$i][0]);
                if ($tabeSmsAndDest[$i][0] !== $baeId) {
                    $numBClient++;
                    // //var_dump($numBClient);
                }
                $baeId = $tabeSmsAndDest[$i][0];
            }

            $moyenneSend =  $total / $numBClient;
            // //var_dump("moyenneSend  ".
            // $moyenneSend. '  total '. $total);

            $listDestinataire = array();



            for ($i = 0; $i < count($tabeSmsAndDest); $i++) {

                if (!empty($tabeSmsAndDest[$i][2])) {

                    $messageF = $tabeSmsAndDest[$i][1];
                    $senderId = $tabeSmsAndDest[$i][5];
                    $desty = [];
                    //  //var_dump(count($tabeSmsAndDest[$i][2]));
                    for ($j = 0; $j < count($tabeSmsAndDest[$i][2]); $j++) {
                        //  //var_dump($tabeSmsAndDest[$i]);
                        if (count($desty) < $moyenneSend) {
                            array_push($desty, $tabeSmsAndDest[$i][2][$j]);
                        }
                    }



                    $lotUse = $tabeSmsAndDest[$i][3];
                    $routeId = $tabeSmsAndDest[$i][4];
                    array_push($listDestinataire, [$desty, $clientId, $lotUse, $routeId, $messageF]);

                    // $this->send([$senderId, $message,  $listDestinataire]);


                    // array_push($tableFinal, [$senderId, $message, $listDestinataire, $routeId]);

                    // $listlotsmsconcerne =   $findlistSmsLotsEnvoye->findOneBy(['id' => $tabeSmsAndDest[$i][3]])->setStatus(true);

                    // $customerEntityManager->persist($listlotsmsconcerne);
                    // $customerEntityManager->flush();
                } else {
                }


                // //var_dump("changement du status du lot d'id" . $tabeSmsAndDest[$i][3]);
            }



            // if ($tabeSmsAndDest[$i][3] == 1) {

            //     $data = [
            //             'senderId' =>
            //             $senderId,
            //             'message' => $message,
            //             'destinataire' => $listDestinataire
            //         ];

            //     // $this->sendToCamerounApi($data);
            // }

        } else {
            //var_dump("tout a deja ete send");
        }


        for (
            $i = 0;
            $i < count($listDestinataire);
            $i++
        ) {


            array_push($tableFinal, [

                $senderId,
                $listDestinataire[$i][4], $listDestinataire[$i][0],
                $listDestinataire[$i][1], $listDestinataire[$i][2], $listDestinataire[$i][3],
            ]);
        }




        for ($i = 0; $i < count($tableFinal); $i++) {
            $noexceptionText = true;


            $qt = 0;


            $listFacture = $licenceManager->getRepository(Facture::class)->findBy([
                'clientId' => $listClientId[$i], 'licence' => 3
            ]);
            if (!$listFacture) {
                return;
            }
            $listLsa = [];
            foreach ($listFacture as $lf) {

                $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['facture' => $lf->getId(), 'routeId' => $tableFinal[$i][5]]);

                if ($lsa != null) {
                    // //var_dump('client ' . $listClientId[$i] . '  voici ca********' . $tableFinal[$i][5]);

                    array_push($listLsa, $lsa->getId());
                    if (!$lsa) {
                        continue;
                    } else {
                        $qt += $lsa->getQuantite();
                    }
                }
            }


            //  var_dump(  ($tableFinal[$i][1]));



            foreach (explode(' ', $tableFinal[$i][1])  as  $me) {


                if (in_array(strtoupper($me), $lExceptionText)) {

                    $noexceptionText = false;
                }
            }
            // var_dump($noexceptionText);
            if ($noexceptionText) {
                $data = [
                    'senderId' =>
                    $tableFinal[$i][0],
                    'message' => $tableFinal[$i][1],
                    'destinataire' => $tableFinal[$i][2],
                    'listSMSAchetteId' => $listLsa,
                    'listSmsLot' => $tableFinal[$i][4],
                    'statusSpecial' => $statusSpecial ?? true
                ];
                // var_dump($data);
                // Chemin du fichier log où les erreurs doivent être sauvegardées
                // $logFile = "/var/www/api.devoo.gessiia.com/error5s.log";

                // Enregistrement du message d'erreur dans le fichier log
                // error_log("voici".$qt ."*****". "count"  .count($tableFinal[$i][2]) * count(str_split($tableFinal[$i][1])), 3, $logFile);

                if ($qt >= count($tableFinal[$i][2]) * (count(str_split($tableFinal[$i][1])) / ($statusSpecial ? 70  : 159))) {



                    if ($tableFinal[$i][5] == '1') {

                        $this->sendToCamerounApi($data);
                    } else if ($tableFinal[$i][5] == '2') {

                        $this->sendToGuineeEApi($data);
                    }
                }
            }
        }
        // //var_dump('voici ca********' . $lf->getId() . 'dsfgh' . $qt);
    }

    /**
     * Undocumented function
     * @param [array] $request doit contenir le senderId, le message , la liste des destinataire
     * @return void
     */

    public function sendToCamerounApi($request)
    {

        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request;

        $statusSpecial  = $data['statusSpecial'];
        $bulkManager = $this->doctrine->getManager('Bulk');
        $licenceManager = $this->doctrine->getManager('Licence');

        $numberByOperator = array();
        $customerEntityManager = $this->doctrine->getManager('Route');
        foreach ($data['destinataire'] as $dest) {

            $NumAndApi = array();
            if (
                strlen($dest) == 9
            ) {
                if (str_split($dest)[0] . str_split($dest)[1] === "65") {

                    if (
                        str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "651"
                    ) {

                        $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
                        $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                        $NumAndApi
                            = [$dest, $apiLink->getId()];
                        array_push($numberByOperator,  $NumAndApi);
                    }
                    if (
                        str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "652"
                    ) {

                        $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
                        $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                        $NumAndApi
                            = [$dest, $apiLink->getId()];
                        array_push($numberByOperator,  $NumAndApi);
                    }
                    if (
                        str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "653"
                    ) {

                        $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
                        $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                        $NumAndApi
                            = [$dest, $apiLink->getId()];
                        array_push($numberByOperator,  $NumAndApi);
                    }
                    if (
                        str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "654"
                    ) {

                        $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
                        $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                        $NumAndApi
                            = [$dest, $apiLink->getId()];
                        array_push($numberByOperator,  $NumAndApi);
                    }
                    if (
                        str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "654"
                    ) {

                        $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
                        $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                        $NumAndApi
                            = [$dest, $apiLink->getId()];
                        array_push($numberByOperator,  $NumAndApi);
                    } else {
                        $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
                        $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                        $NumAndApi
                            = [$dest, $apiLink->getId()];
                        array_push($numberByOperator,  $NumAndApi);
                    }
                } else if (
                    str_split($dest)[0] . str_split($dest)[1] === "69"
                ) {
                    $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
                    $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);

                    $NumAndApi
                        = [
                            $dest, $apiLink->getId()
                        ];
                    array_push($numberByOperator,  $NumAndApi);
                } else if (
                    str_split($dest)[0] . str_split($dest)[1] === "67"
                ) {
                    $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
                    $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                    $NumAndApi
                        = [$dest, $apiLink->getId()];
                    array_push($numberByOperator,  $NumAndApi);
                } else if (
                    str_split($dest)[0] . str_split($dest)[1] === "68"
                ) {
                    $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
                    $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                    $NumAndApi
                        = [$dest, $apiLink->getId()];
                    array_push($numberByOperator,  $NumAndApi);
                } else if (
                    str_split($dest)[0] . str_split($dest)[1] === "66"
                ) {
                    $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
                    $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                    $NumAndApi
                        = [$dest, $apiLink->getId()];
                    array_push($numberByOperator, $NumAndApi);
                } else {
                    $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 6]);
                    $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
                    if ($apiLink !== null) {
                        $NumAndApi
                            = [$dest, $apiLink->getId()];
                        array_push(
                            $numberByOperator,
                            $NumAndApi
                        );
                    }
                }

                array_push(
                    $destinatireFinal,
                    $NumAndApi
                );
            } else {
                array_push(
                    $destinatireError,
                    ['incorrectNUmber', $dest]
                );
            }
        }


        //   var_dump($destinatireFinal);
        foreach ($destinatireFinal as $desta => $desti) {
            $listSmsContact = null;
            // if (preg_match(
            //     "/^[a-zA-Z0-9. ,?Â'ùçàéëèà;@!:_-]*$/",
            //     $data['message']
            // )) {
            //    dd(" is a valid message");
            $contact = $bulkManager->getRepository(Contact::class)->findOneBy(['phone' => $desti[0]]);
            $listSmsContactF = $bulkManager->getRepository(ListSmsContact::class)->findOneBy(['listSmsLotsEnvoye' => $data['listSmsLot'], 'contact' =>  $contact]);

            var_dump($contact->getId());
            // //var_dump($data['listSmsLot'])


            if ($listSmsContactF) {
                $listSmsContact
                    = $listSmsContactF;
            } else {
                $newLSC = new ListSmsContact();
                // dd($data);
                $newLSC->setContact($contact);
                $newLSC->setListSmsLotsEnvoye($data['listSmsLot']);

                $bulkManager->persist($newLSC);
                $bulkManager->flush();

                $listSmsContact
                    =      $newLSC;
            }
            if ($listSmsContact != null && $listSmsContact->getStatus() != true) {


                try {

                    // dd($data);
                    $query = [
                        // 'login' =>  679170000,   //$client->getPhone(),
                        // 'password' => "Oi7i469x", //$client->getPassword(),


                        'login' =>  690863838,   //$client->getPhone(),
                        'password' => "12345678", //$client->getPassword(),
                        'sender_id' => $data['senderId'],
                        'destinataire' => $desti[0],
                        'message' => $data['message']
                    ];
                    var_dump($query);
                    // $query = [
                    //     'login' =>  679170000,   //$client->getPhone(),
                    //     'password' => "Oi7i469x", //$client->getPassword(),
                    //     'sender_id' => $data['senderId'],
                    //     'destinataire' => 690863838,
                    //     'message' => $data['message']
                    // ];


                    $response = $this->clientWeb->request(
                        'GET',
                        'http://sms.gessiia.com/ss/api.php',
                        [
                            'query' => $query
                        ]
                    );

                    $statusCode =
                        $response->getStatusCode();
                    $responseApi =  $response->getContent();
                    // $statusCode =200;
                    // $responseApi = 'hgghhg';
                    var_dump($responseApi);
                    if ($statusCode == 200 && !empty($responseApi) && trim($responseApi) != 'Solde insuffisant' && $responseApi != 'Solde insuffisant' && $responseApi != 'Mot de passe incorrect'   && $this->verifCorrectRes($responseApi)) {

                        $qt = 0;
                        $AccountEntityManager = $this->doctrine->getManager('Account');
                        for ($j = 0; $j < count($data['listSMSAchetteId']); $j++) {
                            $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['id' => $data['listSMSAchetteId'][$j]]);

                            $typeCompteSms = $AccountEntityManager->getRepository(
                                TypeCompte::class
                            )->findOneBy(['id' => 1]);

                            // dd($lsa->getFacture()->getClientId());
                            $compteSms =
                                $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $lsa->getFacture()->getClientId(), 'typeCompte' =>   $typeCompteSms]);


                            if ($lsa->getQuantite() > 0) {

                                $compteSms->setSolde($compteSms->getSolde() - ceil(count(str_split($data['message'])) / (($statusSpecial ? 70  :  159))));
                                $AccountEntityManager->persist($compteSms);

                                $AccountEntityManager->flush();
                                $listSMSAchette = $lsa->setQuantite($lsa->getQuantite() - ceil(count(str_split($data['message'])) / ($statusSpecial ? 70  : 159)));
                                $licenceManager->persist($listSMSAchette);
                                $licenceManager->flush();

                                break;
                            } else {
                                continue;
                            }
                        }

                        $lsupLsc =  $listSmsContact->setStatus(true);
                        $lsupLsc =  $listSmsContact->setResponseApi(trim($responseApi));
                        $sms = $listSmsContact->getListSmsLotsEnvoye()->getSms();
                        $sms->setMessage($data['message']);
                        $bulkManager->persist($sms);
                        $bulkManager->persist($lsupLsc);
                        $bulkManager->flush();

                        // dd($listSmsContact->getId());
                    }
                    array_push($dataSucess, [(200 == 200) ?  'success' : 'error', $desti[0]]);
                } catch (Exception $e) {

                    return   new JsonResponse([
                        'success' => false,
                        'message' => $e,
                    ], 400);
                }
            }
        }

        //var_dump($dataSucess);
        return  new JsonResponse(
            [

                'reponse' => 'Traitement effectue',
                'messageSend' => $data['message'],
                'success'
                => $dataSucess,
                'error'
                => $destinatireError,

            ],
            200
        );
    }




    /**
     * Undocumented function
     * @param [array] $request doit contenir le senderId, le message , la liste des destinataire
     * @return void
     */

    public function sendToGuineeEApi($request)
    {
        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request;
        $bulkManager = $this->doctrine->getManager('Bulk');
        $licenceManager = $this->doctrine->getManager('Licence');

        $numberByOperator = array();
        $routeManager = $this->doctrine->getManager('Route');
        // //var_dump('**********************');
        foreach ($data['destinataire'] as $desta => $desti) {




            $contact = $bulkManager->getRepository(Contact::class)->findOneBy(['phone' => $desti]);
            $listSmsContact = $bulkManager->getRepository(ListSmsContact::class)->findOneBy(['listSmsLotsEnvoye' => $data['listSmsLot'], 'contact' =>  $contact]);

            if ($listSmsContact  && $listSmsContact->getStatus() != true) {



                try {
                    $routeId = $bulkManager->getRepository(RouteListSmsLotsEnvoye::class)->findOneBy(['listSmsLotsEnvoye' => $data['listSmsLot']])->getRouteId();

                    $codePays = $routeManager->getRepository(Route::class)->findOneBy(['id' => $routeId])->getPays()->getCodePhone();
                    // dd($codePays);
                    $query = [
                        /*  'login' =>  'DEVOO',
                       
                        */
                        'password' => "D3V@0!10",
                        'sender' => $data['senderId'],
                        'phone' =>  '237' . $desti,
                        'api_key' => '1yzBBKel2f6q2Me',
                        'message' => $data['message'],
                        'flag' => strlen($data['message']) > 160 ? 'long_sms' : 'short_sms'
                    ];
                    // dd($query);
                    $response = $this->clientWeb->request(
                        'POST',
                        'https://app.lmtgroup.com/bulksms/api/v1/push',
                        [
                            'headers' => [
                                'Content-type' => 'application/x-www-form-urlencoded',
                            ],
                            'body' => $query
                        ]
                    );
                    $statusCode = $response->getStatusCode();
                    // dd($response->getContent());
                    $responseApi = json_decode($response->getContent(), true)['message'];
                    if ($statusCode == 200 && json_decode($response->getContent(), true)['status'] == 'success') {
                        $qt = 0;
                        $AccountEntityManager = $this->doctrine->getManager('Account');
                        for ($j = 0; $j < count($data['listSMSAchetteId']); $j++) {
                            $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['id' => $data['listSMSAchetteId'][$j]]);
                            // dd($lsa->getId());
                            $typeCompteSms = $AccountEntityManager->getRepository(
                                TypeCompte::class
                            )->findOneBy(['id' => 1]);

                            // dd($lsa->getFacture()->getClientId());
                            $compteSms =
                                $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $lsa->getFacture()->getClientId(), 'typeCompte' =>   $typeCompteSms]);
                            if ($lsa->getQuantite() > 0) {
                                $compteSms->setSolde($compteSms->getSolde() - 1);
                                $AccountEntityManager->persist($compteSms);
                                $AccountEntityManager->flush();
                                $listSMSAchette = $lsa->setQuantite($lsa->getQuantite() - 1);
                                $licenceManager->persist($listSMSAchette);
                                $licenceManager->flush();
                                break;
                            } else {
                                continue;
                            }
                        }

                        $lsupLsc =  $listSmsContact->setStatus(true);
                        $lsupLsc =  $listSmsContact->setResponseApi($responseApi);

                        $bulkManager->persist($lsupLsc);
                        $bulkManager->flush();
                    }
                    array_push($dataSucess, [($statusCode == 200) ?  'success' : 'error', $desti[0]]);
                } catch (Exception $e) {

                    return   new JsonResponse([
                        'success' => false,
                        'message' => $e,
                    ], 400);
                }
            }
        }

        //var_dump($dataSucess);
        return  new JsonResponse(
            [

                'reponse' => 'Traitement effectue',
                'messageSend' => $data['message'],
                'success'
                => $dataSucess,
                'error'
                => $destinatireError,

            ],
            200
        );
    }














    public   function verifCorrectRes($datas)
    {
        return ((intval(trim($datas)) != 0) ? true : false);
    }




    /**
     * Undocumented function
     * @param [array] $data doit contenir le senderId, le message , la liste des destinataire
     * @return void
     */
    public function send($data)
    {
        //var_dump("voici les data de send.........");
        //var_dump($data);
        $formData = new FormDataPart([
            ['array_field' => 'some value'],
            ['array_field' => 'other value'],
        ]);
        $formData->getParts();
        //var_dump($data);
        $response = $this->clientWeb->request(
            'POST',
            "http://127.0.0.1:8000/api/auth",
            [
                'body' => [
                    "phone" => "690863838",
                    "password" => "00000"
                ]
            ]
        );
    }
}
