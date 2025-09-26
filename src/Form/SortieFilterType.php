<?php

namespace App\Form;

use App\Entity\Site;
use App\Service\SiteService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterType extends AbstractType
{
    public function __construct(private SiteService $siteService){}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sites = $this->siteService->getAllSites();
        $builder
            ->add('site', ChoiceType::class, [
                'choices' => $sites,
                'choice_label' => fn(Site $site) => $site->getNomSite(),
                'choice_value' => fn(?Site $site) => $site ? $site->getId() : '',
                'placeholder' => 'Choisissez un site',
                'required' => false,
                'label' => 'Site :',
                'attr' => [
                    'class' => 'form-control custom-select',
                ],
            ])
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
            ->add('sortieCreator', CheckboxType::class, [
                'required'   => false,
                'mapped'     => false,
                'label'      => "Sorties dont je suis l'organisateur",
                'row_attr'   => ['class' => 'form-check'],
                'label_attr' => ['class' => 'form-check-label'],
                'attr'       => ['class' => 'form-check-input'],
            ])
            ->add('sortieInscrit', CheckboxType::class, [
                'required'   => false,
                'mapped'     => false,
                'label'      => "Sorties auxquelles je suis inscrit",
                'row_attr'   => ['class' => 'form-check'],
                'label_attr' => ['class' => 'form-check-label'],
                'attr'       => ['class' => 'form-check-input'],
            ])
            ->add('sortieNonInscrit', CheckboxType::class, [
                'required'   => false,
                'mapped'     => false,
                'label'      => "Sorties auxquelles je ne suis pas inscrit",
                'row_attr'   => ['class' => 'form-check'],
                'label_attr' => ['class' => 'form-check-label'],
                'attr'       => ['class' => 'form-check-input'],
            ])
            ->add('sortiesPassees', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => "Sorties passées",
                'row_attr' => ['class' => 'form-check'],
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
            ])

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
