<?php

const ALGO_MODE = false;
//const ALGO_MODE = true;

use VK\Exceptions\Api\VKApiBlockedException;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;


include_once __DIR__ . '/../vendor/autoload.php';

include_once __DIR__ . '/GPT.php';


include_once __DIR__ . '/Group.php';
include_once __DIR__ . '/WallPost.php';
include_once __DIR__ . '/User.php';
include_once __DIR__ . '/WallPostManager.php';
include_once __DIR__ . '/DataLoader.php';
include_once __DIR__ . '/DB_Connection.php';
include_once __DIR__ . '/MatchingAlgo.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'config.env');
$dotenv->load();

$gpt = new GPT();
$db = new DB_Connection();
$add_new_post_count = 0;

if (ALGO_MODE) {
    $matching = new MatchingAlgo($db->GetAllPost());
    $matching->match_greedy();
}
else {

    $post_count = 100;
    $groups = [
        new Group('podselenie_moskva', -92625295, GroupPostingType::User),
        new Group('sosed499', -98585531, GroupPostingType::Group),
        new Group('sdam.snimy.moskva', -14791225, GroupPostingType::GroupInText)
    ];

    $data_loader = new DataLoader();
    $post_manager = new WallPostManager();

    try {
        foreach ($groups as $group) {
            [$wall_posts, $users_info] = $data_loader->LoadGroup($group, $post_count);
            $post_manager->add_group_posts($wall_posts, $users_info, $group);
        }
    } catch (VKApiBlockedException|VKApiException|VKClientException $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
    }

    file_put_contents(__DIR__ . '/../out/out.csv', $post_manager->work_all_posts());


    echo "New posts: $add_new_post_count";
}


