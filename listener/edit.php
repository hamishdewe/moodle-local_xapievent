<?php

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('listener_form.php');

global $PAGE, $CFG, $OUTPUT, $DB;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$title = get_string('listenertemplate', 'local_xapievent');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$id = optional_param('id', null, PARAM_INT);
$PAGE->set_url(new moodle_url('/local/xapievent/listener/edit.php', ['id'=>$id]));

$mform = new \local_xapievent\listener_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect(new moodle_url("/local/xapievent/listeners.php"));
} else if ($data = $mform->get_data()) {
  //In this case you process validated data. $mform->get_data() returns data posted in form.
  if ($data->id) {
    $DB->update_record('xapievent_listener', $data);
  } else {
    $data->id = $DB->insert_record('xapievent_listener', $data);
  }
  redirect(new moodle_url("/local/xapievent/listeners.php"));
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.

  //Set default data (if any)
  $data = $DB->get_record('xapievent_listener', ['id'=>$id]);
  $mform->set_data($data);
  //displays the form
  echo $OUTPUT->header();
  $mform->display();
  echo $OUTPUT->footer();
}
