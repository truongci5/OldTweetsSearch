<?php

require_once("twitteroauth.php");

class TwitterSearch
{
    private $twitteroauth=null;
    private static $max = 1000;

    public function __construct($appKey, $appSecret, $token, $tokenSecret)
    {
        $this->twitteroauth = new TwitterOAuth($appKey, $appSecret, $token, $tokenSecret);
    }

    /**
     * Returns a collection of relevant Tweets matching a specified query
     * @param  string $query A UTF-8, URL-encoded search query of 1,000 characters maximum, including operators
     * @param  string $lang  Restricts tweets to the given language, given by an ISO 639-1 code
     * @param  long   $since Returns tweets posted after this time-stamp
     * @return mixed         List of tweets
     */
    public function search($query, $lang=false, $since=false)
    {
        if (!$since) {
            $since = time() - 6*86400;
        }

        $tweets = array();
        $first = time();
        $maxId = false;

        while ($first > $since) {
            $params = array(
                'q'           => $query,
                'result_type' => 'recent',
                'count'       => 100,
            );

            if ($lang) $params['lang'] = $lang;
            if ($maxId) $params['max_id'] = $maxId;

            $data = $this->twitteroauth->get("search/tweets",$params);

            try {
                $statuses = $data->statuses;

                foreach ($statuses as $t) {
                    if (!isset($tweets[$t->id])) {
                        if (!$maxId || $maxId > $t->id) {
                            $maxId = $t->id;
                        }
                        if ($first > strtotime($t->created_at)) {
                            $first = strtotime($t->created_at);
                        }

                        $tweets[$t->id] = array(
                            'id' => $t->id,
                            'text' => $t->text,
                            'created_at' => strtotime($t->created_at),
                            'retweet_count' => $t->retweet_count,
                            'user_id' => $t->user->id,
                            'user_screen_name' => $t->user->screen_name,
                            'user_name' => $t->user->name,
                            'user_followers_count' => $t->user->followers_count,
                            'user_lang' => $t->user->lang,
                            'user_location' => $t->user->location
                        );
                    }
                }

                if (count($statuses) < 10 || count($tweets) > self::$max) break;
            }
            catch(Exception $e) {
                // echo "Rate limit exceeded";
            }
        }

        krsort($tweets);
        $tweets = array_values($tweets);

        return $tweets;
    }
}