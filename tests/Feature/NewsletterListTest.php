<?php

namespace Bitfumes\MailchimpNewsletters\Tests\Feature;

use Bitfumes\MailchimpNewsletters\Tests\TestCase;
use Bitfumes\MailchimpNewsletters\NewsletterList;

class NewsletterListTest extends TestCase
{
    protected $newsletterList;

    public function setUp():void
    {
        parent::setUp();
        $this->newsletterList = new NewsletterList('subscriber', ['id' => 'abc123']);
    }

    /** @test */
    public function it_can_determine_the_name_of_the_list()
    {
        $this->assertSame('subscriber', $this->newsletterList->getName());
    }

    /** @test */
    public function it_can_determine_the_id_of_the_list()
    {
        $this->assertSame('abc123', $this->newsletterList->getId());
    }
}
