<?php

namespace App\Console\Commands;

use App\Services\ArticleService;
use App\Services\NewsProviders\GuardianProvider;
use App\Services\NewsProviders\NewsAPIProvider;
use App\Services\NewsProviders\NYTimesProvider;
use Illuminate\Console\Command;

class FetchArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:fetch {--provider= : Fetch from specific provider (newsapi, guardian, nytimes)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from news providers and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle(ArticleService $articleService): int
    {
        $this->info('Starting to fetch articles...');

        $providers = $this->getProviders();
        $totalArticles = 0;

        foreach ($providers as $provider) {
            $this->info("Fetching from {$provider->getProviderName()}...");
            
            $count = $articleService->fetchAndStore($provider, [
                'pageSize' => 50,
            ]);
            
            $totalArticles += $count;
            $this->info("Stored {$count} articles from {$provider->getProviderName()}");
        }

        $this->info("Total articles fetched: {$totalArticles}");
        return Command::SUCCESS;
    }

    /**
     * Get the list of providers to fetch from
     *
     * @return array
     */
    private function getProviders(): array
    {
        $providerOption = $this->option('provider');

        if ($providerOption) {
            return match ($providerOption) {
                'newsapi' => [app(NewsAPIProvider::class)],
                'guardian' => [app(GuardianProvider::class)],
                'nytimes' => [app(NYTimesProvider::class)],
                default => [],
            };
        }

        return [
            app(NewsAPIProvider::class),
            app(GuardianProvider::class),
            app(NYTimesProvider::class),
        ];
    }
}
