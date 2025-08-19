<?php

namespace App\Entity;

use App\Enum\DescriptionType;
use App\Enum\ManufacturerType;
use App\Repository\ToolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ToolRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['id'], message: 'This ID is already taken')]
class Tool
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Please enter a unique ID')]
    private ?string $id = null;

    #[ORM\Column(type: 'string', enumType: DescriptionType::class)]
    private DescriptionType $description;

    #[ORM\Column(type: 'string', enumType: ManufacturerType::class)]
    private ManufacturerType $manufacturer;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(type: 'string', length: 1)]
    #[Assert\Choice(choices: ['1', '2', '3', '4', '5'])]
    private ?string $armoire = null;
    
    #[ORM\Column(type: 'string', length: 1)]
    #[Assert\Choice(choices: ['A', 'B', 'C', 'D'])]
    private ?string $dnas = null;
    
    #[ORM\Column(type: 'string', length: 1)]
    #[Assert\Choice(choices: ['1', '2', '3', '4', '5', '6'])]
    private ?string $emplacement = null;

    #[ORM\OneToMany(mappedBy: 'tool', targetEntity: IO::class)]
    private Collection $ios;

    public function __construct()
    {
        $this->ios = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDescription(): DescriptionType
    {
        return $this->description;
    }

    public function setDescription(DescriptionType $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getManufacturer(): ManufacturerType
    {
        return $this->manufacturer;
    }

    public function setManufacturer(ManufacturerType $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getStatus(): StatusToolType
    {
        return $this->status;
    }

    public function setStatus(StatusToolType $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
    
    public function getArmoire(): ?string
    {
        return $this->armoire;
    }
    
    public function setArmoire(string $armoire): static
    {
        $this->armoire = $armoire;
        
        return $this;
    }
    
    public function getDnas(): ?string
    {
        return $this->dnas;
    }
    
    public function setDnas(string $dnas): static
    {
        $this->dnas = $dnas;
        
        return $this;
    }
    
    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }
    
    public function setEmplacement(string $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }
}
