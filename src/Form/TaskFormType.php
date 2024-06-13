<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\Status;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskFormType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name', TextType::class, [
          'label' => 'Task Name',
      ])
      ->add('description', TextareaType::class, [
          'label' => 'Description',
          'required' => false,
      ])
      ->add('status', EntityType::class, [
          'class' => Status::class,
          'choice_label' => 'name',
          'label' => 'Status',
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class' => Task::class,
    ]);
  }
}
