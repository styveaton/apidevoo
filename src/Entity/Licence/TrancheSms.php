<?php

namespace App\Entity\Licence;

use App\Repository\Licence\TrancheSmsRepository;
use Doctrine\ORM\Mapping as ORM;


use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TrancheSmsRepository::class)
 *@ApiResource(
 *  normalizationContext={"groups"={"read:tranche"}},
 *  denormalizationContext={"groups"={"write:tranche"}},
 * ) 
 */
class TrancheSms
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:tranche","write:tranche"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read:tranche","write:tranche"})
     */
    private $min;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read:tranche","write:tranche"})
     */
    private $max;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read:tranche","write:tranche"})
     */
    private $pourcentage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function setMax(int $max): self
    {
        $this->max = $max;

        return $this;
    }

    public function getPourcentage(): ?int
    {
        return $this->pourcentage;
    }

    public function setPourcentage(int $pourcentage): self
    {
        $this->pourcentage = $pourcentage;

        return $this;
    }
}
