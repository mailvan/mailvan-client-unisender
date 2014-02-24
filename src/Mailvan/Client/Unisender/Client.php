<?php

namespace Mailvan\Client\Unisender;

use Guzzle\Common\Collection;
use Guzzle\Service\Description\ServiceDescription;
use Mailvan\Core\Client as BaseClient;
use Mailvan\Core\MailvanException;
use Mailvan\Core\Model\SubscriptionListInterface;
use Mailvan\Model\Unisender\User;
use Mailvan\Core\Model\UserInterface;
use Mailvan\Core\Model;

class Client extends BaseClient
{
    public static function factory($config = array())
    {
        $required = array('base_url', 'api_key');

        $config = Collection::fromConfig($config, array(), $required);

        $client = new self($config->get('base_url'), $config);

        $client->setDescription(ServiceDescription::factory(dirname(__FILE__).'/operations.json'));

        return $client;
    }

    /**
     * Subscribes given user to given SubscriptionList. Returns true if operation is successful
     *
     * @param UserInterface $user
     * @param SubscriptionListInterface $list
     * @return boolean
     */
    public function subscribe(UserInterface $user, SubscriptionListInterface $list)
    {
        $params = array(
            'list_ids' => $list->getId(),
            'fields' => array(
                'email' => $user->getEmail(),
            ),
        );

        if ($user->getFirstName()){
            $params['fields']['Name'] = trim($user->getFirstName());
        }

        if ($user instanceof User && $user->getTags()){
            $params['tags'] = $user->getTags();
        }

        return $this->doExecuteCommand('subscribe', $params, function($response){
            return $response['person_id'];
        });
    }

    /**
     * Unsubscribes given user from given SubscriptionList.
     *
     * @param UserInterface $user
     * @param SubscriptionListInterface $list
     * @return boolean
     */
    public function unsubscribe(UserInterface $user, SubscriptionListInterface $list)
    {
        $params = array(
            'contact' => $user->getEmail(),
            'list_ids' => $list->getId(),
        );

        return $this->doExecuteCommand('unsubscribe', $params, function(){
            return true;
        });
    }

    /**
     * Moves user from one list to another. In some implementation can create several http queries.
     *
     * @param UserInterface $user
     * @param SubscriptionListInterface $from
     * @param SubscriptionListInterface $to
     * @return boolean
     */
    public function move(UserInterface $user, SubscriptionListInterface $from, SubscriptionListInterface $to)
    {
        return $this->unsubscribe($user, $from) && $this->subscribe($user, $to);
    }

    /**
     * Returns list of subscription lists created by owner.
     *
     * @return array
     */
    public function getLists()
    {
        return $this->doExecuteCommand('getLists', [], function($response) {
            return array_map(
                function($item) {
                    return $this->createSubscriptionList($item['id']);
                },
                $response['result']
            );
        });
    }

    /**
     * Merge API key into params array. Some implementations require to do this.
     *
     * @param $params
     * @return mixed
     */
    protected function mergeApiKey($params)
    {
        return array_merge($params, array('api_key' => $this->getConfig('api_key')));
    }

    /**
     * Check if server returned response containing error message.
     * This method must return true if servers does return error.
     *
     * @param $response
     * @return mixed
     */
    protected function hasError($response)
    {
        return isset($response['error']);
    }

    /**
     * Raises Exception from response data
     *
     * @param $response
     * @return MailvanException
     */
    protected function raiseError($response)
    {
        return new UnisenderException($response['error'], $response['code']);
    }
}
