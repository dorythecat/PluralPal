create table users
(
    id int unsigned auto_increment primary key,
    username varchar(64) not null unique,
    email varchar(255) not null unique,
    password_hash varchar(255) not null,
    created_on datetime not null default current_timestamp,
    updated_on datetime not null default current_timestamp on update current_timestamp,
    role enum('owner', 'admin', 'mod', 'user') not null default 'user',
    verified bit not null default 0,
    hidden bit not null default 0,
    unlimited bit not null default 0,
    can_login bit not null default 1,
    public_emai bit not null default 0,
    last_login datetime not null default current_timestamp,
    alter_roles json not null default (json_array()),
    alter_count smallint unsigned not null default 0,
    max_alters smallint unsigned not null default 50,
    temp_extra_alters smallint unsigned not null default 0,
    temp_until datetime null default null
);

create table alters
(
    id int unsigned auto_increment primary key,
    user_id int unsigned not null,
    name varchar(255) not null,
    avatar_url varchar(255) null default null,
    pronouns tinytext null default null,
    front_tag tinytext null default null,
    description text null default null,
    created_on datetime not null default current_timestamp,
    updated_on datetime not null default current_timestamp on update current_timestamp,
    first_front datetime null default null,
    last_front datetime null default null,
    roles json not null default (json_array()),
    images_count smallint unsigned not null default 0,
    max_images smallint unsigned not null default 3,
    temp_extra_images smallint unsigned not null default 0,
    temp_until datetime null default null,
    constraint user_link foreign key (user_id) references users(id) on delete cascade
);