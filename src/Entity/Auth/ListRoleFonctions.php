<?php

namespace App\Entity\Auth;

use App\Repository\Auth\ListRoleFonctionsRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

use ApiPlatform\Core\Annotation\ApiFilter;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;

/**
 * @ORM\Entity(repositoryClass=ListRoleFonctionsRepository::class)
 * @ApiResource()
 * @ApiFilter(
 *     BooleanFilter::class,
 *     properties={
 *       
 *       "id": "exact"
 *     }
 * )
 */
class ListRoleFonctions
{
    /**
     * 
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Roles::class, inversedBy="listRoleFonctions")
     * @Groups({"read:fonction"})
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity=Fonctions::class, inversedBy="listRoleFonctions")
     */
    private $fonction;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreate;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:fonction"})
     * 
     */
    private $status;
    public function __construct()
    {
        $this->dateCreate  = new \DateTime();
        $this->status = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?Roles
    {
        return $this->role;
    }

    public function setRole(?Roles $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getFonction(): ?Fonctions
    {
        return $this->fonction;
    }

    public function setFonction(?Fonctions $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTimeInterface $dateCreate): self
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
