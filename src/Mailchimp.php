<?php

namespace Bitfumes\MailchimpNewsletters;

use DrewM\MailChimp\MailChimp as MailChimpApi;

class Mailchimp
{
    protected $mailChimp;
    protected $lists;

    public function __construct(MailChimpApi $mailChimp, NewsletterListCollection $lists)
    {
        $this->mailChimp = $mailChimp;
        $this->lists     = $lists;
    }

    public function subscribe(string $email, array $mergeFields = [], string $listName = '', array $options = [])
    {
        $list     = $this->lists->findByName($listName);
        $options  = $this->getSubscriptionOptions($email, $mergeFields, $options);
        $response = $this->mailChimp->post("lists/{$list->getId()}/members", $options);
        if (!$this->lastActionSucceeded()) {
            return false;
        }
        return $response;
    }

    public function subscribePending(string $email, array $mergeFields = [], string $listName = '', array $options = [])
    {
        $options = array_merge($options, ['status' => 'pending']);
        return $this->subscribe($email, $mergeFields, $listName, $options);
    }

    public function subscribeOrUpdate(string $email, array $mergeFields = [], string $listName = '', array $options = [])
    {
        $list     = $this->lists->findByName($listName);
        $options  = $this->getSubscriptionOptions($email, $mergeFields, $options);
        $response = $this->mailChimp->put("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}", $options);
        if (!$this->lastActionSucceeded()) {
            return false;
        }
        return $response;
    }

    public function getMembers(string $listName = '', array $parameters = [])
    {
        $list = $this->lists->findByName($listName);
        return $this->mailChimp->get("lists/{$list->getId()}/members", $parameters);
    }

    public function getMember(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);
        return $this->mailChimp->get("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");
    }

    public function getMemberActivity(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);
        return $this->mailChimp->get("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}/activity");
    }

    public function hasMember(string $email, string $listName = '') : bool
    {
        $response = $this->getMember($email, $listName);
        if (!isset($response['email_address'])) {
            return false;
        }
        if (strtolower($response['email_address']) != strtolower($email)) {
            return false;
        }
        return true;
    }

    public function isSubscribed(string $email, string $listName = '') : bool
    {
        $response = $this->getMember($email, $listName);
        if (!isset($response)) {
            return false;
        }
        if ($response['status'] != 'subscribed') {
            return false;
        }
        return true;
    }

    public function unsubscribe(string $email, string $listName = '')
    {
        $list     = $this->lists->findByName($listName);
        $response = $this->mailChimp->patch("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}", [
            'status' => 'unsubscribed',
        ]);
        if (!$this->lastActionSucceeded()) {
            return false;
        }
        return $response;
    }

    public function updateEmailAddress(string $currentEmailAddress, string $newEmailAddress, string $listName = '')
    {
        $list     = $this->lists->findByName($listName);
        $response = $this->mailChimp->patch("lists/{$list->getId()}/members/{$this->getSubscriberHash($currentEmailAddress)}", [
            'email_address' => $newEmailAddress,
        ]);
        return $response;
    }

    public function delete(string $email, string $listName = '')
    {
        $list     = $this->lists->findByName($listName);
        $response = $this->mailChimp->delete("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");
        return $response;
    }

    public function createCampaign(
        string $fromName,
        string $replyTo,
        string $subject,
        string $html = '',
        string $listName = '',
        array $options = [],
        array $contentOptions = []
    ) {
        $list           = $this->lists->findByName($listName);
        $defaultOptions = [
            'type'       => 'regular',
            'recipients' => [
                'list_id' => $list->getId(),
            ],
            'settings' => [
                'subject_line' => $subject,
                'from_name'    => $fromName,
                'reply_to'     => $replyTo,
            ],
        ];
        $options  = array_merge($defaultOptions, $options);
        $response = $this->mailChimp->post('campaigns', $options);
        if (!$this->lastActionSucceeded()) {
            return false;
        }
        if ($html === '') {
            return $response;
        }
        if (!$this->updateContent($response['id'], $html, $contentOptions)) {
            return false;
        }
        return $response;
    }

    public function updateContent(string $campaignId, string $html, array $options = [])
    {
        $defaultOptions = compact('html');
        $options        = array_merge($defaultOptions, $options);
        $response       = $this->mailChimp->put("campaigns/{$campaignId}/content", $options);
        if (!$this->lastActionSucceeded()) {
            return false;
        }
        return $response;
    }

    public function getApi() : MailChimp
    {
        return $this->mailChimp;
    }

    /**
     * @return array|false
     */
    public function getLastError()
    {
        return $this->mailChimp->getLastError();
    }

    public function lastActionSucceeded() : bool
    {
        return $this->mailChimp->success();
    }

    protected function getSubscriberHash(string $email) : string
    {
        return $this->mailChimp->subscriberHash($email);
    }

    protected function getSubscriptionOptions(string $email, array $mergeFields, array $options) : array
    {
        $defaultOptions = [
            'email_address' => $email,
            'status'        => 'subscribed',
            'email_type'    => 'html',
        ];
        if (count($mergeFields)) {
            $defaultOptions['merge_fields'] = $mergeFields;
        }
        $options = array_merge($defaultOptions, $options);
        return $options;
    }
}
