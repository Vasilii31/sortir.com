<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'label' => 'Le nom de la sortie contient:',
                'attr' => [
                    'placeholder' => 'Rechercher par nom',
                    'class' => 'form-control'
                ],
            ])
            ->add('datedebut', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'Date début',
                'attr' => ['class' => 'form-control datepicker'],
            ])
            ->add('datecloture', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'Date clôture',
                'attr' => ['class' => 'form-control datepicker'],
            ])
//            ->add('etatsortie')
//            ->add('etat', EntityType::class, [
//                'class' => Etat::class,
//                'choice_label' => 'id',
//            ])
//            ->add('organisateur', EntityType::class, [
//                'class' => Participant::class,
//                'choice_label' => 'id',
//            ])
//            ->add('Lieu', EntityType::class, [
//                'class' => Lieu::class,
//                'choice_label' => 'id',
//            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher'
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'method' => 'GET',
            'data_class' => null,
        ]);
    }
}
