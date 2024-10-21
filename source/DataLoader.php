<?php

include_once __DIR__.'/WallPostManager.php';

use VK\Client\VKApiClient;
use VK\Exceptions\Api\VKApiBlockedException;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;


class DataLoader
{
    private string $access_token;

    private VKApiClient $vk;
    public function __construct()
    {
        $this->access_token = $_ENV['VK_API_KEY'];
        $this->vk = new VKApiClient();
    }



    private function GetUserIdList($wall_posts, GroupPostingType $type) : string
    {
        $loaded_post_count = count($wall_posts['items']);

        $user_ids = "";
        for($i=0; $i < $loaded_post_count; ++$i) {
            $wall_post = $wall_posts['items'][$i];

            $id = WallPostManager::GetUserId($wall_post, $type);
            if ($id !== false) $user_ids .= $id;
            else continue;
            //$key_name = ($type == GroupPostingType::User ? 'from_id' : ($type == GroupPostingType::Group ? 'signer_id' : ''));
            //if (!array_key_exists($key_name, $wall_post)) continue;
            //$user_ids .= $wall_post[$key_name];

            $user_ids .= $i + 1 != $loaded_post_count ? ", " : "";
        }
        return $user_ids;
    }

    /**
     * @throws VKApiBlockedException
     * @throws VKApiException
     * @throws VKClientException
     */
    public function LoadGroup(Group $group, int $post_count): array
    {
        $wall_posts = $this->vk->wall()->get($this->access_token, [
            'owner_id'  => $group->id,
            'fields'    => $group->username,
            'count'    => $post_count,
            'filter'    => $group->type == GroupPostingType::User ? 'others' :
                ($group->type == GroupPostingType::Group || $group->type == GroupPostingType::GroupInText ? 'owner' : ''),
            'offset'    => 0,
            'lang' => 'ru',
        ]);

        //print_r($wall_posts);

        file_put_contents(__DIR__.'/../out/wall_posts_out.txt', print_r($wall_posts, true));

        $user_ids_list = $this->GetUserIdList($wall_posts, $group->type);

        $users_info = $this->vk->users()->get($this->access_token, [
            'user_ids'  => $user_ids_list,
            'fields'    => 'has_photo, photo_max_orig, sex, can_write_private_message, bdate',
            'lang' => 'ru',
        ]);


        file_put_contents(__DIR__.'/../out/users_info.txt', print_r($users_info, true));

        return [$wall_posts, $users_info];
    }
}
