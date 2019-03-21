<?php

// show list of listeners

global $PAGE, $CFG, $OUTPUT;
require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('locallib.php');

admin_externalpage_setup('listeners');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$action = optional_param('action', null, PARAM_ALPHA);
$id = optional_param('id', null, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_RAW);
if ($action && $id && $sesskey && $sesskey == sesskey()) {
  switch ($action) {
    case 'delete': {
      $DB->delete_records('xapievent_listener', ['id'=>$id]);
      redirect(new moodle_url('/local/xapievent/listeners.php'));
    }
    case 'observehistoric': {
      \local_xapievent\observer::observe_historic_logstore($id);
      redirect(new moodle_url('/local/xapievent/listeners.php'));
    }
  }
}

$title = get_string('xapieventlisteners', 'local_xapievent');

$table = new local_xapievent_listeners_table('local_xapievent-table');
$table->define_columns(['name', 'eventname', 'actor', 'verb', 'object', 'attachments', 'context','result', 'version', 'enabled', 'action']);
$table->define_headers([
  get_string('name', 'local_xapievent'),
  get_string('event', 'local_xapievent'),
  get_string('actor', 'local_xapievent'),
  get_string('verb', 'local_xapievent'),
  get_string('object', 'local_xapievent'),
  get_string('attachments', 'local_xapievent'),
  get_string('context', 'local_xapievent'),
  get_string('result', 'local_xapievent'),
  get_string('version', 'local_xapievent'),
  get_string('enabled', 'local_xapievent'),
  get_string('action', 'local_xapievent')]);
$table->downloadable = false;

$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url("/local/xapievent/listeners.php");

echo $OUTPUT->header();
echo "<div><a class='btn btn-primary text-white' href='{$CFG->wwwroot}/local/xapievent/listener/edit.php'>" . get_string('newlistener', 'local_xapievent') . "</a></div>";

// Work out the sql for the table.
$table->set_sql("*, '' as action", "{xapievent_listener}", "true");

$table->define_baseurl("$CFG->wwwroot/local/xapievent/listeners.php");

$table->out(50, true);


echo $OUTPUT->footer();
