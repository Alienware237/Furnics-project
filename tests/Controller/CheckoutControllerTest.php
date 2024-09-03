<?php

namespace okpt\furnics\project\tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use okpt\furnics\project\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Entity\Cart;
use okpt\furnics\project\Entity\CartItem;
use okpt\furnics\project\Entity\Orders;

class CheckoutControllerTest extends WebTestCase
{
    private $entityManager;
    private $schemaTool;
    private $metadata;
    private $client;
    private $passwordHasher;

    protected function setUp(): void
    {
        //self::bootKernel();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->schemaTool = new SchemaTool($this->entityManager);
        $this->metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // Drop and recreate the schema
        $this->schemaTool->dropSchema($this->metadata);
        $this->schemaTool->createSchema($this->metadata);

        // Run the createArticleTestCommand to insert articles
        $application = new Application(self::$kernel);
        $command = $application->find('app:create-article');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Insert a user, create a cart with an item, and create an order for the login tests
        $this->insertTestUserCartAndOrder();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager) {
            $this->schemaTool->dropSchema($this->metadata);
            $this->entityManager->close();
            $this->entityManager = null; // avoid memory leaks
        }

        parent::tearDown();
    }

    private function insertTestUserCartAndOrder(): void
    {
        // Create and persist a user
        $user = new User();
        $user->setLastName('Doe');
        $user->setFirstName('John');
        $user->setEmail('john.doe@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setSalutation('Mr.');
        $user->setStreet('Main Street');
        $user->setHouseNumber(123);
        $user->setZipCode('12345');
        $user->setCity('Anytown');
        $user->setCountry('Countryland');
        $user->setPhone('1234567890');

        $this->entityManager->persist($user);

        // Create and persist a cart for the user
        $cart = new Cart();
        $cart->setUser($user);
        $this->entityManager->persist($cart);

        $article = $this->entityManager->getRepository(Article::class)->findOneBy(['articleId' => 1]);

        // Create and persist an item for the cart
        $cartItem = new CartItem();
        $cartItem->setArticle($article); // Set the Article entity
        $cartItem->setQuantity(1);
        $cartItem->setDetailsOfChoice(''); // Set details of choice if needed
        $cart->addCartItem($cartItem);
        $this->entityManager->persist($cartItem);

        // Create and persist an order for the user
        $order = new Orders();
        $order->setHouseNumber($user->getHouseNumber());
        $order->setStreet($user->getStreet());
        $order->setCity($user->getCity());
        $order->setCountry($user->getCountry());
        $order->setPhone($user->getPhone());
        $order->setEmail($user->getEmail());
        $order->setName($user->getFirstName() . ' ' . $user->getLastName());
        $order->setUser($user);
        $this->entityManager->persist($order);

        // Flush all entities to the database
        $this->entityManager->flush();
    }

    public function testUserRegistration()
    {
        // Simulate user registration
        $crawler = $this->client->request('GET', '/user/register');

        // Check the registration page response
        $this->assertResponseIsSuccessful();

        // Find the form and fill it
        $form = $crawler->selectButton('Register')->form([
            'registration[lastName]' => 'Doe',
            'registration[firstName]' => 'John',
            'registration[email]' => 'john.doe2@example.com', // Different email to avoid conflict with pre-inserted user
            'registration[password][first]' => 'password123',
            'registration[password][second]' => 'password123',
            'registration[salutation]' => 'Mr.',
            'registration[street]' => 'Main Street',
            'registration[houseNumber]' => 123,
            'registration[zipCode]' => '12345',
            'registration[city]' => 'Anytown',
            'registration[country]' => 'Countryland',
            'registration[phone]' => '1234567890',
        ]);

        // Submit the form
        $this->client->submit($form);

        // Check if the response is a redirect to the login page
        $this->assertTrue($this->client->getResponse()->isRedirect(), 'Registration form submission did not result in a redirect.');

        // Follow the redirect
        $this->client->followRedirect();

        // Check that the redirected page is the login page
        $this->assertSelectorExists('form[name="login"]');

        // Verify the user is created in the database
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'john.doe2@example.com']);
        $this->assertNotNull($user, 'User registration failed.');
    }

    public function testLoginAndCheckoutProcess()
    {
        // Simulate user login
        $crawler = $this->client->request('GET', '/login');

        // Check the login page response
        $this->assertResponseIsSuccessful();

        // Find the form and fill it
        $form = $crawler->selectButton('Login')->form([
            'login[email]' => 'john.doe@example.com',
            'login[password]' => 'password123',
        ]);

        // Submit the form
        $this->client->submit($form);

        // Check if the response is a redirect
        $this->assertTrue($this->client->getResponse()->isRedirect(), 'Login form submission did not result in a redirect.');

        // Follow the redirect
        $this->client->followRedirect();

        // Test accessing the checkout page and getting redirected based on current place
        $crawler = $this->client->request('GET', '/checkout');
        $response = $this->client->getResponse();

        // Check if the response is a redirect
        if ($response->isRedirect()) {
            $this->client->followRedirect();
            $this->assertSelectorExists('.shopping-cart-page');

            // Proceed to delivery address
            $crawler = $this->client->request('GET', '/checkout/delivery_address');
            $this->assertResponseIsSuccessful();
            $this->assertSelectorExists('form#delivery_address_form');

            // Fill the delivery address form with a European country to trigger the tax number field
            $form = $crawler->selectButton('Submit')->form([
                'delivery_address[name]' => 'John Doe',
                'delivery_address[phone]' => '1234567890',
                'delivery_address[email]' => 'john.doe@example.com',
                'delivery_address[country]' => 'Germany-Country', // EU country to trigger the tax number field
                'delivery_address[city]' => 'Berlin',
                'delivery_address[street]' => 'Sample Street',
                'delivery_address[houseNumber]' => '123',
                //'delivery_address[taxNumber]' => '123456789', // Required for EU countries
            ]);

            $this->client->submit($form);
            $this->client->followRedirect();
        } else {
            // Debugging: Output the status code and response
            echo $response->getStatusCode();
            echo $response->getContent();
        }

        // Now the order should be in 'summary_for_purchase' state
        $crawler = $this->client->request('GET', '/checkout');
        $response = $this->client->getResponse();

        // Assuming 'summary_for_purchase' redirects to summary handling
        if ($response->isRedirect()) {
            $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
            $this->assertSelectorExists('form#summary_form');

            $form = $crawler->selectButton('Submit')->form();
            $this->client->submit($form);
            $this->client->followRedirect();
        } else {
            // Debugging: Output the status code and response
            echo $response->getStatusCode();
            //echo $response->getContent();
        }

        // Now the order should be in 'ordered' state
        $crawler = $this->client->request('GET', '/checkout');
        $response = $this->client->getResponse();

        // Assuming 'ordered' redirects to thank you page
        if ($response->isRedirect()) {
            $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
            $this->assertSelectorExists('.thank-you-page');
        } else {
            // Debugging: Output the status code and response
            echo $response->getStatusCode();
            //echo $response->getContent();
        }
    }
}
