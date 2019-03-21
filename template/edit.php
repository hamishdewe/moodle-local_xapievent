<?php

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('template_form.php');

global $PAGE, $CFG, $OUTPUT, $DB;

$context = context_system::instance();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$title = get_string('atemplate', 'local_xapievent', get_string('event', 'local_xapievent'));
$PAGE->set_title($title);
$PAGE->set_heading($title);

$id = optional_param('id', null, PARAM_INT);
$PAGE->set_url(new moodle_url('/local/xapievent/template/edit.php', ['id'=>$id]));

$mform = new \local_xapievent\template_form();
if ($id) {
  $data = $DB->get_record('xapievent_template', ['id'=>$id]);
  $mform->set_data($data);
}

if ($mform->is_cancelled()) {
  //Handle form cancel operation, if cancel button is present on form
  redirect(new moodle_url("/local/xapievent/templates.php"));
} else if ($data = $mform->get_data()) {
  if ($id) {
    $DB->update_record('xapievent_template', $data);
  } else {
    $id = $DB->insert_record('xapievent_template', $data);
  }
  redirect(new moodle_url("/local/xapievent/templates.php"));
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    //Set default data (if any)
    //
    //displays the form
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
