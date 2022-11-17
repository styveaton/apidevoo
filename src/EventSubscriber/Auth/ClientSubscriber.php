<?php

namespace App\EventSubscriber\Auth;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Account\Compte;
use App\Entity\Account\TypeCompte;
use App\Entity\Auth\Client;
use App\Entity\Auth\Roles;
use App\Entity\Bulk\GroupeContact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Vitrine\Vitrine;

final class ClientSubscriber extends AbstractController implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['operationAfterCreateAMember', EventPriorities::POST_WRITE]
        ];
    }

    public function operationAfterCreateAMember(ViewEvent $event): void
    {
        $Client = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');

        $bulkEntityManager = $this->doctrine->getManager('Bulk');
        $AccountEntityManager = $this->doctrine->getManager('Account');
        if ($Client instanceof Client && Request::METHOD_POST === $method) {
            $otherNumber = [];

            for ($i = 0; $i < 4; $i++) {
                try {
                    $otherNumber[] = random_int(0, 9);
                } catch (\Exception $e) {
                    echo $e;
                }
            }

            $keySecret = password_hash(($Client->getPhone() . '' . $Client->getPassword() . '' . (new \DateTime())->format('Y-m-d H:i:s') . '' . implode("", $otherNumber)), PASSWORD_DEFAULT);

            if (strlen($keySecret) > 100) {
                $keySecret = substr($keySecret, 0, 99);
            }

            $Client->setKeySecret($keySecret);
            $this->em->persist($Client);
            $this->em->flush();
        }


        if ($Client instanceof Client && Request::METHOD_POST === $method) {
            $roleClient = $this->em->getRepository(Roles::class)->findOneBy(['id' => 5]);
            if ($Client->getRole() == null) {
               

                $Client->setRole($roleClient);
                $this->em->persist($Client);
                $this->em->flush();
            }

            if ($Client->getCodeParrain() == null) {
                $su = $this->em->getRepository(Client::class)->findBy(['status' => true]);

                foreach ($su as $u) {
                    if ($u->getRole()->getId() == 1) {


                        $codeParrain = $u->getId() . '@' . $Client->getId();
                        $Client->setCodeParrain($codeParrain);
                        $this->em->persist($Client);
                        $this->em->flush();
                        break;
                    }
                }
            } else {
                $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findBy(['nom' => $Client->getCodeParrain()]);
                if ($vitrine) {
                    $codeParrain =
                        $vitrine->getClientId() . '@' . $Client->getId();
                } else {
                    $codeParrain = $Client->getCodeParrain() . '@' . $Client->getId();
                }


                $Client->setCodeParrain($codeParrain);
                $this->em->persist($Client);
                $this->em->flush();
            }


            for ($i = 1; $i <= 3; $i++) {
                $newCompte = new Compte();
                $typeComte = $AccountEntityManager->getRepository(TypeCompte::class)->findOneBy(['id' => $i]);

                $newCompte->setClientId($Client->getId());
                $newCompte->setTypeCompte($typeComte);
                $AccountEntityManager->persist($newCompte);

                $AccountEntityManager->flush();
            }



            $groupeDefault = new GroupeContact();
            $groupeDefault->setNom('Default');
            $groupeDefault->setDescription('Groupe de contact par defaut de l\'tilisateur');
            $groupeDefault->setStatus(false);
            $groupeDefault->setClientId($Client->getId());
            $bulkEntityManager->persist($groupeDefault);

            $bulkEntityManager->flush();
        }
    }
}
