<?php

namespace App\Form;

use App\Entity\Employee;
use App\Enum\EmployeeHours;
use App\Enum\EmployeePosition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class EmployeeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('birthdate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('employedSince', DateType::class, [
                'label' => 'Employé depuis',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('employedUntil', DateType::class, [
                'label' => 'Employé jusqu\'à',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('hours', EnumType::class, [
                'class' => EmployeeHours::class,
                'label' => 'Heures',
                'required' => true,
                'choice_label' => fn ($choice) => $choice->value,
            ])          
            ->add('salary', IntegerType::class, [
                'label' => 'Salaire',
                'required' => true,
            ])
            ->add('position', EnumType::class, [
                'class' => EmployeePosition::class,
                'label' => 'Poste',
                'required' => true,
                'choice_label' => fn ($choice) => $choice->value,
            ])          
            ->add('manager', EntityType::class, [
                'class' => Employee::class,
                'choice_label' => function (Employee $e) 
                {
                    return sprintf('%s %s (%s)', $e->getFirstname(), $e->getLastname(), $e->getPosition()?->value ?? '');
                },                
                'required' => false,
                'placeholder' => 'Aucun'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}
