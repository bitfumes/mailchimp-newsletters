<?php

return [
    /*
     * The API key of a MailChimp account. You can find yours at
     * https://us10.admin.mailchimp.com/account/api-key-popup/.
     */
    'apiKey' => env('MAILCHIMP_APIKEY'),
    /*
     * If no list name is provided, then this list will be used.
     */
    'defaultList' => 'test',
    /*
     * Define list related properties.
     */
    'lists' => [
        /*
         * This key is used to identify this list. It can be used
         * as the listName parameter provided in the various methods.
         *
         * You can set it to any string you want and you can add
         * as many lists as you want.
         */
        'test' => [
            /*
             * A MailChimp list id. Check the MailChimp docs if you don't know
             * how to get this value:
             * http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id.
             */
            'id' => env('MAILCHIMP_LIST_ID'),
        ],
    ],
    /*
     * If you're having trouble with https connections, set this to false.
     */
    'ssl' => true,
];
