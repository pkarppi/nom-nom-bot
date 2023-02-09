<?php

use voku\helper\HtmlDomParser;

/**
 * Fetches nearby restaurant menus 
 *
 * @return array
 */
function get(): array
{
    $restaurant_dish_map = [];
    $day_of_week = date('N');

    for ($i = 0; $i < 3; $i++) {
        $url = "https://www.lounaat.info/ajax/filter?view=lahistolla&day=" . $day_of_week . "&page=$i&coords=false";
        $html = request($url);

        if ($html) {
            $dom =  HtmlDomParser::str_get_html($html);
            $menus = $dom->find('.menu');
            foreach ($menus as $item) {
                $name = $item->find('.item-header h3 a', 0);
                $restaurant_name = $name->innertext;

                $list = $item->find('.menu-item .dish');

                $restaurant_dish_map[$restaurant_name] = [];

                foreach ($list as $dish) {
                    $dish = strip_tags($dish->innertext);
                    $dish = preg_replace('!\s+!', ' ', $dish);
                    $restaurant_dish_map[$restaurant_name][] = $dish;
                }
            }
        }
    }
    return $restaurant_dish_map;
}

function request(string $url): string
{
    if (!defined('CURL_HTTP_VERSION_2_0')) {
        define('CURL_HTTP_VERSION_2_0', 3);
    }

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.80 Safari/537.36',
    ]);

    curl_setopt($ch, CURLOPT_REFERER, 'https://www.lounaat.info/');

    // Set cookie 
    $location = ["lat" => $_SERVER["LAT"], "lng" => $_SERVER["LNG"]];

    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            "Cookie: location_v2=" . urlencode(json_encode($location)),
        )
    );

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function create_message(array $map, array $wanted): string
{
    $random_index = array_rand($wanted);

    // Get random restaurant with AI 
    $random_restaurant = $wanted[$random_index];

    $text = <<<EOF
```     _
    ( )
     H
     H
    _H_ 
 .-'-.-'-.
/         \
|           |
|   .-------'._
|  / /  '.' '. \
|  \ \ @   @ / / 
|   '---------'        
|    _______|  
|  .'-+-+-+|   ----------------------------
|  '.-+-+-+|  - NOM-NOM FOOD BOT 2001      -
|    """""" | - Obey my recommendations!!! -
'-.__   __.-'  ----------------------------
     """```

*PÄIVÄN SUOSITUS ON $random_restaurant*

EOF;

    foreach ($wanted as $name) {
        if (isset($map[$name])) {
            $text .= '*' . $name . '*' . "\n";
            foreach ($map[$name] as $dish) {
                $dish = trim($dish);
                if (mb_strlen($dish) > 2) {
                    $text .= ' • ' . $dish . "\n";
                }
            }
            $text .= "\n";
        }
    }
    return $text;
}

function get_restaurants(): array
{
    $wanted_restaurants = json_decode($_SERVER['RESTAURANTS'], true);
    if ($wanted_restaurants === null) {
        echo "Invalid restaurants.\n";
        exit();
    }
    return $wanted_restaurants;
}

function slack(string $text): void
{
    $params = [
        'text' => $text,
    ];

    if (!isset($_SERVER['SLACK_WEBHOOK'])) {
        die('No slack configured!');
    }

    $url = $_SERVER['SLACK_WEBHOOK'];

    $data_string = json_encode($params);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        )
    );

    $result = curl_exec($ch);

    if (!$result) {
        return;
    }
}
