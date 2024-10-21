<?php

// Метод главных компонент
class MatchingAlgo
{
    private array $posts;

    public function __construct(array $posts)
    {
        $this->posts = $posts;
    }


    private function get_sex(int $sex): string
    {
        return ($sex == 1) ? 'Женский' : (($sex == 2) ? 'Мужской' : 'Любой');
    }

    public static function GetProfileLink(int $id): string
    {
        return 'https://vk.com/id' . $id;
    }


    public static function distance(float $loc1_s, float $loc1_w, float $loc2_s, float $loc2_w): float
    {
        return sqrt(pow($loc1_s - $loc2_s, 2) + pow($loc1_w - $loc2_w, 2));
    }

    private const BUDGET_DIFF = 0.15;
    private const MOSCOW_DIST = 0.34213203515748; //40km
    private const LOCATION_DIFF = self::MOSCOW_DIST / 15.0;

    private static function is_match(array $post1, array $post2): bool
    {
        global $db;

        if ($post1['apartments_location_s'] == 0 || $post1['apartments_location_w'] == 0 || $post2['apartments_location_s'] == 0
            || $post2['apartments_location_w'] == 0 || $post1['apartments_budget'] == 0 || $post2['apartments_budget'] == 0)
            return false;

        $dist = self::distance($post1['apartments_location_s'], $post1['apartments_location_w'],
            $post2['apartments_location_s'], $post2['apartments_location_w']);

        $budget = abs($post1['apartments_budget'] - $post2['apartments_budget']) / (float)($post1['apartments_budget'] + $post2['apartments_budget']);

        $sex_user1 = $db->GetUserSex($post1['user_id']);
        $sex_user2 = $db->GetUserSex($post2['user_id']);
        $sex_match = (($post1['roommate_sex'] == $sex_user2 || $post1['roommate_sex'] == 0) && ($post2['roommate_sex'] == $sex_user1 || $post2['roommate_sex'] == 0));

        return $dist < self::LOCATION_DIFF && $budget < self::BUDGET_DIFF && $sex_match;
    }

    public function match_greedy(): void
    {
        global $db;

        $db->ClearMatches();

        $matches = [];
        $post_count = count($this->posts);
        for ($i = 0; $i < $post_count; ++$i) {
            for ($j = $i + 1; $j < $post_count; ++$j) {
                if (self::is_match($this->posts[$i], $this->posts[$j])) {
                    $matches[$i][] = $j;
                    $db->InsertMatch($this->posts[$i]['user_id'], $this->posts[$j]['user_id']);
                }
            }
        }

        $out = "";
        foreach ($matches as $key => $list) {
            $out .= $this->posts[$key]['link'] . " , ";
            $out .= $this->posts[$key]['date'] . " , ";
            foreach ($list as $index) {
                $out .= $this->posts[$index]['link'] . ' , ';
            }
            $out .= "\n";
        }

        file_put_contents(__DIR__ . '/../out/match.txt', $out);
    }

    public function match_gpt()
    {
        global $gpt;
        global $db;

        $posts_data = "";
        foreach ($this->posts as $post) {
            $posts_data .= "\n{\nID человека: " . $post['id']
                . "\nБюджет квартиры: " . $post['apartments_budget']
                . "\nПол соседа: " . $this->get_sex($post['roommate_sex'])
                . "\nЖелаемое местоположение квартиры: " . $post['apartments_location_s'] . ", " . $post['apartments_location_w']
                . "\n}\n";
        }
        print_r($posts_data);

        $answer = $gpt->match_posts($posts_data);

        $out = "";
        foreach ($answer as $id => $ids_roommate) {
            $out .= $db->GetPostLink($id) . " , ";
            foreach ($ids_roommate as $r_id)
                $out .= $db->GetPostLink($r_id) . ' , ';
            $out .= "\n";
        }

        file_put_contents(__DIR__ . '/../out/match.txt', $out);
    }
}