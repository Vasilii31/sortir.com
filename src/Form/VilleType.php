<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_ville', TextType::class, [
                'required' => true,
                'label' => 'Nom de la ville',
                'attr' => [
                    'placeholder' => 'Montauban',
                    'class' => 'form-control'
                ],
            ])
            ->add('code_postal', TextType::class,[
                'required' => true,
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => '82000',
                    'class' => 'form-control'],
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
