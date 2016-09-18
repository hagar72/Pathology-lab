<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Constants\Actions;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    /**
     * @var string
     */
    private $action;
    
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->action = (Actions::Edit == $options['action'])? Actions::Edit: Actions::Create;
        $builder
            ->add('email', EmailType::class)
            ->add('username', TextType::class);
        
        if(Actions::Create == $this->action) {
            $builder->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ));
                    
            $builder->add('role', ChoiceType::class, array(
                'choices'  => array(
                    'Admin' => 'ROLE_ADMIN',
                    'Patient' => 'ROLE_PATIENT',
                ),
                'choices_as_values' => true
            ));
        }
        $builder->add('enabled', CheckboxType::class, array(
            'label'    => 'Active?',
            'data' => true,
        ))
        ->add('submit', SubmitType::class);
        
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
