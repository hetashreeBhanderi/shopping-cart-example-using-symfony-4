<?php

namespace App\Form;

use App\Entity\Answers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('answer', TextType::class,[
                'attr'=>array('class'=>'form-control',
                    'placeholder' => 'Please write answer for this question here..',
                    'disabled' => false),
                ])
            ->add('submit', SubmitType::class,[
                'attr'=>array('class'=>'btn btn-sm btn-primary',
                    'disabled' => false),
                'label' => 'Save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Answers::class,
        ]);
    }
}
