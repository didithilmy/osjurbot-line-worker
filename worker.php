<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

define("AMQP_HOST", getenv("AMQP_HOST") ?: 'localhost');
define("AMQP_PORT", getenv("AMQP_PORT") ?: 5672);
define("AMQP_USER", getenv("AMQP_USER") ?: 'guest');
define("AMQP_PASS", getenv("AMQP_PASS") ?: 'guest');
define("AMQP_VHOST", getenv("AMQP_VHOST") ?: '/');

define("LINEBOT_CHANNEL_SECRET", getenv("LINEBOT_CHANNEL_SECRET") ?: '');
define("LINEBOT_CHANNEL_TOKEN", getenv("LINEBOT_CHANNEL_TOKEN") ?: '');

$connection = new AMQPStreamConnection(AMQP_HOST, AMQP_PORT, AMQP_USER, AMQP_PASS, AMQP_VHOST);
$channel = $connection->channel();

$channel->queue_declare("osjurbot-line-queue", false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
    $msgs = json_decode($msg->body);

    echo ' [x] Received ', count($msgs), " messages to sent\n";

    // Sending messages
    foreach($msgs as $message) {
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(LINEBOT_CHANNEL_TOKEN);
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => LINEBOT_CHANNEL_SECRET]);

        echo " [>] Sending message to ".$message->mid."...\n";
        $bot->pushMessage($message->mid, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message->txt));
    }
};

$channel->basic_consume('osjurbot-line-queue', '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}