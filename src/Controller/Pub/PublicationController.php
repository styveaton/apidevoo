<?php

namespace App\Controller\Pub;

use App\Entity\Account\Compte;
use App\Entity\Account\TypeCompte;
use App\Entity\Auth\Client;
use App\Entity\Auth\ListRoleFonctions;
use App\Entity\Licence\ListSMSAchette;
use App\Entity\Route\Route as RouteRoute;
use App\Entity\User\ListProjetClient;
use App\Entity\User\Projet;
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
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListLotCalendrier;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Bulk\SenderId;
use App\Entity\Bulk\Sms;
use App\Entity\Pub\CategoryPub;
use App\Entity\Pub\Publication;
use App\Entity\Route\Operateur;
use App\Entity\Route\Pays;
use App\Entity\Route\SenderApi;
use App\Entity\Vitrine\TypeVitrine;
use App\Entity\Vitrine\Vitrine;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class PublicationController  extends AbstractController
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
     * @Route("/publications/read", name="publicationRead", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur la cle de la publication
     * 
     */
    public function publicationRead(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $PubEntityManager = $this->doctrine->getManager('Pub');
        $data = $request->toArray();
        $listPubFinal = [];
        $Final = [];
        $publications = $PubEntityManager->getRepository(Publication::class)->findAll();
        $serializer = $this->get('serializer');
        if (empty($data['clef'])) {
            foreach ($publications  as $pub) {
                array_push($listPubFinal, [
                    'id' => $pub->getId(),
                    'title' => $pub->getTitle(),
                    'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                    'description' => $pub->getDescription(),

                ]);
            }
        } else {


            $publication = $PubEntityManager->getRepository(Publication::class)->findOneBy(['clef' => $data['clef']]);

            $possible = true;

            if ($publication) {
                array_push($listPubFinal, [
                    'id' => $publication->getId(),
                    'title' => $publication->getTitle(),
                    'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $publication->getPublicationObject()->getFilePathId(),
                    'description' => $publication->getDescription(),

                ]);
            }
            $publications = $PubEntityManager->getRepository(Publication::class)->findAll();
            foreach ($publications  as $pub) {
                array_push($listPubFinal, [
                    'id' => $pub->getId(),
                    'title' => $pub->getTitle(),

                    'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                    'description' => $pub->getDescription(),

                ]);
            }
        }
        $Final = $serializer->serialize(array_reverse($listPubFinal), 'json');
        return
            new JsonResponse([
                'data'
                =>
                JSON_DECODE($Final),


            ], 201);
    }
    /**
     * @Route("/category/read", name="categoryRead", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur la keySecret du client
     * 
     */
    public function categoryRead(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $PubEntityManager = $this->doctrine->getManager('Pub');
        $BulkEntityManager = $this->doctrine->getManager('Bulk');
        // $AuthEntityManager = $this->doctrine->getManager('Auth');
        $data = $request->toArray();
        $listCatFinal = [];
        $listCatThisDay =  [];
        $listCatThisWeek =  [];
        $listCatThisMonth = [];
        $listCatThisYear = [];
        $saveDay = [];

        $saveMonth = [];
        $saveCat = [];
        $saveYear = [];
        $possible = false;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

        $Final = [];
        $category = $PubEntityManager->getRepository(CategoryPub::class)->findAll();
        $serializer = $this->get('serializer');
        foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            if ($lr->isStatus() && $lr->getFonction()->getId() == '32') {
                $possible = true;
            }
        }
        if ($possible) {
            //Block Toutes les categories
            foreach ($category  as $cat) {
                $pubs = [];

                foreach ($cat->getPublications()  as $pub) {
                    $sms = $BulkEntityManager->getRepository(Sms::class)->findOneBy(['id' => $pub->getSms()]);
                    if ($sms) {
                        if ($sms->getClientId()) {
                            $client
                                = $this->em->getRepository(Client::class)->findOneBy(['id' => $sms->getClientId()]);
                            if ($client) {

                                $path =
                                    ($client->getProfile()) ?    $client->getProfile()->getFilePath() ?? '' : '';
                                if ($pub->getPublicationObject()) {
                                    if ($pub->getPublicationObject()->getFilePathId()) {
                                        array_push($pubs, [
                                            'id' => $pub->getId(),
                                            'title' => $pub->getTitle(),
                                            'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                            'description' =>  $pub->getDescription(),
                                            'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                            'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($pubs)) {
                    array_push($listCatFinal, [
                        'id' => $cat->getId(),
                        'label' => $cat->getTitle(),
                        'publications' => $pubs,


                    ]);
                }
            }


            //Block   la publication la plus recente de la semaine

            $dateStartWeek = strftime("%Y-%m-%d", strtotime("this week"));
            $dateEndWeek = strftime("%Y-%m-%d", strtotime("this week + 6days"));

            foreach ($category  as $cat) {
                $pubs = [];

                foreach ($cat->getPublications()  as $pub) {

                    // var_dump($dateStartWeek, $pub->getDateCreated()->format('Y-m-d'), $dateEndWeek);
                    // var_dump(   $dateStartWeek <=
                    //     $pub->getDateCreated()->format('Y-m-d')  && $pub->getDateCreated()->format('Y-m-d')
                    //     <=      $dateEndWeek);
                    if (
                        $dateStartWeek <=
                        $pub->getDateCreated()->format('Y-m-d')  && $pub->getDateCreated()->format('Y-m-d')
                        <=      $dateEndWeek
                    ) {
                        $sms = $BulkEntityManager->getRepository(Sms::class)->findOneBy(['id' => $pub->getSms()]);
                        if ($sms) {
                            if ($sms->getClientId()) {
                                $client
                                    = $this->em->getRepository(Client::class)->findOneBy(['id' => $sms->getClientId()]);
                                if ($client) {

                                    $path =
                                        ($client->getProfile()) ?    $client->getProfile()->getFilePath() ?? '' : '';


                                    if ($pub->getPublicationObject()) {
                                        if ($pub->getPublicationObject()->getFilePathId()) {

                                            if (empty($pubs)) {
                                                array_push($pubs, [
                                                    'id' => $pub->getId(),
                                                    'title' => $pub->getTitle(),
                                                    'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                                    'description' =>  $pub->getDescription(),
                                                    'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                                    'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                                ]);
                                            } else {

                                                if ($pubs[0]['date'] < $pub->getDateCreated()->format('Y-m-d')) {


                                                    $pubX
                                                        = [
                                                            'id' => $pub->getId(),
                                                            'title' => $pub->getTitle(),
                                                            'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                                            'description' =>  $pub->getDescription(),
                                                            'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                                            'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                                        ];

                                                    array_push($pubs, $pubX);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($pubs)) {
                    array_push($listCatThisWeek, [
                        'id' => $cat->getId(),
                        'label' => $cat->getTitle(),
                        'publications' => $pubs,


                    ]);
                }
            }
            // var_dump($listCatThisWeek);

            if (!empty($listCatThisWeek)) {
                $saveCat = $listCatThisWeek[0];

                for (
                    $j = 0;
                    $j < count($listCatThisWeek);
                    $j++
                ) {

                    for ($i = 0; $i < count($listCatThisWeek[$j]['publications']); $i++) {
                        for ($k = 0; $k < count($saveCat['publications']); $k++) {


                            if (
                                $listCatThisWeek[$j]['publications'][$i]['date']
                                >=

                                $saveCat['publications'][$k]['date']
                            ) {
                                $saveCat = $listCatThisWeek[$j];
                            }
                        }
                    }
                }
            }


            //Block   la publication la plus recente de ce mois

            $dateStartMonth = strftime("%Y-%m-%d", strtotime(date('Y-m-1')));
            $dateEndMonth =
                date("t/m/y", strtotime("this month"));

            foreach ($category  as $cat) {
                $pubs = [];

                foreach ($cat->getPublications()  as $pub) {


                    if (
                        $dateStartMonth <=
                        $pub->getDateCreated()->format('Y-m-d')  && $pub->getDateCreated()->format('Y-m-d')
                        <=      $dateEndMonth
                    ) {
                        $sms = $BulkEntityManager->getRepository(Sms::class)->findOneBy(['id' => $pub->getSms()]);
                        if ($sms) {
                            if ($sms->getClientId()) {
                                $client
                                    = $this->em->getRepository(Client::class)->findOneBy(['id' => $sms->getClientId()]);
                                if ($client) {

                                    $path =
                                        ($client->getProfile()) ?    $client->getProfile()->getFilePath() ?? '' : '';


                                    if ($pub->getPublicationObject()) {
                                        if ($pub->getPublicationObject()->getFilePathId()) {

                                            if (empty($pubs)) {
                                                array_push($pubs, [
                                                    'id' => $pub->getId(),
                                                    'title' => $pub->getTitle(),
                                                    'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                                    'description' =>  $pub->getDescription(),
                                                    'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                                    'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                                ]);
                                            } else {

                                                if ($pubs[0]['date'] < $pub->getDateCreated()->format('Y-m-d')) {


                                                    $pubX
                                                        = [
                                                            'id' => $pub->getId(),
                                                            'title' => $pub->getTitle(),
                                                            'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                                            'description' =>  $pub->getDescription(),
                                                            'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                                            'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                                        ];

                                                    array_push($pubs, $pubX);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($pubs)) {
                    array_push($listCatThisMonth, [
                        'id' => $cat->getId(),
                        'label' => $cat->getTitle(),
                        'publications' => $pubs,


                    ]);
                }
            }



            if (!empty($listCatThisMonth)) {
                $saveMonth = $listCatThisMonth[0];
                // var_dump($saveMonth);
                for (
                    $j = 0;
                    $j < count($listCatThisMonth);
                    $j++
                ) {
                    // VAR_DUMP(($listCatThisMonth[$j]['publications']));
                    for ($i = 0; $i < count($listCatThisMonth[$j]['publications']); $i++) {
                        for ($k = 0; $k < count($saveMonth['publications']); $k++) {


                            if (
                                $listCatThisMonth[$j]['publications'][$i]['date']
                                >=

                                $saveMonth['publications'][$k]['date']
                            ) {
                                $saveMonth = $listCatThisMonth[$j];
                            }
                        }
                    }
                }


                //

            }


            //Block   la publication la plus recente de la journee

            $datethisDay = strftime("%Y-%m-%d", strtotime(date('Y-m-d')));


            foreach ($category  as $cat) {
                $pubs = [];

                foreach ($cat->getPublications()  as $pub) {
                    // var_dump($datethisDay == $pub->getDateCreated()->format('d/m/Y'));

                    if (
                        $datethisDay ==
                        $pub->getDateCreated()->format('Y-m-d')
                    ) {
                        $sms = $BulkEntityManager->getRepository(Sms::class)->findOneBy(['id' => $pub->getSms()]);
                        if ($sms) {
                            if ($sms->getClientId()) {
                                $client
                                    = $this->em->getRepository(Client::class)->findOneBy(['id' => $sms->getClientId()]);
                                if ($client) {

                                    $path =
                                        ($client->getProfile()) ?    $client->getProfile()->getFilePath() ?? '' : '';


                                    if ($pub->getPublicationObject()) {
                                        if ($pub->getPublicationObject()->getFilePathId()) {

                                            if (empty($pubs)) {
                                                array_push($pubs, [
                                                    'id' => $pub->getId(),
                                                    'title' => $pub->getTitle(),
                                                    'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                                    'description' =>  $pub->getDescription(),
                                                    'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                                    'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                                ]);
                                            } else {

                                                if ($pubs[0]['date'] < $pub->getDateCreated()->format('Y-m-d')) {


                                                    $pubX
                                                        = [
                                                            'id' => $pub->getId(),
                                                            'title' => $pub->getTitle(),
                                                            'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                                            'description' =>  $pub->getDescription(),
                                                            'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                                            'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                                        ];

                                                    array_push($pubs, $pubX);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($pubs)) {
                    array_push($listCatThisDay, [
                        'id' => $cat->getId(),
                        'label' => $cat->getTitle(),
                        'publications' => $pubs,


                    ]);
                }
            }



            if (!empty($listCatThisDay)) {
                $saveDay = $listCatThisDay[0];
                for (
                    $j = 0;
                    $j < count($listCatThisDay);
                    $j++
                ) {

                    for ($i = 0; $i < count($listCatThisDay[$j]['publications']); $i++) {


                        if (
                            $listCatThisDay[$j]['publications'][$i]['date']
                            >=

                            $saveDay['publications'][$i]['date']
                        ) {
                            $saveDay = $listCatThisDay[$j];
                        }
                    }
                }


                //

            }




            //Block   la publication la plus recente de l'annee'

            $datethisYearStart = strftime(
                "%Y-%m-%d",
                strtotime(date('Y-01-01'))
            );
            $datethisYearEnd = strftime("%Y-%m-%d", strtotime(date('Y-12-31')));


            foreach ($category  as $cat) {
                $pubs = [];

                foreach ($cat->getPublications()  as $pub) {


                    if (
                        $datethisYearStart <=
                        $pub->getDateCreated()->format('Y-m-d')  && $pub->getDateCreated()->format('Y-m-d')
                        <=      $datethisYearEnd
                    ) {
                        $sms = $BulkEntityManager->getRepository(Sms::class)->findOneBy(['id' => $pub->getSms()]);
                        if ($sms) {
                            if ($sms->getClientId()) {
                                $client
                                    = $this->em->getRepository(Client::class)->findOneBy(['id' => $sms->getClientId()]);
                                if ($client) {

                                    $path =
                                        ($client->getProfile()) ?    $client->getProfile()->getFilePath() ?? '' : '';


                                    if ($pub->getPublicationObject()) {
                                        if ($pub->getPublicationObject()->getFilePathId()) {

                                            if (empty($pubs)) {
                                                array_push($pubs, [
                                                    'id' => $pub->getId(),
                                                    'title' => $pub->getTitle(),
                                                    'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                                    'description' =>  $pub->getDescription(),
                                                    'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                                    'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                                ]);
                                            } else {

                                                if ($pubs[0]['date'] < $pub->getDateCreated()->format('Y-m-d')) {


                                                    $pubX
                                                        = [
                                                            'id' => $pub->getId(),
                                                            'title' => $pub->getTitle(),
                                                            'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/publications/' . $pub->getPublicationObject()->getFilePathId(),
                                                            'description' =>  $pub->getDescription(),
                                                            'profileUser' => 'https://' . $_SERVER['SERVER_NAME'] . '/images/client/' . $path,
                                                            'date' => ($pub->getDateCreated() ?? new \DateTime('now'))->format('Y-m-d')

                                                        ];

                                                    array_push($pubs, $pubX);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($pubs)) {
                    array_push($listCatThisYear, [
                        'id' => $cat->getId(),
                        'label' => $cat->getTitle(),
                        'publications' => $pubs,


                    ]);
                }
            }



            if (!empty($listCatThisYear)) {
                $saveYear = $listCatThisYear[0];
                for (
                    $j = 0;
                    $j < count($listCatThisYear);
                    $j++
                ) {

                    for (
                        $i = 0;
                        $i < count($listCatThisYear[$j]['publications']);
                        $i++
                    ) {
                        for (
                            $k = 0;
                            $k < count($saveYear['publications']);
                            $k++
                        ) {


                            if (
                                $listCatThisYear[$j]['publications'][$i]['date']
                                >=

                                $saveYear['publications'][$k]['date']
                            ) {
                                $saveYear = $listCatThisYear[$j];
                            }
                        }
                    }
                }


                //

            }





            $Final = $serializer->serialize(array_reverse($listCatFinal), 'json');
            $FinaDay = $serializer->serialize([array_reverse($saveDay)], 'json');
            $FinalWeek = $serializer->serialize([array_reverse($saveCat)], 'json');
            $FinalMonth = $serializer->serialize([array_reverse($saveMonth)], 'json');
            $FinalYear = $serializer->serialize([array_reverse($saveYear)], 'json');


            return
                new JsonResponse([
                    'data'
                    =>
                    JSON_DECODE($Final),
                    'thisDay'
                    =>  JSON_DECODE($FinaDay),
                    'thisWeek'
                    =>  JSON_DECODE($FinalWeek),
                    'thisMonth'
                    =>  JSON_DECODE($FinalMonth),
                    'thisYear'
                    =>  JSON_DECODE($FinalYear),

                ], 201);
        } else {
            return new JsonResponse([
                'message' => 'Action impossible000'
            ], 400);
        }
    }
}
