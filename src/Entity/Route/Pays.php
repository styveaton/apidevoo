<?php

namespace App\Entity\Route;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Route\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PaysRepository::class)
 * @ApiResource(
 * collectionOperations = { 
 * "get" = {
 * "normalization_context"={
 *                  "groups"={
 *                      "read:pays"
 *                  }
 *              },
 * 
 * },
 * "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:pays"
 *                  }
 *              },
 *            
 *           
 *          }},
 * 
 * itemOperations={
 *          "get"={},
 * "delete"={},
 *          "patch"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "write:pays"
 *              },
 *             },
 * }
 * }),
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *    "routes": "exact"
 *     }
 * )
 */
class Pays
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:pays","read:route","read:operateur","read:senderId"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:pays","read:route", "read:operateur", "read:senderId"," create:pays"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:pays","read:route","read:operateur","read:senderId","create:pays"})
     */
    private $codePhone;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:pays","read:route","read:operateur","read:senderId","create:pays"})
     */
    private $sigle;

    /**
     * @ORM\OneToMany(targetEntity=Route::class, mappedBy="pays")
     * @Groups({"read:pays"})
     */
    private $routes;

    public function __construct()
    {
        $this->routes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodePhone(): ?string
    {
        return $this->codePhone;
    }

    public function setCodePhone(string $codePhone): self
    {
        $this->codePhone = $codePhone;

        return $this;
    }

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(string $sigle): self
    {
        $this->sigle = $sigle;

        return $this;
    }

    /**
     * @return Collection<int, Route>
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function addRoute(Route $route): self
    {
        if (!$this->routes->contains($route)) {
            $this->routes[] = $route;
            $route->setPays($this);
        }

        return $this;
    }

    public function removeRoute(Route $route): self
    {
        if ($this->routes->removeElement($route)) {
            // set the owning side to null (unless already changed)
            if ($route->getPays() === $this) {
                $route->setPays(null);
            }
        }

        return $this;
    }
}
