<?php

namespace App\Entity;

use App\Enum\HoursEnum;
use App\Enum\PositionEnum;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $firstname = null;

    #[ORM\Column(length: 60)]
    private ?string $lastname = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?\DateTime $birthdate = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column]
    private ?\DateTime $employed_since = null;

    #[ORM\Column]
    private ?\DateTime $employed_until = null;

    #[ORM\Column(enumType: HoursEnum::class)]
    private ?HoursEnum $hours = null;

    #[ORM\Column]
    private ?int $salary = null;

    #[ORM\Column(enumType: PositionEnum::class)]
    private ?PositionEnum $position = null;

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
        return $this->employed_since;
    }

    public function setEmployedSince(\DateTime $employed_since): static
    {
        $this->employed_since = $employed_since;

        return $this;
    }

    public function getEmployedUntil(): ?\DateTime
    {
        return $this->employed_until;
    }

    public function setEmployedUntil(\DateTime $employed_until): static
    {
        $this->employed_until = $employed_until;

        return $this;
    }

    public function getHours(): ?HoursEnum
    {
        return $this->hours;
    }

    public function setHours(HoursEnum $hours): static
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

    public function getPosition(): ?PositionEnum
    {
        return $this->position;
    }

    public function setPosition(PositionEnum $position): static
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
