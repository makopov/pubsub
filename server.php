<?php
require 'datastore.php';

/**
 * This is a very basic pub/sub server, the purpose of this is so that a subscriber
 * can be notified when a perticular topic happens. This verison does not implement 
 * unsubscribe, the reason being is my data store would need to be a bit more intelligent
 * for that. For the purposes of this demonstration I've left that out.
 */
class PubSubServer {
    private $datastore;

    // Instantiate our datastore and any other variables we'd need
    public function __construct() {
        $this->datastore = new Datastore();
    }

    /**
     * Since we're not using a framework we need something to route our requests
     * This is a simple implementation of a router for this purpose.
     */
    public function route() {
        if($_SERVER['REQUEST_URI'] == '/upsert' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            if(!$this->is_upsert_payload_valid($_POST)) {
                http_response_code(400);
                return;
            }
            $this->upsert($_POST['topic'], $_POST['message']);
        }

        if($_SERVER['REQUEST_URI'] == '/subscribe' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            if(!$this->is_subscribe_payload_valid($_POST)) {
                http_response_code(400);
                return;
            }

            $this->subscribe($_POST['topic'], $_POST['callback']);
        }
    }

    /**
     * This will perform an upsert, however we're not actually storing the message for 
     * any historical reasons, simply relaying here. In a real world example I'd want 
     * to store messages for at least a short period of time. 
     * 
     * We grab all subscribers to this topic and send a post to them.
     */
    public function upsert($topic, $message) {
        if($this->datastore->topic_has_subscribers($topic)) {
            foreach($this->datastore->get_subscribers($topic) as $consumer_route) {
                $this->post_message($consumer_route, $message);
            }
        }
    }

    /**
     * This is how a consumer would subscribe to a topic, its very simple, they need
     * a topic and a callback url for us. We stoer it and move on.
     */
    public function subscribe($topic, $callback) {
        $this->datastore->store_subscribers($topic, $callback);
    }

    /**
     * Not implementing this but stubbing it as a thought. Ideally you can unsubscribe from a topic, which means 
     * as a consumer, you'd have a uuid to uniquely identify you as a consumer, then using whatever data store we
     * want we would remove your entry from the list of consumers.
     */
    public function unsubscribe($topic, $uid) {
        //... 
    }

    /**
     * A basic payload verification function, most frameworks like Laravel have this built in
     */
    private function is_upsert_payload_valid($post) {
        if(isset($post['topic']) && isset($post['message'])) {
            return true;
        }

        return false;
    }

    /**
     * A basic payload verification function, most frameworks like Laravel have this built in
     */
    private function is_subscribe_payload_valid($post) {
        if(isset($post['topic']) && isset($post['callback'])) {
            return true;
        }

        return false;
    }

    /**
     * Not the prettiest implementation of a post, but without any frameworks here, curl does the job.
     * This will send the relay message to all of our subscribers.
     */
    private function post_message($consumer_route, $message) {
        $json_data = json_encode(['message' => $message], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        $curl = curl_init($consumer_route);

        curl_setopt($curl, CURLOPT_URL, $consumer_route);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);
        curl_close($curl);
    }
}

$server = new PubSubServer();
$server->route();

