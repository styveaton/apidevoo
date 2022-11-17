<?php

namespace App\EventSubscriber\Account;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Account\TransactionCompte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;


final class TransactionSubscriber extends AbstractController implements EventSubscriberInterface
{
    private $em;
    public    $doctrine;

    public function __construct(EntityManagerInterface $em,  ManagerRegistry $doctrine)
    {
        $this->em = $em;
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['transactionCompte', EventPriorities::PRE_WRITE]
        ];
    }

    public function transactionCompte(ViewEvent $event): void
    {

        $Transactions = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        $bulkEntityManager = $this->doctrine->getManager('Bulk');
        $AccountEntityManager = $this->doctrine->getManager('Account');
        #----------------- Methode POST pour la transaction ---------------------------#
        /**
         * type transaction 1-> depot, 2-> retrait , 3-> Achat Sms
         */

        if ($Transactions instanceof TransactionCompte && Request::METHOD_POST === $method) {

            if ($Transactions->getMontant() != 0) {

                if (!empty($Transactions->getEmetteur())) {



                    /**
                     * depot
                     */
                    if ($Transactions->getTypeTransaction()->getId()  == 1 && ($Transactions->getEmetteur()->getTypeCompte()->getId() != 1) && ($Transactions->getRecepteur()->getTypeCompte()->getId() != 3) && !empty($Transactions->getRecepteur())) {


                        /**
                         * recharge type compte local [porte feuile ver sompte locale]
                         */
                        if ($Transactions->getRecepteur()->getTypeCompte()->getId() == 2) {

                            if ($Transactions->getEmetteur()->getSolde() > ($Transactions->getMontant())) {

                                $montantFinalR = $Transactions->getRecepteur()->getSolde() + $Transactions->getMontant();
                                $montantFinalE = $Transactions->getEmetteur()->getSolde() - $Transactions->getMontant();
                                $Transactions->getRecepteur()->setSolde($montantFinalR);
                                $Transactions->getEmetteur()->setSolde($montantFinalE);
                                $AccountEntityManager->persist($Transactions);
                                $AccountEntityManager->flush();
                            }
                        } else {
                            dd($Transactions->getTypeTransaction()->getId());
                        }
                    }
                    /**
                     * retrait du type compte porte feuille
                     */
                    else if ($Transactions->getTypeTransaction()->getId()  == 2 && ($Transactions->getEmetteur()->getTypeCompte()->getId() != 1) && ($Transactions->getEmetteur()->getTypeCompte()->getId() != 2)) {

                        if ($Transactions->getEmetteur()->getSolde() > ($Transactions->getMontant())) {


                            $montantFinalE = $Transactions->getEmetteur()->getSolde() - $Transactions->getMontant();

                            $Transactions->getEmetteur()->setSolde($montantFinalE);
                            $AccountEntityManager->persist($Transactions);
                            $AccountEntityManager->flush();
                        }
                    }

                    /**
                     * recharge du solde sms a partir des autres comptes
                     */
                    else  if ($Transactions->getTypeTransaction()->getId()  == 3 && !empty($Transactions->getRecepteur())) {


                        if ($Transactions->getEmetteur()->getSolde() > ($Transactions->getMontant())) {
                        }
                    } else {
                        var_dump('iii');
                    }
                } else if ($Transactions->getTypeTransaction()->getId()  == 4 && $Transactions->getRecepteur()->getTypeCompte()->getId() == 3) {
                    $montantFinalR = $Transactions->getRecepteur()->getSolde() + $Transactions->getMontant();

                    $Transactions->getRecepteur()->setSolde($montantFinalR);
                    $AccountEntityManager->persist($Transactions);
                    $AccountEntityManager->flush();
                } else   if (
                    $Transactions->getRecepteur()->getTypeCompte()->getId() == 2 &&
                    $Transactions->getTypeTransaction()->getId()  == 1
                ) {

                    // $montantFinalR = $Transactions->getRecepteur()->getSolde() + $Transactions->getMontant();

                    // $Transactions->getRecepteur()->setSolde($montantFinalR);

                    // $AccountEntityManager->persist($Transactions);
                    // $AccountEntityManager->flush();

                }

                return;
            }
        }


        if ($Transactions instanceof TransactionCompte && Request::METHOD_PATCH === $method) {

            if ($Transactions->getMontant() != 0) {
                if (empty($Transactions->getEmetteur()) && !empty($Transactions->getRecepteur())) {

                    /**
                     * depot sur le compte local
                     */
                    if ($Transactions->getTypeTransaction()->getId()  == 1) {

                        /**
                         * recharge type compte local [externe vers sompte locale]
                         */
                        if ($Transactions->getRecepteur()->getTypeCompte()->getId() == 2) {
                            if ($Transactions->getStatus() == 0) {
                                $montantFinalR = $Transactions->getRecepteur()->getSolde() + $Transactions->getMontant();
                                $Transactions->getRecepteur()->setSolde($montantFinalR);
                                $Transactions->setStatus(1); 
                                $AccountEntityManager->persist($Transactions);
                                $AccountEntityManager->flush();
                            }
                        } else {
                            dd($Transactions->getTypeTransaction()->getId());
                        }
                    }
                }
            }
        }
    }
}
