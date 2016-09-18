<?php

namespace AbstractBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $em = $client->getContainer()->get('doctrine')->getEntityManager();
        $user = $em->getRepository('UserBundle:User')->findOneByUsername('hagar');

        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main_firewall', $user->getRoles());
        self::$kernel->getContainer()->get('security.context')->setToken($token);

        $session = $client->getContainer()->get('session');
        $session->set('_security_' . 'main_firewall', serialize($token));
        $session->save();

        $crawler = $client->request('GET', '/login/required/page/');

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }
}
