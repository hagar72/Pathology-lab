<?php

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends WebTestCase
{
    private $client;
    
    public function __construct() {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
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
        
        $em = $this->client->getContainer()->get('doctrine')->getEntityManager();
        $user = $em->getRepository('UserBundle:User')->findOneByUsername('name' . $randomval);

        $this->assertInstanceOf('UserBundle\Entity\User', $user);
        
        $this->assertTrue(getRequest()->getUri('/users/' . $user->getId()), 'response is a redirect to /users');
    }

    public function testUpdateUser() {
        
        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("Test")')->count(), 'Missing element td:contains("Test")');

        // Edit the entity
        $crawler = $this->client->click($crawler->selectLink('Edit')->link());

        $form = $crawler->selectButton('Update')->form(array(
            'userbundle_user[field_name]'  => 'Foo',
            // ... other fields to fill
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertGreaterThan(0, $crawler->filter('[value="Foo"]')->count(), 'Missing element [value="Foo"]');

        // Delete the entity
        $this->client->submit($crawler->selectButton('Delete')->form());
        $crawler = $this->client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Foo/', $this->client->getResponse()->getContent());
    }
}
