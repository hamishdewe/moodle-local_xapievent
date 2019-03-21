<?php

namespace local_xapievent\task;

global $CFG;

class send_error_summary extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('task:process_notification', 'local_xapievent');
    }

    public function execute() {
        \local_xapievent\observer::process_notification_summary();
    }
}
