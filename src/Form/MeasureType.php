<?php

namespace App\Form;

use App\Entity\Measure;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeasureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('section', NumberType::class, [
                'label' => 'Section',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter section value'
                ]
            ])
            ->add('crimpingHeight', NumberType::class, [
                'label' => 'Crimping Height',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter crimping height value'
                ]
            ])
            ->add('insulationHeight', NumberType::class, [
                'label' => 'Insulation Height',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter insulation height value'
                ]
            ])
            ->add('crimpingWidth', NumberType::class, [
                'label' => 'Crimping Width',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter crimping width value'
                ]
            ])
            ->add('insulationWidth', NumberType::class, [
                'label' => 'Insulation Width',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter insulation width value'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Measure::class,
        ]);
    }
} 