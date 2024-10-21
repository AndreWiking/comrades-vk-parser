<?php


include_once "User.php";

class WallPost
{
    public User $user;
    public string $text, $link, $publication_date, $group_link;
    public int $id;

    public int $publication_timestamp;
    public int $apartments_budget, $roommate_sex;
    public float $apartments_location_s, $apartments_location_w;

    private function preprocessing_text(string $text) : string
    {
        return str_replace("\"", "\"\"", $text);
    }


    public function __construct(User $user, $post_data, string $group_username)
    {
        $this->user = $user;
        $this->id = $post_data['id'];
        $this->text = $this->preprocessing_text($post_data['text']);
        $this->link = 'https://vk.com/'.$group_username.'?w=wall'.$post_data['owner_id'].'_'.$this->id;
        $this->group_link = 'https://vk.com/'.$group_username;
        $this->publication_timestamp = $post_data['date'];
        $this->publication_date = $this->convert_date($this->publication_timestamp);
    }

    private function convert_date(int $timestamp) : string
    {
        date_default_timezone_set('Europe/Moscow');
        return date('d M Y H:i', $timestamp);
    }

    public function add_post_to_db(): void
    {
        global $gpt;
        global $db;
        global $add_new_post_count;

        if (!$db->IsPostInserted($this->id)) {
            $post_details = $gpt->parse_post_text($this->text);

            $this->apartments_budget = $gpt->transform_budget($post_details[0]);

            $this->roommate_sex = $gpt->transform_roommate_sex($post_details[2]);

            $location = $gpt->transform_location($post_details[1]);
            $this->apartments_location_s = $location[0];
            $this->apartments_location_w = $location[1];


            $db->InsertUser($this->user);
            $db->InsertPost($this);

            ++$add_new_post_count;
        }

    }
}
