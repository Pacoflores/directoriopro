<?php

namespace Application\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilder;


class RegisterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name')
        		->add('email', 'email')
				->add('category_id')
				->add('pass')
				/*
        		->add('pass', 'repeated', array(
				           'first_name' => 'password',
				           'second_name' => 'confirm',
				           'type' => 'password',
							'invalid_message' => 'Las contraseñas tienen que coincidir',
				        ))
				*/
				->add('unemployed')		
				->add('freelance')
				->add('search_team')
				->add('location')
				->add('city_id')
				->add('country_id');
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Application\UserBundle\Entity\User');
    }

    public function getName()
    {
        return 'register';
    }
}