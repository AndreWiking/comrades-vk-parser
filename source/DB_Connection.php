<?php

include_once __DIR__ . '/User.php';
include_once __DIR__ . '/WallPostManager.php';

const DB_HOST = 'localhost';
//const DB_HOST = '46.17.41.227';
const DB_PORT = '5432';
const DB_USER = 'super_admin';
const DB_PASSWORD = 'gt53_gky94.rtG&xx-rp-ovD';
const DB_NAME = 'postgres';

class DB_Connection
{
    private PDO $connect;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $host = DB_HOST;
        $port = DB_PORT;
        $db = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASSWORD;

        $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->connect = new PDO($dsn, $user, $pass, $opt);
    }

    public function InsertUser(User $user): void
    {
        $res = $this->connect->prepare("SELECT * FROM VK_Users WHERE id = ?");
        $res->execute([$user->id]);
        if (!$res->fetch()) {
            $stmt = $this->connect->prepare(
                "INSERT INTO VK_Users (id, first_name, last_name, sex, age, is_open_direct, photo_link, profile_link) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user->id, $user->fist_name, $user->last_name, $user->sex, $user->age, (int)$user->open_direct, $user->photo_link, $user->profile_link]);
        }
    }

    public function IsPostInserted(int $post_id) : bool {
        $res = $this->connect->prepare("SELECT * FROM VK_Post WHERE id = ?");
        $res->execute([$post_id]);
        return (bool)$res->fetch();
    }


    public function InsertPost(WallPost $post): void
    {
        $stmt = $this->connect->prepare(
            "INSERT INTO VK_Post (id, user_id, text, link, date, apartments_budget, apartments_location_s, apartments_location_w, roommate_sex)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$post->id, $post->user->id, $post->text, $post->link, $post->publication_date,
            $post->apartments_budget, $post->apartments_location_s, $post->apartments_location_w, $post->roommate_sex]);
    }

    public function InsertMatch($user1_id, $user2_id): void
    {
        try {
            $stmt = $this->connect->prepare(
                "INSERT INTO VK_Match (user1_id, user2_id) VALUES (?, ?)");
            //$stmt->execute($user1_id < $user2_id ? [$user1_id, $user2_id] : [$user2_id, $user1_id]);
            $stmt->execute([$user1_id, $user2_id]);
        }
        catch (Exception $e) {
            echo 'InsertMatch exception: ', $e->getMessage(), "\n";
        }
    }//TRUNCATE VK_Match;

    public function ClearMatches(): void
    {
        $this->connect->query("TRUNCATE VK_Match");
    }

    function GetAllPost(): bool|array
    {
        $user = $this->connect->query("SELECT * FROM VK_Post");
        return $user->fetchAll();
    }

    function GetPostLink($post_id): string
    {
        $req = $this->connect->prepare("SELECT link FROM VK_Post WHERE id = ?");
        $req->execute([$post_id]);
        $res = $req->fetch();
        if (!$res) return "";
        else return $res['link'];
    }

    function GetUserSex($user_id): int
    {
        $req = $this->connect->prepare("SELECT sex FROM VK_Users WHERE id = ?");
        $req->execute([$user_id]);
        $res = $req->fetch();
        if (!$res) return 0;
        else return $res['sex'];
    }
}