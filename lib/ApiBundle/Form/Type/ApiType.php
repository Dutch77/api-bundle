<?php

namespace Asyf\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('use_fields', [])
            ->setDefault('csrf_protection', false);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if (isset($options['use_fields']) && is_array($options['use_fields']) && $options['use_fields']) {
            foreach ($builder->all() as $name => $field) {
                if (!in_array($name, $options['use_fields'])) {
                    $builder->remove($name);
                }
            }
        }
    }
}