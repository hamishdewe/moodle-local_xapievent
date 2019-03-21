<?php

require_once($CFG->dirroot . '/local/xapievent/classes/defaults.php');

function xmldb_local_xapievent_install() {
    \local_xapievent\defaults::insert_defaults();
}
