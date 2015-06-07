<?php

namespace Wallabag\CoreBundle\Form\Type;

use Wallabag\CoreBundle\Entity\EntrySearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntrySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',null,array(
                'required' => false,
            ))
            ->add('search','submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            // avoid to pass the csrf token in the url (but it's not protected anymore)
            'csrf_protection' => false,
            'data_class' => 'Wallabag\CoreBundle\Entity\EntrySearch'
        ));
    }

    public function getName()
    {
        return 'entry_search_type';
    }
}