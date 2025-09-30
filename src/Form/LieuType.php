<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Service\VilleService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function __construct(
        private readonly VilleService $villeService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $villes = $this->villeService->getAllVilles();

        $builder
            ->add('nom_lieu', TextType::class, [
                'required' => true,
                'label' => 'Nom du lieu',
                'attr' => [
                    'placeholder' => 'Restaurant, Parc, etc.',
                    'class' => 'form-control',
                ],
            ])
            ->add('rue', TextType::class, [
                'required' => false,
                'label' => 'Rue',
                'attr' => [
                    'placeholder' => '123 rue de la République',
                    'class' => 'form-control',
                ],
            ])
            ->add('ville', ChoiceType::class, [
                'choices' => $villes,
                'choice_label' => fn(Ville $ville) => $ville->getNomVille() . ' (' . $ville->getCodePostal() . ')',
                'choice_value' => fn(?Ville $ville) => $ville ? $ville->getId() : '',
                'placeholder' => 'Choisissez une ville',
                'required' => true,
                'label' => 'Ville',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('latitude', NumberType::class, [
                'required' => false,
                'label' => 'Latitude',
                'attr' => [
                    'placeholder' => '48.8566',
                    'class' => 'form-control',
                    'step' => 'any',
                ],
            ])
            ->add('longitude', NumberType::class, [
                'required' => false,
                'label' => 'Longitude',
                'attr' => [
                    'placeholder' => '2.3522',
                    'class' => 'form-control',
                    'step' => 'any',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer le lieu',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}