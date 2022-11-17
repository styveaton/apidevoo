<?php

namespace App\Entity\Bulk;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Bulk\ListSmsLotsEnvoyeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:listsmslotsenvoye"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *               
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:listsmslotsenvoye"
 *                  }
 *              },
 *              
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *               
 *          },
 *          "patch"={
 * 
 *          },
 *          "delete"={
 * 
 *          },
 *     }
 * ),
 *  * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *      "lot": "exact",
 *      "sms": "exact",
 *  "sms.clientId": "exact",
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass=ListSmsLotsEnvoyeRepository::class)
 */
class ListSmsLotsEnvoye
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read:listsmslotsenvoye","read:routelse"})
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"create:listsmslotsenvoye","read:routelse"})
     */
    private $status = false;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsContact::class, mappedBy="listSmsLotsEnvoye", cascade={"persist"})
     * @Groups({"create:sms","read:listsmslotsenvoye","read:routelse"})
     */
    private $listSmsContacts;

    /**
     * @ORM\ManyToOne(targetEntity=Lot::class, inversedBy="listSmsLotsEnvoyes")
     * @Groups({"create:sms","create:listsmslotsenvoye","read:listsmslotsenvoye","read:routelse"})
     */
    private $lot;

    /**
     * @ORM\ManyToOne(targetEntity=Sms::class, inversedBy="listSmsLotsEnvoyes")
     * @Groups({"create:listsmslotsenvoye","read:listsmslotsenvoye","read:routelse"})
     */
    private $sms;

    /**
     * @ORM\OneToMany(targetEntity=RouteListSmsLotsEnvoye::class, mappedBy="listSmsLotsEnvoye", cascade={"persist"})
     * @Groups({"create:sms"})
     */
    private $routeListSmsLotsEnvoyes;

    /**
     * @ORM\ManyToOne(targetEntity=GroupeContact::class, inversedBy="listSmsLotsEnvoyes")
     * @Groups({"create:sms"})
     */
    private $groupeContact;

    public function __construct()
    {
        $this->listSmsContacts = new ArrayCollection();
        $this->dateCreated = new \DateTime();
        $this->routeListSmsLotsEnvoyes = new ArrayCollection();
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
     * @return Collection<int, ListSmsContact>
     */
    public function getListSmsContacts(): Collection
    {
        return $this->listSmsContacts;
    }

    public function addListSmsContact(ListSmsContact $listSmsContact): self
    {
        if (!$this->listSmsContacts->contains($listSmsContact)) {
            $this->listSmsContacts[] = $listSmsContact;
            $listSmsContact->setListSmsLotsEnvoye($this);
        }

        return $this;
    }

    public function removeListSmsContact(ListSmsContact $listSmsContact): self
    {
        if ($this->listSmsContacts->removeElement($listSmsContact)) {
            // set the owning side to null (unless already changed)
            if ($listSmsContact->getListSmsLotsEnvoye() === $this) {
                $listSmsContact->setListSmsLotsEnvoye(null);
            }
        }

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

    public function getSms(): ?Sms
    {
        return $this->sms;
    }

    public function setSms(?Sms $sms): self
    {
        $this->sms = $sms;

        return $this;
    }

    /**
     * @return Collection<int, RouteListSmsLotsEnvoye>
     */
    public function getRouteListSmsLotsEnvoyes(): Collection
    {
        return $this->routeListSmsLotsEnvoyes;
    }

    public function addRouteListSmsLotsEnvoye(RouteListSmsLotsEnvoye $routeListSmsLotsEnvoye): self
    {
        if (!$this->routeListSmsLotsEnvoyes->contains($routeListSmsLotsEnvoye)) {
            $this->routeListSmsLotsEnvoyes[] = $routeListSmsLotsEnvoye;
            $routeListSmsLotsEnvoye->setListSmsLotsEnvoye($this);
        }

        return $this;
    }

    public function removeRouteListSmsLotsEnvoye(RouteListSmsLotsEnvoye $routeListSmsLotsEnvoye): self
    {
        if ($this->routeListSmsLotsEnvoyes->removeElement($routeListSmsLotsEnvoye)) {
            // set the owning side to null (unless already changed)
            if ($routeListSmsLotsEnvoye->getListSmsLotsEnvoye() === $this) {
                $routeListSmsLotsEnvoye->setListSmsLotsEnvoye(null);
            }
        }

        return $this;
    }

    public function getGroupeContact(): ?GroupeContact
    {
        return $this->groupeContact;
    }

    public function setGroupeContact(?GroupeContact $groupeContact): self
    {
        $this->groupeContact = $groupeContact;

        return $this;
    }
}
