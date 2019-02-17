<?php

namespace Bitfumes\MailchimpNewsletters;

use Illuminate\Support\Facades\Facade;

class MailchimpNewsletterFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'mailchimp';
    }
}
