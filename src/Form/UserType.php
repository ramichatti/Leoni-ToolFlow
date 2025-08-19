<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'User ID',
                'attr' => [
                    'placeholder' => 'Enter user ID',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a user ID',
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'attr' => [
                    'placeholder' => 'Enter first name',
                ],
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'attr' => [
                    'placeholder' => 'Enter last name',
                ],
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Enter email address',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ]),
                ],
            ])
            ->add('tel', TextType::class, [
                'label' => 'Phone Number',
                'attr' => [
                    'placeholder' => 'Enter phone number',
                ],
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
                'attr' => [
                    'placeholder' => 'Enter address',
                ],
                'required' => false,
            ])
            ->add('role', EnumType::class, [
                'class' => Role::class,
                'label' => 'Role',
                'choice_label' => function (Role $role) {
                    return match ($role) {
                        Role::ROLE_MAINTENANCE => 'Maintenance',
                        Role::ROLE_ADMIN => 'Admin',
                        Role::ROLE_SUPER_ADMIN => 'Super Admin',
                    };
                },
            ]);
            
        // Add password field only for new users or when explicitly requested
        if ($options['require_password']) {
            $builder->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => [
                    'label' => 'Password',
                    'attr' => [
                        'placeholder' => 'Enter password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => [
                        'placeholder' => 'Repeat password',
                    ],
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ]);
        } else {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Password (leave blank to keep current)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Enter new password',
                ],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => false,
        ]);
    }
} 