<?php

namespace App\Form;

use App\Entity\Todo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class TodoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 4,
                        'minMessage' => 'Veuillez remplir un nom d\'au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Veuillez remplir un nom de moins de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class)
            ->add('picture_name', FileType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'À faire' => 'À faire',
                    'En cours' => 'En cours',
                    'Terminé' => 'Terminé'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
        ]);
    }
}
