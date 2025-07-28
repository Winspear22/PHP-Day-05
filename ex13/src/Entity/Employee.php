<?php

namespace App\Entity;
use App\Enum\EmployeeHours;

use App\Enum\EmployeePosition;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(max: 50)]
    private ?string $firstname = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(max: 50)]
    private ?string $lastname = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Email invalide.")]
    #[Assert\Length(max: 100)]
    private ?string $email = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de naissance est obligatoire.")]
    #[Assert\LessThanOrEqual("today", message: "La date de naissance doit être dans le passé.")]
    #[Assert\GreaterThanOrEqual("1945-01-01", message: "La date de naissance ne peut pas être avant 1945.")]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $active = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date d'embauche est obligatoire.")]
    private ?\DateTimeInterface $employedSince = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\Expression(
        "this.getEmployedUntil() === null or this.getEmployedUntil() >= this.getEmployedSince()",
        message: "La date de fin doit être après la date d'embauche."
    )]
    private ?\DateTimeInterface $employedUntil = null;

    #[ORM\Column(type: 'string', enumType: EmployeeHours::class)]
    #[Assert\NotNull(message: "Choisir les heures de travail.")]
    private ?EmployeeHours $hours = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive(message: "Le salaire doit être positif.")]
    private ?int $salary = null;

    #[ORM\Column(type: 'string', enumType: EmployeePosition::class)]
    #[Assert\NotNull(message: "Le poste doit être précisé.")]
    private ?EmployeePosition $position = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'employees')]
    private ?self $manager = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'manager')]
    private Collection $employees;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }
    
#[Assert\Callback]
public function validateBusinessRules(ExecutionContextInterface $context): void
{
    // Pas de manager pour le CEO
    if ($this->position?->value === 'ceo' && $this->manager !== null) {
        $context->buildViolation("Le CEO ne peut pas avoir de manager.")
            ->atPath('manager')
            ->addViolation();
    }

    // Un employé (hors CEO) doit obligatoirement avoir un manager
    if ($this->position?->value !== 'ceo' && $this->manager === null) {
        $context->buildViolation("Un manager doit être choisi pour ce poste.")
            ->atPath('manager')
            ->addViolation();
    }

    // Le COO ne peut avoir que le CEO comme manager
    if ($this->position?->value === 'coo' && $this->manager?->getPosition()?->value !== 'ceo') {
        $context->buildViolation("Le COO doit avoir le CEO comme manager.")
            ->atPath('manager')
            ->addViolation();
    }

    // Un employé ne peut pas être son propre manager
    if ($this->manager && $this->manager === $this) {
        $context->buildViolation("Un employé ne peut pas être son propre manager.")
            ->atPath('manager')
            ->addViolation();
    }

    if ($this->birthdate) 
    {
        if ($this->birthdate && $this->employedSince)
        {
            $interval = $this->employedSince->diff($this->birthdate);
            if ($interval->y < 18)
            {
                $context->buildViolation('Le salarié doit avoir au moins 18 ans.')
                    ->atPath('birthdate')
                    ->addViolation();
            }
        }
    }
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTime $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getEmployedSince(): ?\DateTime
    {
        return $this->employedSince;
    }

    public function setEmployedSince(\DateTime $employedSince): static
    {
        $this->employedSince = $employedSince;

        return $this;
    }

    public function getEmployedUntil(): ?\DateTime
    {
        return $this->employedUntil;
    }

    public function setEmployedUntil(\DateTime $employedUntil): static
    {
        $this->employedUntil = $employedUntil;

        return $this;
    }

    public function getHours(): ?EmployeeHours
    {
        return $this->hours;
    }

    public function setHours(EmployeeHours $hours): static
    {
        $this->hours = $hours;

        return $this;
    }

    public function getSalary(): ?int
    {
        return $this->salary;
    }

    public function setSalary(int $salary): static
    {
        $this->salary = $salary;

        return $this;
    }

    public function getPosition(): ?EmployeePosition
    {
        return $this->position;
    }

    public function setPosition(EmployeePosition $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getManager(): ?self
    {
        return $this->manager;
    }

    public function setManager(?self $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(self $employee): static
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->setManager($this);
        }

        return $this;
    }

    public function removeEmployee(self $employee): static
    {
        if ($this->employees->removeElement($employee)) {
            // set the owning side to null (unless already changed)
            if ($employee->getManager() === $this) {
                $employee->setManager(null);
            }
        }

        return $this;
    }
}
