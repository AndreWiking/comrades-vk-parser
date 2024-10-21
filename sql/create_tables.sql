DROP TABLE IF EXISTS VK_Users CASCADE;
CREATE TABLE VK_Users
(
    id             INTEGER PRIMARY KEY,
    first_name     VARCHAR(128),
    last_name      VARCHAR(128),
    sex            INT,
    age            INT,
    is_open_direct BOOL,
    photo_link     VARCHAR(1024),
    profile_link   VARCHAR(1024)
);

DROP TABLE IF EXISTS VK_Post CASCADE;
CREATE TABLE VK_Post
(
    id                    INTEGER PRIMARY KEY,
    user_id               INTEGER REFERENCES VK_Users (id),
    text                  TEXT,
    link                  VARCHAR(1024),
    date                  TIMESTAMP,
    apartments_budget     INTEGER,
    apartments_location_s FLOAT,
    apartments_location_w FLOAT,
    roommate_sex          INTEGER
);

ALTER TABLE VK_Post DROP COLUMN IF EXISTS is_find_roommate;
ALTER TABLE VK_Post ADD COLUMN type INT default 0;

DROP TABLE IF EXISTS VK_Match CASCADE;
CREATE TABLE VK_Match
(
    id       SERIAL,
    user1_id INT REFERENCES VK_Users (id),
    user2_id INT REFERENCES VK_Users (id),
    PRIMARY KEY (user1_id, user2_id)
);




SELECT *
FROM VK_Users;
SELECT *
FROM VK_Post;

--
-- INSERT INTO `VK_Users` (`id`, `first_name`, `last_name`, `sex`, `age`, `is_open_direct`, `photo_link`, `profile_link`)
-- VALUE (?, ?, ?, ?, ?, ?, ?, ?);
--
-- INSERT INTO `VK_Post` (`id`, `user_id`, `text`, `link`, `date`, `apartments_budget`, `apartments_location`, `roommate_sex`)
--     VALUE (?, ?, ?, ?, ?, ?, ?, ?);

-- brew services start postgresql