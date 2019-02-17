<?php

namespace Bitfumes\MailchimpNewsletters;

use Illuminate\Support\ServiceProvider;
use DrewM\MailChimp\MailChimp as MailChimpApi;

class MailchimpNewslettersServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'MailchimpNewsletters');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->mergeConfigFrom(__DIR__ . '/../config/mailchimpNewsletters.php', 'mailchimpNewsletter');
        $this->publishes([
            __DIR__ . '/../config/mailchimpNewsletters.php' => config_path('mailchimpNewsletter.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Mailchimp::class, function () {
            $mailChimp = new MailChimpApi(config('mailchimpNewsletter.apiKey'));
            $mailChimp->verify_ssl = config('mailchimpNewsletter.ssl', true);
            $configuredLists = NewsletterListCollection::createFromConfig(config('mailchimpNewsletter'));
            return new MailChimp($mailChimp, $configuredLists);
        });
        $this->app->alias(Mailchimp::class, 'mailchimpNewsletter');
    }
}
