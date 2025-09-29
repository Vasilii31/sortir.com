<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;

class UserImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csvFile', FileType::class, [
                'label' => 'Fichier CSV',
                'mapped' => false, // pas lié à une entité
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['text/plain', 'text/csv', 'text/x-csv', 'application/csv'],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier CSV valide.',
                    ])
                ],
            ]);
    }

}