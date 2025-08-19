<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tel', TelType::class, [
                'label' => 'Phone Number',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter your phone number',
                    'pattern' => '[0-9]{8,15}',
                    'title' => 'Phone number should be between 8 and 15 digits',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9]{8,15}$/',
                        'message' => 'Phone number should contain only digits and be between 8 and 15 characters long',
                    ]),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter your address',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Address cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
} 