<?php
class User
{
    public string $fist_name, $last_name, $photo_link, $profile_link; //$post_text, $post_link;
    public int $id, $sex, $age;
    public bool $open_direct;


    public function __construct($user_data) {
        $this->id = $user_data['id'];
        $this->fist_name = $user_data['first_name'];
        $this->last_name = $user_data['last_name'];
        $this->sex = $user_data['sex'];
        $this->open_direct = $user_data['can_write_private_message'];
        $this->photo_link = $user_data['photo_max_orig'];
        $this->profile_link = 'https://vk.com/id'.$this->id;
        $this->age = array_key_exists('bdate', $user_data) ? $this->convert_bdate_to_age($user_data['bdate']) : 0;
    }

    private function convert_bdate_to_age(string $bdate) : int {
        if (substr_count($bdate, '.') == 2)
        {
            $dateParts = explode('.', $bdate);
            $day = (int)$dateParts[0];
            $month = (int)$dateParts[1];
            $year = (int)$dateParts[2];

            $birthDate = new DateTime("$year-$month-$day");

            $currentDate = new DateTime();

            $interval = $currentDate->diff($birthDate);

            return $interval->y;
        }
        else
        {
            return 0;
        }
    }

//    public function get_info_line() : string {
//        return sprintf(
//            "\"%s\",\"%s\",\"%s\",%d,\"%d\",\"%s\",\"%s\"",
//            $this->fist_name,
//            $this->last_name,
//            ($this->sex == 1) ? 'Женский' : (($this->sex == 2) ? 'Мужской' : 'Не определён'),
//            $this->age,
//            $this->open_direct,
//            $this->photo_link,
//            $this->profile_link
//        );
//    }
}

//$out_str .= sprintf("\n\"%s\",\"%s\",\"%s\",%d,\"%s\",\"%s\",\"%s\",\"%s\"",
//    $users[$user_id]['first_name'],
//    $users[$user_id]['last_name'],
//    ($users[$user_id]['sex'] == 1) ? 'Женский' : (($users[$user_id]['sex'] == 2) ? 'Мужской' : 'Не определён'),
//    $users[$user_id]['can_write_private_message'],
//    $users[$user_id]['photo_max_orig'],
//    ('https://vk.com/id'.$users[$user_id]['id']),
//    pre_work($wall_posts['items'][$i]['text']),
//    ('https://vk.com/'.$group_username.'?w=wall'.$wall_posts['items'][$i]['owner_id'].'_'.$wall_posts['items'][$i]['id'])
//);