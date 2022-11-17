<?php

namespace App\Entity\Bulk;

use App\Entity\Bulk\Calendrier;
use App\Entity\Bulk\Lot;
use App\Repository\Bulk\ListLotCalendrierRepository;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * 
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:listlotcalendar"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:listlotcalendar"
 *                  }
 *              },
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "patch"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "delete"={
 *               
 *          },
 *     }
 * ),
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *       
 *    "calendrier.clientId": "exact"
 *     }
 * )
 *  
 *
 * @ORM\Entity(repositoryClass=ListLotCalendrierRepository::class)
 */
class ListLotCalendrier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer") 
     * @Groups({"read:listlotcalendar"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\ManyToOne(targetEntity=Calendrier::class, inversedBy="listLotCalendriers")
     * @Groups({"read:listlotcalendar", "create:listlotcalendar"})
     */
    private $calendrier;

    /**
     * @ORM\ManyToOne(targetEntity=lot::class, inversedBy="listLotCalendriers")
     * @Groups({"read:listlotcalendar", "create:listlotcalendar"})
     */
    private $lot;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getCalendrier(): ?Calendrier
    {
        return $this->calendrier;
    }

    public function setCalendrier(?Calendrier $calendrier): self
    {
        $this->calendrier = $calendrier;

        return $this;
    }

    public function getLot(): ?Lot
    {
        return $this->lot;
    }

    public function setLot(?Lot $lot): self
    {
        $this->lot = $lot;

        return $this;
    }
}
