<?php

namespace App\Form;

use App\Entity\Complaint;
use App\Enum\ComplaintStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComplaintResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => ComplaintStatus::PENDING,
                    'Processing' => defined('App\\Enum\\ComplaintStatus::PROCESSING') ? ComplaintStatus::PROCESSING : 'PROCESSING',
                    'Resolved' => ComplaintStatus::RESOLVED,
                    'Rejected' => ComplaintStatus::REJECTED,
                ],
                'attr' => ['class' => 'form-select'],
                'label' => 'Status'
            ])
            ->add('adminResponse', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your response',
                    'rows' => 5
                ],
                'label' => 'Response',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Complaint::class,
        ]);
    }
} 