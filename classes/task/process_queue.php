<?php

namespace local_xapievent\task;

global $CFG;

class process_queue extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('task:process_queue', 'local_xapievent');
    }

    public function execute() {
        \local_xapievent\observer::process_queue();
    }
}
