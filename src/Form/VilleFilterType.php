<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VilleFilterType extends AbstractType
{
    public function __construct()
    {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => 'Le nom de la ville contient :',
                'attr' => [
                    'placeholder' => 'Rechercher par nom',
                    'class' => 'form-control'
                ]])
                ->add('submit', SubmitType::class,[
                    'label' => 'Rechercher'
                ]);


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