<?php

// show list of listeners

global $PAGE, $CFG, $OUTPUT;
require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('locallib.php');

admin_externalpage_setup('templates');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$action = optional_param('action', null, PARAM_ALPHA);
$id = optional_param('id', null, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_RAW);
if ($action && $id && $sesskey && $sesskey == sesskey()) {
    switch ($action) {
        case 'delete': {
            $DB->delete_records('xapievent_template', ['id'=>$id]);
            redirect(new moodle_url('/local/xapievent/templates.php'));
        }
    }
}

$title = get_string('xapieventtemplates', 'local_xapievent');

$table = new local_xapievent_templates_table('local_xapievent-table');
$table->sortable(true);
$table->define_columns(['name', 'shortname', 'property', 'datatype', 'content', 'query', 'action']);
$table->define_headers([
  get_string('name', 'local_xapievent'),
  get_string('shortname', 'local_xapievent'),
  get_string('property', 'local_xapievent'),
  get_string('datatype', 'local_xapievent'),
  get_string('content', 'local_xapievent'),
  get_string('query', 'local_xapievent'),
  get_string('action', 'local_xapievent')]);
$table->downloadable = false;

$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url("/local/xapievent/templates.php");

echo $OUTPUT->header();
echo "<div><a class='btn btn-primary text-white' href='{$CFG->wwwroot}/local/xapievent/template/edit.php'>" . get_string('newtemplate', 'local_xapievent') . "</a></div>";


// Work out the sql for the table.
$table->set_sql("*, '' as action", "{xapievent_template}", "true");

$table->define_baseurl("$CFG->wwwroot/local/xapievent/templates.php");

$table->out(200, true);

echo $OUTPUT->footer();
