<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_ville')
            ->add('code_postal')
        ;
    }

//    public function buildForm(FormBuilderInterface $builder, array $options): void
//    {
//        $builder
//            ->add('nom', TextType::class, [
//                'required' => true,
//                'label' => 'Nom ',
//                'attr' => [
//                    'placeholder' => 'Randonnée',
//                    'class' => 'form-control'
//                ],
//            ]);
//
//
//    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
