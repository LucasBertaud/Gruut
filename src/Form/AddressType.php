<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Quel nom souhaitez-vous donner à votre adresse ?',
                'attr' => [
                    'placeholder' => 'Entrez le nom que vous voulez donner à votre adresse.'
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Entrez votre prénom.'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom.'
                ]
            ])
            ->add('company', TextType::class, [
                'required' => false,
                'label' => 'Nom de votre société'.' '.'( Facultatif )',
                'attr' => [
                    'placeholder' => 'Entrez votre société. ( Facultatif )'
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Entrez votre adresse.'
                ]
            ])
            ->add('postal', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'Entrez votre code postal.'
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Entrez votre ville'
                ]
            ])
            ->add(
                'country',
                CountryType::class,
                [
                    'label' => 'Pays',
                ]
            )
            ->add('phone', TelType::class, [
                'label' => 'Votre téléphone',
                'attr' => [
                    'placeholder' => 'Entrez votre numéro de téléphone.',
                    'maxlength' => 10
                ]
                
            ])
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Enregistrer'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
