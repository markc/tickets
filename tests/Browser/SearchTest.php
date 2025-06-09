<?php

namespace Tests\Browser;

use App\Models\Ticket;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class SearchTest extends PantherTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createPantherClient();
    }

    public function test_search_functionality()
    {
        // Navigate to login page
        $crawler = $this->client->request('GET', $_ENV['APP_URL'].'/login');

        // Take screenshot of login page
        $this->client->takeScreenshot('tests/Browser/screenshots/login-page.png');

        // Fill login form
        $form = $crawler->selectButton('Log in')->form();
        $form['email'] = 'admin@tikm.com';
        $form['password'] = 'password';
        $this->client->submit($form);

        // Wait for dashboard to load
        $this->client->waitFor('.max-w-7xl', 10);

        // Navigate to search
        $crawler = $this->client->request('GET', $_ENV['APP_URL'].'/search?q=test');

        // Take screenshot of search results
        $this->client->takeScreenshot('tests/Browser/screenshots/search-results.png');

        // Assert search form exists
        $this->assertSelectorExists('input[name="q"]');

        // Assert results section exists
        $this->assertSelectorExists('.max-w-7xl');

        echo "Search functionality test completed successfully!\n";
    }

    public function test_ticket_creation()
    {
        // Navigate to ticket creation page
        $crawler = $this->client->request('GET', $_ENV['APP_URL'].'/tickets/create');

        // Fill ticket form
        $form = $crawler->selectButton('Create Ticket')->form();
        $form['subject'] = 'Browser Test Ticket';
        $form['content'] = 'This is a test ticket created by Panther browser test';
        $form['office_id'] = '1';
        $form['ticket_priority_id'] = '1';

        $this->client->submit($form);

        // Wait for redirect
        $this->client->waitFor('.bg-green-100', 10);

        // Take screenshot
        $this->client->takeScreenshot('tests/Browser/screenshots/ticket-created.png');

        // Assert success message
        $this->assertSelectorTextContains('.bg-green-100', 'Ticket created successfully');

        echo "Ticket creation test completed successfully!\n";
    }

    protected function tearDown(): void
    {
        $this->client->quit();
        parent::tearDown();
    }
}
