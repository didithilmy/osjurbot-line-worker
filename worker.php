<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

define("AMQP_HOST", getenv("AMQP_HOST") ?: 'localhost');
define("AMQP_PORT", getenv("AMQP_PORT") ?: 5672);
define("AMQP_USER", getenv("AMQP_USER") ?: 'guest');
define("AMQP_PASS", getenv("AMQP_PASS") ?: 'guest');
define("AMQP_VHOST", getenv("AMQP_VHOST") ?: '/');

$connection = new AMQPStreamConnection(AMQP_HOST, AMQP_PORT, AMQP_USER, AMQP_PASS, AMQP_VHOST);
$channel = $connection->channel();

$channel->queue_declare("osjurbot-line-queue", false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";
};

$channel->basic_consume('osjurbot-line-queue', '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}