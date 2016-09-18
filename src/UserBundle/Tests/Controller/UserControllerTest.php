<?php

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $container;
    
    public function __construct() {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        $this->container = self::$kernel->getContainer();
    }
    
    private function logIn()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Log in')->form(array(
            '_username'  => 'admin',
            '_password'  => 'admin',
        ));
        
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        $crawler = $this->client->followRedirects();

        $this->assertRegExp('/\/users/', $this->client->getResponse()->headers->get('location'));
    }
    
    public function testListUsers()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/users');
        
        $this->assertContains('User list', $this->client->getResponse()->getContent());
    }
    
    public function testCreateNewUser()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/users/new');
        
        $this->assertContains('User creation', $this->client->getResponse()->getContent());
        $randomval =  rand();
        // Fill in the form and submit it
        $form = $crawler->selectButton('Submit')->form(array(
            'user[email]'  => 'test@localhost.com' . $randomval,
            'user[username]' => 'name' . $randomval,
            'user[password][first]' => '123456',
            'user[password][second]' => '123456',
            'user[role]'    => 'ROLE_PATIENT'
        ));
        
        $this->client->submit($form);
        
        $em = $this->container->get('doctrine')->getManager();
        $user = $em->getRepository('UserBundle:User')->findOneByUsername('name' . $randomval);

        $this->assertInstanceOf('UserBundle\Entity\User', $user);
        
        $this->assertContains($this->container->get('router')->generate('users_show', array('id' => $user->getId())), $this->client->getRequest()->getUri());
    }

    public function testUpdateUser() {
        
        $this->logIn();
        
        $em = $this->container->get('doctrine')->getManager();
        $randomval =  rand();
        $user = new \UserBundle\Entity\User();
        $user->setEmail('email' . $randomval . '@localhost.com')
            ->setUsername('name' . $randomval)
            ->setPassword('123456')
            ->addRole('ROLE_PATIENT');
        $em->persist($user);
        $em->flush();
        $em->refresh($user);
        
        $crawler = $this->client->request('GET', '/users/' . $user->getId() . '/edit');
        
        $this->assertContains('User edit', $this->client->getResponse()->getContent());
        
        // Fill in the form and submit it
        $form = $crawler->selectButton('Submit')->form(array(
            'user[email]'  => 'test@localhost.com' . $randomval,
            'user[username]' => 'name' . $randomval,
        ));
       
        $this->client->submit($form);
        $user = $em->getRepository('UserBundle:User')->findOneByEmail('test@localhost.com' . $randomval);
        $this->assertInstanceOf('UserBundle\Entity\User', $user);
    }
    
    public function testDeleteUser() {
        
        $this->logIn();
        
        $em = $this->container->get('doctrine')->getManager();
        $randomval =  rand();
        $user = new \UserBundle\Entity\User();
        $user->setEmail('email' . $randomval . '@localhost.com')
            ->setUsername('name' . $randomval)
            ->setPassword('123456')
            ->addRole('ROLE_PATIENT');
        $em->persist($user);
        $em->flush();
        $em->refresh($user);
        
        $crawler = $this->client->request('GET', '/users/' . $user->getId());
        // Delete the entity
        $this->client->submit($crawler->selectButton('Delete')->form());
        
        // Check the entity has been delete on the list
        $user = $em->getRepository('UserBundle:User')->findOneByEmail('email' . $randomval . '@localhost.com');
        $this->assertNotInstanceOf('UserBundle\Entity\User', $user);
    }
}
