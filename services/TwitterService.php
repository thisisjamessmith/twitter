<?php
/**
 * @link      https://dukt.net/craft/twitter/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/twitter/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'twitter/vendor/autoload.php');
require_once(CRAFT_PLUGINS_PATH.'twitter/base/TwitterTrait.php');

use Guzzle\Http\Client;

class TwitterService extends BaseApplicationComponent
{
	// Traits
	// =========================================================================

	use TwitterTrait;

    // Public Methods
    // =========================================================================

    /**
     * Returns the tweet with URLs transformed into HTML links
     *
     * @param int $text      The tweet's text.
     * @param array $options Options to pass to AutoLink.
     *
     * @return string
     */
    public function autoLinkTweet($text, $options = array())
    {
	    require_once(CRAFT_PLUGINS_PATH.'twitter/src/Twitter/AutoLink.php');

	    $twitter = \Twitter\AutoLink::create();

        $aliases = array(
            'urlClass' => 'setURLClass',
            'usernameClass' => 'setUsernameClass',
            'listClass' => 'setListClass',
            'hashtagClass' => 'setHashtagClass',
            'cashtagClass' => 'setCashtagClass',
            'noFollow' => 'setNoFollow',
            'external' => 'setExternal',
            'target' => 'setTarget'
        );

        foreach($options as $k => $v)
        {
            if(isset($aliases[$k]))
            {
                $twitter->{$aliases[$k]}($v);
            }
        }

        $html = $twitter->autoLink($text);

        return $html;
    }

    /**
     * Returns the HTML embed of a Tweet from its ID
     *
     * @param int $tweetId
     * @param array $params
     * @return string|null HTML embed of the Tweet
     */
    public function embedTweet($tweetId, $params = array())
    {
        try {
            // params

            $opts = array();

            $aliases = array(
                'conversations' => 'data-conversations',
                'cards' => 'data-cards',
                'linkColor' => 'data-link-color',
                'theme' => 'data-theme'
            );

            foreach($aliases as $aliasKey => $alias)
            {
                if(!empty($params[$aliasKey]))
                {
                    $opts[$alias] = $params[$aliasKey];
                    unset($params[$alias]);
                }
            }

            $params = array_merge($opts, $params);
            $paramsString = '';

            foreach($params as $paramKey => $paramValue)
            {
                $paramsString .= ' '.$paramKey.'="'.$paramValue.'"';
            }


            // oembed

            $response = craft()->twitter_api->get('statuses/oembed', array('id' => $tweetId));

            if($response)
            {
                $html = $response['html'];

                $html = str_replace('<blockquote class="twitter-tweet">', '<blockquote class="twitter-tweet"'.$paramsString.'>', $html);


                return $html;
            }
        }
        catch(\Exception $e)
        {
            return;
        }
    }

    /**
     * Returns a tweet by its ID.
     *
     * @param int $tweetId
     * @param array $params
     * @param bool $enableCache
     *
     * @return array|null
     */
    public function getTweetById($tweetId, $params = array(), $enableCache = false)
    {
        $params = array_merge($params, array('id' => $tweetId));

        $tweet = craft()->twitter_api->get('statuses/show', $params, array(), $enableCache);

        if(is_array($tweet))
        {
            return $tweet;
        }
    }

    /**
     * Returns a tweet by its URL or ID.
     *
     * @param string $urlOrId
     * @return array|null
     */
    public function getTweetByUrl($urlOrId)
    {
        // Extract tweet id from a tweet URL

        if (preg_match('/^\d+$/', $urlOrId))
        {
            $id = $urlOrId;
        }
        else if (preg_match('/\/status(es)?\/(\d+)\/?$/', $urlOrId, $matches))
        {
            $id = $matches[2];
        }


        // Retrieve the tweet from its ID

        if(isset($id))
        {
            return $this->getTweetById($id);
        }
    }

    /**
     * Returns a user by their ID.
     *
     * @param int $userId
     * @param array $params
     * @return array|null
     */
    public function getUserById($userId, $params = array())
    {
        $params = array_merge($params, array('user_id' => $userId));
        return craft()->twitter_api->get('users/show', $params);
    }
}
