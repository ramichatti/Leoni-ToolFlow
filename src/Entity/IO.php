<?php

namespace App\Entity;

use App\Repository\IORepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\IOStatus;

#[ORM\Entity(repositoryClass: IORepository::class)]
#[ORM\Table(name: 'i_o')]
#[ORM\HasLifecycleCallbacks]
class IO
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tool::class)]
    #[ORM\JoinColumn(name: "tool_id", referencedColumnName: "id", nullable: false)]
    private ?Tool $tool = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\Positive(message: 'Section must be a positive number')]
    private ?float $section = null;

    #[ORM\Column(type: 'float', name: 'crimping_height', nullable: true)]
    #[Assert\Positive(message: 'Crimping height must be a positive number')]
    private ?float $crimpingHeight = null;

    #[ORM\Column(type: 'float', name: 'insulation_height', nullable: true)]
    #[Assert\Positive(message: 'Insulation height must be a positive number')]
    private ?float $insulationHeight = null;

    #[ORM\Column(type: 'float', name: 'crimping_width', nullable: true)]
    #[Assert\Positive(message: 'Crimping width must be a positive number')]
    private ?float $crimpingWidth = null;

    #[ORM\Column(type: 'float', name: 'insulation_width', nullable: true)]
    #[Assert\Positive(message: 'Insulation width must be a positive number')]
    private ?float $insulationWidth = null;

    #[ORM\Column(type: 'string', enumType: IOStatus::class)]
    private IOStatus $status;

    #[ORM\Column(type: 'datetime', name: 'date_entre')]
    private ?\DateTimeInterface $dateEntre = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Measure::class)]
    #[ORM\JoinColumn(name: "measure_id", referencedColumnName: "id", nullable: true)]
    private ?Measure $measure = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $conformite = 'non conforme';

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $machine = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $withCahier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTool(): ?Tool
    {
        return $this->tool;
    }

    public function setTool(?Tool $tool): static
    {
        $this->tool = $tool;

        return $this;
    }

    public function getSection(): ?float
    {
        return $this->section;
    }

    public function setSection(?float $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getCrimpingHeight(): ?float
    {
        return $this->crimpingHeight;
    }

    public function setCrimpingHeight(?float $crimpingHeight): static
    {
        $this->crimpingHeight = $crimpingHeight;

        return $this;
    }

    public function getInsulationHeight(): ?float
    {
        return $this->insulationHeight;
    }

    public function setInsulationHeight(?float $insulationHeight): static
    {
        $this->insulationHeight = $insulationHeight;

        return $this;
    }

    public function getCrimpingWidth(): ?float
    {
        return $this->crimpingWidth;
    }

    public function setCrimpingWidth(?float $crimpingWidth): static
    {
        $this->crimpingWidth = $crimpingWidth;

        return $this;
    }

    public function getInsulationWidth(): ?float
    {
        return $this->insulationWidth;
    }

    public function setInsulationWidth(?float $insulationWidth): static
    {
        $this->insulationWidth = $insulationWidth;

        return $this;
    }

    public function getStatus(): IOStatus
    {
        return $this->status;
    }

    public function setStatus(IOStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDateEntre(): ?\DateTimeInterface
    {
        return $this->dateEntre;
    }

    public function setDateEntre(\DateTimeInterface $dateEntre): static
    {
        $this->dateEntre = $dateEntre;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMeasure(): ?Measure
    {
        return $this->measure;
    }

    public function setMeasure(?Measure $measure): static
    {
        $this->measure = $measure;

        return $this;
    }

    public function getConformite(): ?string
    {
        return $this->conformite;
    }

    public function setConformite(?string $conformite): static
    {
        $this->conformite = $conformite;

        return $this;
    }

    public function getMachine(): ?int
    {
        return $this->machine;
    }

    public function setMachine(?int $machine): static
    {
        $this->machine = $machine;

        return $this;
    }

    public function getWithCahier(): ?string
    {
        return $this->withCahier;
    }

    public function setWithCahier(?string $withCahier): static
    {
        $this->withCahier = $withCahier;

        return $this;
    }

    #[ORM\PrePersist]
    public function setDateEntreValue(): void
    {
        $this->dateEntre = new \DateTime();
    }
} 