<?php

$tasks = array(
    array(
        'classname' => 'local_xapievent\task\process_queue',
        'blocking'  => 0,
        'minute'    => '*',
        'hour'      => '*',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*'
    ),
    array(
        'classname' => 'local_xapievent\task\send_error_summary',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '11',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*'
    )
);
