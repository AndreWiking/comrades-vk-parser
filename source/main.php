<?php
// key: b72abf88b72abf88b72abf88d9b40ba9cebb72ab72abf88d03a4d18efe221ae6727063c

include_once __DIR__.'/../vendor/autoload.php';


use VK\Client\VKApiClient;


function pre_work(string $text): string
{
    return str_replace("\"", "\"\"", $text);
}

$group_username = 'podselenie_moskva';
$group_id = -92625295;
$count = 20;

$access_token = "b72abf88b72abf88b72abf88d9b40ba9cebb72ab72abf88d03a4d18efe221ae6727063c";
$vk = new VKApiClient();
$wall_posts = $vk->wall()->get($access_token, [
    'owner_id'  => $group_id,
    'fields'    => $group_username,
    'count'    => $count,
    'filter'    => 'others',
    'offset'    => 0,
    'lang' => 'ru',
]);


file_put_contents('wall_posts_out.txt', print_r($wall_posts, true));

$wall_post_count = count($wall_posts['items']);

$user_ids = "";
for($i=0; $i < $wall_post_count; ++$i) {
    $user_ids .= $wall_posts['items'][$i]['from_id'];
    $user_ids .= $i + 1 != $wall_post_count ? ", " : "";
}
echo $user_ids;
$users_info = $vk->users()->get($access_token, [
    'user_ids'  => $user_ids,
    'fields'    => 'has_photo, photo_max_orig, sex, can_write_private_message',
    'lang' => 'ru',
]);


file_put_contents('users_info.txt', print_r($users_info, true));

$users_info_count = count($users_info);
$users = [];
for($i=0; $i < $users_info_count; ++$i) {
    $users[$users_info[$i]['id']] = $users_info[$i];
}
print_r($users);

$columns_title = ['Имя', 'Фамилия', 'Пол', 'Открыта личка', 'Фото', 'Профиль', 'Текс поста', 'Пост'];
$out_str = "";
foreach ($columns_title as $column) $out_str .= $column.',';

$out_str = rtrim($out_str, ',');

for($i=0; $i < $wall_post_count; ++$i) {
    $user_id = $wall_posts['items'][$i]['from_id'];

    $out_str .= sprintf("\n\"%s\",\"%s\",\"%s\",%d,\"%s\",\"%s\",\"%s\",\"%s\"",
        $users[$user_id]['first_name'],
        $users[$user_id]['last_name'],
        ($users[$user_id]['sex'] == 1) ? 'Женский' : (($users[$user_id]['sex'] == 2) ? 'Мужской' : 'Не определён'),
        $users[$user_id]['can_write_private_message'],
        $users[$user_id]['photo_max_orig'],
        ('https://vk.com/id'.$users[$user_id]['id']),
        pre_work($wall_posts['items'][$i]['text']),
        ('https://vk.com/'.$group_username.'?w=wall'.$wall_posts['items'][$i]['owner_id'].'_'.$wall_posts['items'][$i]['id'])
    );
}

file_put_contents('out.csv', $out_str);