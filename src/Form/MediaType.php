<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * @extends AbstractType<Media>
 */
class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Image',
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (jpeg, png, gif).',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux. La taille maximale autorisée est de 2 Mo.',
                    ]),
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
        ;

        if ((bool) $options['is_admin']) {
            $builder
                ->add('user', EntityType::class, [
                    'label' => 'Utilisateur',
                    'required' => false,
                    'class' => User::class,
                    'choice_label' => 'name',
                ])
                ->add('album', EntityType::class, [
                    'label' => 'Album',
                    'required' => false,
                    'class' => Album::class,
                    'choice_label' => 'name',
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'is_admin' => false,
        ]);
    }
}
