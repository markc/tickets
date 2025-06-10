<?php

namespace App\Console\Commands;

use Facebook\WebDriver\WebDriverDimension;
use Illuminate\Console\Command;
use Symfony\Component\Panther\Client;

class TakeDocumentationScreenshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'screenshots:docs 
                            {--url=http://localhost:8000 : The base URL of the application}
                            {--page=* : Specific pages to screenshot}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take screenshots of documentation pages using Panther';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = $this->option('url');
        $specificPages = $this->option('page');

        $this->info('Starting screenshot capture with Panther...');

        // Create screenshots directory if it doesn't exist
        $screenshotDir = public_path('img/screenshots');
        if (! file_exists($screenshotDir)) {
            mkdir($screenshotDir, 0755, true);
            $this->info("Created directory: $screenshotDir");
        }

        // Set GeckoDriver path
        $geckodriverPath = base_path('drivers/geckodriver');

        // Initialize Panther client with Firefox
        $client = Client::createFirefoxClient($geckodriverPath, [
            '--headless',
            '--width=1280',
            '--height=800',
        ], [
            'capabilities' => [
                'acceptInsecureCerts' => true,
                'moz:firefoxOptions' => [
                    'prefs' => [
                        'network.dns.disableIPv6' => false,
                        'network.proxy.type' => 0,
                    ],
                ],
            ],
        ]);

        try {
            $this->info('Authentication disabled in local environment - taking screenshots...');

            // Define pages to screenshot
            $pages = [
                'login' => '/login',
                'dashboard' => '/dashboard',
                'admin-panel' => '/admin',
                'documentation-index' => '/admin/documentation',
                'documentation-markdown' => '/admin/documentation/markdown-guide',
                'documentation-faq' => '/admin/documentation/faq',
                'tickets' => '/tickets',
            ];

            // Filter pages if specific ones requested
            if (! empty($specificPages)) {
                $pages = array_intersect_key($pages, array_flip($specificPages));
            }

            foreach ($pages as $name => $path) {
                $this->info("Taking screenshot of: $name ($path)");

                try {
                    // Navigate to the page
                    $crawler = $client->request('GET', $baseUrl.$path);

                    // Wait for page to load
                    $client->waitFor('body', 5);

                    // Additional wait for dynamic content
                    sleep(2);

                    // Take screenshot
                    $screenshotPath = "$screenshotDir/$name.png";
                    $client->takeScreenshot($screenshotPath);

                    $this->info("✅ Screenshot saved: $screenshotPath");

                    // Also take a full page screenshot if it's documentation
                    if (str_contains($path, 'documentation')) {
                        // Execute JavaScript to get full page height
                        $height = $client->executeScript('return document.body.scrollHeight');

                        // Resize window to capture full content
                        $client->manage()->window()->setSize(new WebDriverDimension(1280, min($height, 5000)));

                        // Take full page screenshot
                        $fullPagePath = "$screenshotDir/{$name}-full.png";
                        $client->takeScreenshot($fullPagePath);

                        $this->info("✅ Full page screenshot saved: $fullPagePath");

                        // Reset window size
                        $client->manage()->window()->setSize(new WebDriverDimension(1280, 800));
                    }

                } catch (\Exception $e) {
                    $this->error("Failed to screenshot $name: ".$e->getMessage());
                }
            }

            // Take specific element screenshots for documentation
            $this->takeElementScreenshots($client, $baseUrl);

        } catch (\Exception $e) {
            $this->error('Screenshot process failed: '.$e->getMessage());
        } finally {
            $client->quit();
            $this->info('Screenshot capture completed!');
        }

        return Command::SUCCESS;
    }

    /**
     * Take screenshots of specific elements
     */
    protected function takeElementScreenshots(Client $client, string $baseUrl)
    {
        $this->info('Taking element-specific screenshots...');

        try {
            // Navigate to markdown guide
            $crawler = $client->request('GET', $baseUrl.'/admin/documentation/markdown-guide');
            $client->waitFor('#documentation-content', 5);
            sleep(2);

            // Screenshot code blocks
            if ($client->getCrawler()->filter('pre code')->count() > 0) {
                $element = $client->getCrawler()->filter('pre code')->first();
                $screenshotPath = public_path('img/screenshots/code-block-example.png');
                $client->takeScreenshot($screenshotPath, $element);
                $this->info('✅ Code block screenshot saved');
            }

            // Screenshot tables
            if ($client->getCrawler()->filter('table')->count() > 0) {
                $element = $client->getCrawler()->filter('table')->first();
                $screenshotPath = public_path('img/screenshots/table-example.png');
                $client->takeScreenshot($screenshotPath, $element);
                $this->info('✅ Table screenshot saved');
            }

            // Screenshot FAQ accordions if available
            $crawler = $client->request('GET', $baseUrl.'/admin/documentation/faq');
            $client->waitFor('#documentation-content', 5);
            sleep(2);

            if ($client->getCrawler()->filter('details')->count() > 0) {
                // Click to open first accordion
                $client->getCrawler()->filter('summary')->first()->click();
                sleep(1);

                $element = $client->getCrawler()->filter('details')->first();
                $screenshotPath = public_path('img/screenshots/accordion-example.png');
                $client->takeScreenshot($screenshotPath, $element);
                $this->info('✅ Accordion screenshot saved');
            }

        } catch (\Exception $e) {
            $this->error('Element screenshot failed: '.$e->getMessage());
        }
    }
}
