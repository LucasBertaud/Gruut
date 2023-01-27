<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email :', 
                'attr' => [
                    'placeholder' => 'Entrez votre adresse mail'
                ]
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
                'label' => 'Prénom :',
                'attr' => [
                    'placeholder' => 'Entrez votre prénom' 
                ]
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'label' => 'Nom :',
                'attr' => [
                    'placeholder' => 'Entrez votre nom' 
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'required' => true,
                'label' => 'Sexe :',
                "choices" => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe et la confirmation doivent être identique.',
                'required' => true,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'first_options' => ['label' => 'Votre mot de passe',
                'attr' => [
                    'placeholder' => 'Merci de saisir votre mot de passe',
                    'class' => 'form-label col-md-6'
                ]
                ],
                'second_options' => ['label' => 'Confirmez votre mot de passe',
                'attr' => [
                    'placeholder' => 'Merci de confirmez votre mot de passe',
                    'class' => 'form-label col-md-6'
                ]
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
