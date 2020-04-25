<?php

namespace App\Form;

use App\Entity\Questions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('questionTitle', TextType::class,[
                'label' => 'Que.',
                'attr'=>array('class'=>'form-control',
                    'placeholder' => 'Please write question here..',
                    'disabled' => false),
                'error_mapping'=> 'Please login first to ask questions!',
            ])
            ->add('submit', SubmitType::class,[
                'label'=>'Save',
                'attr'=>array('class'=>'btn btn-sm btn-primary',
                    'disabled' => false),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Questions::class,
        ]);
    }
}
