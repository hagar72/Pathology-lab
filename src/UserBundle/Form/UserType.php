<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Constants\Actions;

class UserType extends AbstractType
{
    /**
     * @var string
     */
    private $action;
    
    public function __construct($action = Actions::Create) {
        $this->action = $action;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email')
            ->add('username');
        
        if(Actions::Create == $this->action) {
            $builder->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ));
                    
            $builder->add('role', 'choice', array(
                'choices'  => array(
                    'ROLE_ADMIN' => 'Admin',
                    'ROLE_PATIENT' => 'Patient',
                ),
            ));
        }
        $builder->add('enabled', 'checkbox', array(
            'label'    => 'Active?',
            'required' => false,
            'data' => true,
        ))
        ->add('submit', 'submit');
        
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\User'
        ));
    }
}
