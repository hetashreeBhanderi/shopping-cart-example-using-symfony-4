<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addressDetails', TextType::class,[
                'label' => "Address"
            ])
            ->add('city', TextType::class)
            ->add('city', TextType::class)
            ->add('state', TextType::class)
            ->add('pincode', TextType::class)
            ->add('payment', SubmitType::class,[
                'label' => 'Proceed',
                'attr'=>array('class' => 'btn btn-lg btn-primary')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
