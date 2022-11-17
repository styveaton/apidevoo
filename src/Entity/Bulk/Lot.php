<?php

namespace App\Entity\Bulk;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Bulk\LotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:lot"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:lot"
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
 *              "security"="is_granted('ROLE_SMS_DELETE')"
 *          },
 *     }
 * )
 * @ORM\Entity(repositoryClass=LotRepository::class)
 */
class Lot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:lot"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read:lot"})
     */
    private $dateCreated;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsLotsEnvoye::class, mappedBy="lot", cascade={"persist"})
     */
    private $listSmsLotsEnvoyes;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:lot"})
     */
    private $status = false;

    /**
     * @ORM\OneToMany(targetEntity=ListLotCalendrier::class, mappedBy="lot", cascade={"persist"})
     */
    private $listLotCalendriers;

    public function __construct()
    {
        $this->listSmsLotsEnvoyes = new ArrayCollection();
        $this->dateCreated = new \DateTime();
        $this->listLotCalendriers = new ArrayCollection();
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

    /**
     * @return Collection<int, ListSmsLotsEnvoye>
     */
    public function getListSmsLotsEnvoyes(): Collection
    {
        return $this->listSmsLotsEnvoyes;
    }

    public function addListSmsLotsEnvoye(ListSmsLotsEnvoye $listSmsLotsEnvoye): self
    {
        if (!$this->listSmsLotsEnvoyes->contains($listSmsLotsEnvoye)) {
            $this->listSmsLotsEnvoyes[] = $listSmsLotsEnvoye;
            $listSmsLotsEnvoye->setLot($this);
        }

        return $this;
    }

    public function removeListSmsLotsEnvoye(ListSmsLotsEnvoye $listSmsLotsEnvoye): self
    {
        if ($this->listSmsLotsEnvoyes->removeElement($listSmsLotsEnvoye)) {
            // set the owning side to null (unless already changed)
            if ($listSmsLotsEnvoye->getLot() === $this) {
                $listSmsLotsEnvoye->setLot(null);
            }
        }

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
            $listLotCalendrier->setLot($this);
        }

        return $this;
    }

    public function removeListLotCalendrier(ListLotCalendrier $listLotCalendrier): self
    {
        if ($this->listLotCalendriers->removeElement($listLotCalendrier)) {
            // set the owning side to null (unless already changed)
            if ($listLotCalendrier->getLot() === $this) {
                $listLotCalendrier->setLot(null);
            }
        }

        return $this;
    }
}
