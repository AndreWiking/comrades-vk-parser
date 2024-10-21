<?php
include_once "WallPost.php";

class WallPostManager
{
    /** @var  array : WallPost */
    private array $wall_posts = [];
    private array $users_id = [];
    private array $columns_title =
        ['Имя', 'Фамилия', 'Пол', 'Возраст','Открыта личка', 'Фото', 'Профиль', 'Текс поста', 'Пост', 'Группа', 'Дата'];


    public static function GetUserId($wall_post, GroupPostingType $type) : int|false
    {

        switch ($type)
        {
            case GroupPostingType::User:
                if (array_key_exists('from_id', $wall_post)) return $wall_post['from_id'];
                else return false;

            case GroupPostingType::Group:
                if (array_key_exists('signer_id', $wall_post)) return $wall_post['signer_id'];
                else return false;

            case GroupPostingType::GroupInText:
                $text = $wall_post['text'];
                $pattern = "[id";
                $pos = strpos($text, $pattern);
                if ($pos !== false) {
                    $len = strlen($text);
                    $id = "";
                    for ($i = $pos + strlen($pattern); $i < $len; ++$i)
                        if ($text[$i] >= '0' && $text[$i] <= '9')
                            $id .= $text[$i];
                        else
                            break;

                    $id = intval($id);
                    return $id == 0 ? false : $id;
                }
                else return false;

        }
        return false;
    }

    private function process_user_info($users_info): array
    {
        $users_info_count = count($users_info);
        $users_data = [];
        for($i=0; $i < $users_info_count; ++$i) {
            $users_data[$users_info[$i]['id']] = $users_info[$i];
        }
        return $users_data;
    }

    public function add_group_posts($wall_posts, $users_info, Group $group): void
    {
        $users_data = $this->process_user_info($users_info);
        $posts_count = count($wall_posts['items']);

        for($i=0; $i < $posts_count; ++$i)
        {
            $wall_post = $wall_posts['items'][$i];

            $user_id = WallPostManager::GetUserId($wall_post, $group->type);
            if (!($user_id !== false)) continue;

            //$key_name = ($group->type == GroupPostingType::User ? 'from_id' :
            //    ($group->type == GroupPostingType::Group ? 'signer_id' : '');
            //if (!array_key_exists($key_name, $post_data)) continue;
            //$user_id = $post_data[$key_name];

            $user = new User($users_data[$user_id]);
            $post = new WallPost($user, $wall_post, $group->username);

            if (!array_key_exists($user_id, $this->users_id)) {
                $this->wall_posts[$user_id] = $post;
            }
            else if ($this->wall_posts[$user_id]->publication_timestamp < $post->publication_timestamp){
                    $this->wall_posts[$user_id] = $post;
            }


            $this->users_id[$user_id] = true;
        }
    }


    public function work_all_posts() : void
    {
//        $out_str = "";
//        foreach ($this->columns_title as $column) $out_str .= $column.',';
//        //$out_str = rtrim($out_str, ',');
//
//        $out_str .= GPT::list_to_line_text(GPT::$parse_points_details);

        foreach ($this->wall_posts as $post) {
            //$out_str .= PHP_EOL.$post->get_table_line();
            $post->add_post_to_db();
        }

        //return $out_str;
    }
}