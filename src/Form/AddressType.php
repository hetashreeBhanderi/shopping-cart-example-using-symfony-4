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
                'label' => "Address",
                'attr' => array('class'=>'form-control',
                    'placeholder'=>'Full address')
            ])
            ->add('city', TextType::class,[
                'attr'=> array('class'=>'form-control',
                    'placeholder'=>'Town/City')
            ])
            ->add('state', TextType::class,[
                'attr'=> array('class'=>'form-control',
                    'placeholder'=>'State')
            ])
            ->add('pincode', TextType::class,[
                'attr'=> array('class'=>'form-control',
                    'placeholder'=>'PinCode/PstalCode')
            ])
            ->add('payment', SubmitType::class,[
                'label' => 'Deliver to this Address',
                'attr'=>array('class' => 'submit check_out btn')
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
