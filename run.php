<?php

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__.'/library.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (empty($_SERVER['LAT']) || empty($_SERVER['LNG'])) {
    die('Put LAT and LNG in .env file!');
}

if (count($_SERVER['argv']) <= 1) {
    die("No command!\n");
}

$command = trim($_SERVER['argv'][1]);

if ($command === 'nearby') {
    $map = get();
    echo "Nearby restaurants:\n";
    echo join("\n", array_keys($map))."\n";
} else if ($command === 'test-slack') {
    $wanted_restaurants = get_restaurants();
    $message = "NOM-NOM FOOD BOT 2001 TESTING";
    slack($message);
} else if ($command === 'preview') {
    $wanted_restaurants = get_restaurants();
    $map = get();
    $message = create_message($map, $wanted_restaurants);
    echo $message;
} else if ($command === 'slack') {
    $wanted_restaurants = get_restaurants();
    $map = get();
    $message = create_message($map, $wanted_restaurants);
    slack($message);
} else {
    echo "NO COMMAND!!!\n";
}
