<?php
namespace App\Form;

use App\Entity\Status;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Status Name'])
            // Optionally add a checkbox to mark a status as default (immutable)
            ->add('isDefault', CheckboxType::class, [
                'label'    => 'Is Default?',
                'required' => false,
                'disabled' => $options['is_edit'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Status::class,
            'is_edit' => false,  // Add this option to control the disabling of the 'isDefault' field on edit forms
        ]);
    }
}
