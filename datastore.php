<?php

/**
 * Ths Datastore class is a wrapper around actually storing the subscription data
 * 
 * This is currently a very simple write to file operation, where each new line
 * represents a new subscriber. Outside of an example I would have this hit Redis or
 * memcache or some other data store with more intelligent keying of subscribers, 
 * such as utilizing a uuid or some such to make unsubscribing possible.
 */
class Datastore {
    private $topics_path = 'topics/';

    /**
     * Makes sure that our directory is created so we do not have errors
     */
    public function __construct() {
        if (!file_exists('topics')) {
            mkdir('topics', 0775, true);
        }     
    }

    /**
     * Loops through the coresponding topic file and returns an array of callbacks for this topic
     */
    public function get_subscribers($topic) : array {
        $subscribers = [];

        if ($file = fopen($this->topics_path . $topic, "r")) {
            while(!feof($file)) {
                $subscribers[] = fgets($file);
            }

            fclose($file);
        }

        return $subscribers;
    }

    /**
     * Creates or appends to a file a new entry with a new callback
     */
    public function store_subscribers($topic, $callback) {
        $file = null;

        if(file_exists($this->topics_path . $topic)) {
            $file = fopen($this->topics_path . $topic, 'a') or die('can\'t open file');
            fwrite($file, "\n");
        } else {
            $file = fopen($this->topics_path . $topic, 'w') or die('can\'t open file');
        }

        fwrite($file, $callback);
        fclose($file);
    }

    /**
     * Returns for us a true or false value of whether the topic has any subscribers
     */
    public function topic_has_subscribers($topic) : bool {
        return file_exists($this->topics_path . $topic);
    }

    /**
     * Not implementing this but stubbing it as a thought. Ideally you can unsubscribe from a topic, which means 
     * as a consumer, you'd have a uuid to uniquely identify you as a consumer, then using whatever data store we
     * want we would remove your entry from the list of consumers.
     */
    public function unsubscribe($topic, $uid) {
       //... 
    }
}