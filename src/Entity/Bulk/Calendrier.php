<?php

namespace App\Entity\Bulk;

use App\Entity\Auth\Client;
use App\Repository\Bulk\CalendrierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CalendrierRepository::class),
 * 
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:calendar"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:calendar"
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
 *      "clientId": "exact"
 *     }
 * )
 *  
 *

 */
class Calendrier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *  @Groups({"read:calendar"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     *  @Groups({"read:calendar","read:listlotcalendar" })
     */
    private $dateProgramme;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     *  @Groups({"read:calendar", "create:calendar"})
     */
    private $dateExecution;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="integer", length=255)
     *  @Groups({"read:calendar", "create:calendar"})
     */
    private $frequence;

    /**
     * @ORM\Column(type="boolean")
     *   @Groups({"read:calendar"})
     */
    private $status = true;

    /**
     * @ORM\OneToMany(targetEntity=ListLotCalendrier::class, mappedBy="calendrier", cascade={"persist"})
     */
    private $listLotCalendriers;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read:calendar", "create:calendar"})
     */
    private $clientId;


    public function __construct()
    {
        $this->listLotCalendriers = new ArrayCollection();
        $this->dateCreated = new \DateTime();
        $this->dateProgramme = new \DateTime();
        $this->dateExecution = new \DateTime();
        $this->frequence = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateProgramme(): ?\DateTimeInterface
    {
        return $this->dateProgramme;
    }

    public function setDateProgramme(\DateTimeInterface $dateProgramme): self
    {
        $this->dateProgramme = $dateProgramme;

        return $this;
    }

    public function getDateExecution(): ?\DateTimeInterface
    {
        return $this->dateExecution;
    }

    public function setDateExecution(\DateTimeInterface $dateExecution): self
    {
        $this->dateExecution = $dateExecution;

        return $this;
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

    public function getFrequence(): ?string
    {
        return $this->frequence;
    }

    public function setFrequence(string $frequence): self
    {
        $this->frequence = $frequence;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, ListLotCalendrier>
     */
    public function getListLotCalendriers(): Collection
    {
        return $this->listLotCalendriers;
    }

    public function addListLotCalendrier(ListLotCalendrier $listLotCalendrier): self
    {
        if (!$this->listLotCalendriers->contains($listLotCalendrier)) {
            $this->listLotCalendriers[] = $listLotCalendrier;
            $listLotCalendrier->setCalendrier($this);
        }

        return $this;
    }

    public function removeListLotCalendrier(ListLotCalendrier $listLotCalendrier): self
    {
        if ($this->listLotCalendriers->removeElement($listLotCalendrier)) {
            // set the owning side to null (unless already changed)
            if ($listLotCalendrier->getCalendrier() === $this) {
                $listLotCalendrier->setCalendrier(null);
            }
        }

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }
}
