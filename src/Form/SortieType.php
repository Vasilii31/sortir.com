<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Service\LieuService;
use App\Service\VilleService;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function __construct(private readonly LieuService $lieuService, private readonly VilleService $villeService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $lieux = $this->lieuService->getAllLieux();
        $villes = $this->villeService->getAllVilles();
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => 'Nom ',
                'attr' => [
                    'placeholder' => 'Randonnée',
                    'class' => 'form-control'
                ],
            ])
            ->add('datedebut', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
                'label' => 'Date début',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('datecloture', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
                'label' => 'Date début',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'required' => false,
                'label' => 'Nombre de places',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '8',
                ],
            ])
            ->add('duree', IntegerType::class, [
                'required' => false,
                'label' => 'Durée (en minutes)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '90',
                ],
            ])
            ->add('descriptionInfos', TextType::class, [
                'required' => false,
                'label' => 'Description et infos ',
                'attr' => [
                    'placeholder' => 'Parcours de 15km...',
                    'class' => 'form-control'
                ],
            ])
            ->add('ville', ChoiceType::class, [
                'choices' => $villes,
                'choice_label' => fn($ville) => $ville->getNomVille() ,
                'choice_value' => fn($ville) => $ville ? $ville->getId() : '',
                'placeholder' => 'Choisissez une ville',
                'required' => true,
                'label' => 'Ville',
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->add('lieu', ChoiceType::class, [
                'choices' => $lieux,
                'choice_label' => fn(Lieu $lieu) => $lieu->getNomLieu(),
                'choice_value' => fn(?Lieu $lieu) => $lieu ? $lieu->getId() : '',
                'choice_attr' => function ($lieu) {
                    if (!$lieu) return [];
                    return [
                        'data-ville-id' => $lieu->getVille()?->getId() ?? '',
                        'data-rue' => $lieu->getRue() ?? '',
                        'data-ville' => $lieu->getVille()?->getNomVille() ?? '',
                        'data-cp' => $lieu->getVille()?->getCodePostal() ?? '',
                        'data-latitude' => $lieu->getLatitude() ?? '',
                        'data-longitude' => $lieu->getLongitude() ?? '',
                    ];
                },
                'placeholder' => 'Choisissez un lieu',
                'required' => false,
                'label' => 'Lieu',
                'attr' => ['class' => 'form-control'],
            ])
           ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
