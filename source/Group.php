<?php

enum GroupPostingType
{
    case User;
    case Group;
    case GroupInText;
}
class Group {
    public string $username;
    public int $id;
    public GroupPostingType $type;

    public function __construct(string $username, int $id, GroupPostingType $type) {
        $this->username = $username;
        $this->id = $id;
        $this->type = $type;
    }
}