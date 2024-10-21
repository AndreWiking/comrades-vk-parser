<?php

use Orhanerday\OpenAi\OpenAi;

class GPT
{
    private OpenAi $open_ai;
    public function __construct()
    {
        $this->open_ai = new OpenAi($_ENV['GPT_API_KEY']);
        $this->open_ai->setBaseURL("https://api.proxyapi.ru/openai");
        $this->open_ai->setHeader(["Connection" => "keep-alive"]);
    }

    public static array $parse_points_details = ['Бюджет', 'Локация', 'Пол сожителя', 'Возраст сожителя', 'Требования к сожителю', 'Возраст автора сообщения', 'Вид деятельности автора сообщения', 'Информация об авторе сообщения'];

    public static function list_to_line_text(array $list) : string
    {
        return implode(',', $list);
    }
//    public function parse_post_text_detail(string $text): array|string
//    {
//        $points = GPT::list_to_line_text(GPT::$parse_points_details);
//        $chat = $this->open_ai->chat([
//            'model' => 'gpt-4o-mini', //gpt-4o-2024-05-13
//            'messages' => [
//                [
//                    "role" => "user",
//                    "content" => "
//Выдели из сообщения следующие пункты: $points.
//Ответ запиши в формате json, где каждому ключи соответствует одна строка..
//Сообщение: $text"
//                ]
//            ],
//            'temperature' => 0.8,
//            'max_tokens' => 1000,
//            'frequency_penalty' => 0,
//            'presence_penalty' => 0,
//            'response_format' => [ "type" => "json_object" ]
//        ]);
//
//        print_r($chat);
//
//        $answer = json_decode($chat);
//        //$tokens = $d->usage->total_tokens;
//        $answer = json_decode($answer->choices[0]->message->content, true);
//        if (is_null($answer)) $answer = [""];
//        print_r($answer);
//
//        $out = '';
//        $count = count(GPT::$parse_points_details);
//        for ($i = 0; $i < $count; ++$i) {
//            $out .= "\"" . print_r($answer[GPT::$parse_points_details[$i]], true) . "\","; //todo: get answer by key
//            if ($i + 1 == $count)
//                $out = rtrim($out, ',');
//        }
//
//        return str_replace('Array', '', $out);
//    }

    private function chat(string $content, string $model = 'gpt-4o-mini', $temperature = 0.8) {
        $chat = $this->open_ai->chat([
            'model' => $model, //gpt-4o-2024-05-13
            'messages' => [
                [
                    "role" => "user",
                    "content" => $content
                ]
            ],
            'temperature' => $temperature,
            'max_tokens' => 1000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'response_format' => [ "type" => "json_object" ]
        ]);


        $answer = json_decode($chat);
        $answer = json_decode($answer->choices[0]->message->content, true);
        if (is_null($answer)) $answer = [""];

        return $answer;
    }

    public static array $parse_points = ['Бюджет', 'Локация', 'Пол сожителя'];
    public function parse_post_text(string $text): array
    {
        $points = GPT::list_to_line_text(GPT::$parse_points);
        $answer = $this->chat("
Выдели из сообщения следующие пункты: $points.
Ответ запиши в формате json, где каждому ключи соответствует одна строка..
Сообщение: $text",
        );

        print_r($answer);

        $res = [];
        $count = count(GPT::$parse_points);
        for ($i = 0; $i < $count; ++$i) {
            $res[] = $answer[GPT::$parse_points[$i]];
        }

        return $res;
    }

    public function transform_budget(string $budget) : int {

        $answer = $this->chat("Преобразуй бюджет человека на снятие квартиры в одну цифру в рублях. Ответ запиши в формате json c ключом budget. Бюджет: $budget");

        print_r($answer);

        printf("BB: %s === %s\n", $budget, $answer['budget']);
        return intval($answer['budget']);
    }

    public function transform_roommate_sex(string $sex) : int {

        $answer = $this->chat("Преобразуй пол человека в следующий формат: если женский, то 1, если мужской, то 2, если не указан или не получается точно определить то 0.
        В ответ запиши одну цифру в формате json c ключом sex. Пол: $sex");

        print_r($answer);

        printf("SS: %s === %s\n", $sex, $answer['sex']);
        return intval($answer['sex']);
    }


    public function transform_location(string $location) : array {

        $answer = $this->chat("Преобразуй местоположение в Москве в координаты. Если дано несколько местоположений, то возьми среднее из них. 
        Ответ дай в формате json c ключами latitude и longitude. Местоположение: $location");

        print_r($answer);

        printf("LL: %s === %s, %s\n", $location, $answer['latitude'], $answer['longitude']);
        return [floatval($answer['latitude']), floatval($answer['longitude'])];
    }

    public function match_posts(string $posts_data) : array {

        $answer = $this->chat("Дана база людей, которые ищут соседа для совместного проживания в квартире. 
        Для каждого человека найди 1-5 наиболее подходящих соседей. Ответ дай в формате json, для каждого человека список ID его соседей. База: $posts_data");

        print_r($answer);

        return $answer;
    }


    public function test_request(string $text)
    {
        $points = GPT::list_to_line_text(GPT::$parse_points_details);
        $chat = $this->open_ai->chat([
            'model' => 'gpt-4o-mini', //gpt-4o-2024-05-13
            'messages' => [
                [
                    "role" => "user",
                    "content" => "
Выдели из сообщения следующие пункты: $points.
Ответ запиши в формате json, где каждому ключи соответствует одна строка.
Сообщение: $text"
                ]
            ],
            'temperature' => 0.8,
            'max_tokens' => 1000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'response_format' => ["type" => "json_object"]
        ]);

        //print_r($chat);

        $answer = json_decode($chat);
        //$tokens = $d->usage->total_tokens;
        $answer = json_decode($answer->choices[0]->message->content, true);
        if (is_null($answer)) $answer = [""];
        print_r($answer);
    }

}