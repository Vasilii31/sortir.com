<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Service\LieuService;
use App\Service\SiteService;
use App\Service\VilleService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function __construct(private readonly LieuService $lieuService, private readonly VilleService $villeService, private readonly SiteService $siteService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieux = $this->lieuService->getAllLieux();
        $villes = $this->villeService->getAllVilles();
        $sites = $this->siteService->getAllSites();

        // Récupérer la sortie actuelle si en mode edit
        $sortie = $options['data'] ?? null;
        $villeInitiale = null;

        if ($sortie && $sortie->getLieu()) {
            $villeInitiale = $sortie->getLieu()->getVille();
        }

        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => 'Nom ',
                'attr' => [
                    'placeholder' => 'Randonnée',
                    'class' => 'form-control'
                ],
            ])
            ->add('ville', ChoiceType::class, [
                'choices' => $villes,
                'choice_label' => fn($ville) => $ville->getNomVille(),
                'choice_value' => fn($ville) => $ville ? $ville->getId() : '',
                'placeholder' => 'Choisissez une ville',
                'required' => true,
                'label' => 'Ville',
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'data' => $villeInitiale, // <-- pré-remplit la ville en edit
            ])

            ->add('datedebut', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'label' => 'Date et heure de début',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('datecloture', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'label' => 'Date de fin',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'required' => true,
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
            ->add('descriptionInfos', TextareaType::class, [
                'required' => false,
                'label' => 'Description et infos ',
                'attr' => [
                    'placeholder' => 'Parcours de 15km...',
                    'class' => 'form-control',
                    'rows' => 5, // nombre de lignes visibles
                ],
            ])
            ->add('ville', ChoiceType::class, [
                'choices' => $villes,
                'choice_label' => fn($ville) => $ville->getNomVille(),
                'choice_value' => fn($ville) => $ville ? $ville->getId() : '',
                'placeholder' => 'Choisissez une ville',
                'required' => true,
                'label' => 'Ville',
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'data' => $villeInitiale, // <-- pré-remplit la ville en edit
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
                'required' => true,
                'label' => 'Lieu',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
            ->add('publier', SubmitType::class, [
                'label' => 'Publier',
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
