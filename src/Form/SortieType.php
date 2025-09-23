<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Service\LieuService;
use App\Service\SiteService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function __construct(private LieuService $lieuService){}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

    $lieux = $this->lieuService->getAllLieux();
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
                'choices' => $lieux, // tableau de Lieu
                'choice_label' => fn(Lieu $lieu) => $lieu->getVille()->getNomVille() . ' (' . $lieu->getVille()->getCodePostal() . ')',
                'choice_value' => fn(?Lieu $lieu) => $lieu ? $lieu->getId() : '',
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
                'placeholder' => 'Choisissez un lieu',
                'required' => false,
                'label' => 'Lieu',
                'attr' => ['class' => 'form-control'],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
