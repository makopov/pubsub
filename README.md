### Pub/Sub example
> Author: Michael Akopov

This example has three files:
- `consumer.php` (can be started by running `./consumer.sh` from a terminal)
- - This file simply outputs what our server `POSTS` to us, its meant to be a simple consumer example
- `server.php` (can be ran in a different terminal window with `./server.sh`)
- - This is the actual server handling the request of a upsert and subscription event.
- `datastore.php`
- - This is used by our server and is meant to abstractly store our subscripton data, in this current example it uses a file on disk operation to do that.
- `postman_collection.json` If you'd like to run this locally you can import this postman collection and run the two endpoints. 
- - One will be to subscripbe to a topic, and the other to post a message.
- - Be sure that you are running both the consumer and the server at the same time before doing this.

##### Requirements
- PHP 8.0: Not sure if there is anything specific to 8 here, but I created this in 8. 
##### Some more reading:
[What is a pub/sub system](https://www.techtarget.com/searchapparchitecture/tip/How-pub-sub-messaging-works-and-why-it-matters-today)