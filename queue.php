<?php

// show list of listeners

global $PAGE, $CFG, $OUTPUT, $DB;
require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('locallib.php');

admin_externalpage_setup('queue');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$action = optional_param('action', null, PARAM_ALPHA);
$id = optional_param('id', null, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_RAW);
if ($action && $id && $sesskey && $sesskey == sesskey()) {
  switch ($action) {
    case 'dequeue': {
      $DB->delete_records('xapievent_queue', ['id'=>$id]);
      redirect(new moodle_url('/local/xapievent/queue.php'));
    }
    case 'resend': {
      \local_xapievent\observer::process_queue($id);
      redirect(new moodle_url('/local/xapievent/queue.php'));
    }
    case 'rebuild': {
      $record = $DB->get_record('xapievent_queue', ['id'=>$id]);
      $event = json_decode($record->eventdata);
      \local_xapievent\observer::route($event, $record->listenerid);
      $DB->delete_records('xapievent_queue', ['id'=>$id]);
    }
  }
}

$title = get_string('xapieventqueue', 'local_xapievent');

$table = new local_xapievent_queue_table('local_xapievent_queue-table');
$table->define_columns(['listenerid', 'eventdata', 'builderror', 'statement', 'senderror', 'sendcount', 'action']);
$table->define_headers([
  get_string('listener', 'local_xapievent'),
  get_string('eventdata', 'local_xapievent'),
  get_string('builderror', 'local_xapievent'),
  get_string('statement', 'local_xapievent'),
  get_string('senderror', 'local_xapievent'),
  get_string('sendcount', 'local_xapievent'),
  get_string('action', 'local_xapievent')]);
$table->downloadable = false;

$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url("/local/xapievent/queue.php");

echo $OUTPUT->header();


// Work out the sql for the table.
$table->set_sql("*, '' as action", "{xapievent_queue}", "true");

$table->define_baseurl("$CFG->wwwroot/local/xapievent/queue.php");

$table->out(50, true);

echo $OUTPUT->footer();
