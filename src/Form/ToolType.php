<?php

namespace App\Form;

use App\Entity\Tool;
use App\Enum\DescriptionType;
use App\Enum\ManufacturerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;

class ToolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'Tool ID',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter unique tool ID',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a tool ID',
                    ]),
                ],
            ])
            ->add('description', EnumType::class, [
                'class' => DescriptionType::class,
                'label' => 'Description',
                'required' => true,
                'placeholder' => 'Select description',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a description',
                    ]),
                ],
            ])
            ->add('manufacturer', EnumType::class, [
                'class' => ManufacturerType::class,
                'label' => 'Manufacturer',
                'required' => true,
                'placeholder' => 'Select manufacturer',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a manufacturer',
                    ]),
                ],
            ])
            ->add('armoire', ChoiceType::class, [
                'label' => 'Armoire',
                'required' => true,
                'choices' => [
                    'Armoire 1' => '1',
                    'Armoire 2' => '2',
                    'Armoire 3' => '3',
                    'Armoire 4' => '4',
                    'Armoire 5' => '5',
                ],
                'placeholder' => 'Select armoire',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select an armoire',
                    ]),
                    new Choice([
                        'choices' => ['1', '2', '3', '4', '5'],
                        'message' => 'Please select a valid armoire',
                    ]),
                ],
            ])
            ->add('dnas', ChoiceType::class, [
                'label' => 'DNAS',
                'required' => true,
                'choices' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                ],
                'placeholder' => 'Select DNAS',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a DNAS',
                    ]),
                    new Choice([
                        'choices' => ['A', 'B', 'C', 'D'],
                        'message' => 'Please select a valid DNAS',
                    ]),
                ],
            ])
            ->add('emplacement', ChoiceType::class, [
                'label' => 'Emplacement',
                'required' => true,
                'choices' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'placeholder' => 'Select emplacement',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select an emplacement',
                    ]),
                    new Choice([
                        'choices' => ['1', '2', '3', '4', '5', '6'],
                        'message' => 'Please select a valid emplacement',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tool::class,
        ]);
    }
} 