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

class ChangeProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { {
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
                    ]);
        }
    }    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
