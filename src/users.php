<?php

namespace App\Users;

use function Funct\Collection\compact;

function setUser($user)
{
    $user_json = json_encode($user);
    file_put_contents('./files/users.txt', "{$user_json}\n", FILE_APPEND);
}

function getUsers()
{
    $users = [];
    $handle = fopen('./files/users.txt', 'r');
    while (!feof($handle)) {
        $user = fgets($handle);
        $users[] = json_decode($user, true);
    }
    fclose($handle);
    return compact($users);
}
