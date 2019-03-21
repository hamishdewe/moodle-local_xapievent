<?php

$observers = array(
    array(
        'eventname' => '*',
        'callback'  => 'local_xapievent\observer::route',
        'includefile' => '/local/xapievent/locallib.php'
    )
);
