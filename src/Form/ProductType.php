<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('availavleQty')
            ->add('maxQtyPerUserPerCart')
            ->add('description')
            ->add('productImage', FileType::class,[
                'data_class' => null,
                'mapped' => true,
                'constraints' => new File([
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg'
                    ],
                    'mimeTypesMessage' => 'Please enter valid Image'
                ])
            ])
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'detailName'
            ])
            ->add('brand', EntityType::class,[
                'class' => Brand::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
