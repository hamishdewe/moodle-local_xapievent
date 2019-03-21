<?php

global $DB, $CFG;

require(__DIR__ . '/../../config.php');
require_once('locallib.php');
global $PAGE, $CFG, $OUTPUT, $DB;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$title = 'Listener Test';
$PAGE->set_title($title);
$PAGE->set_heading($title);

$id = required_param('id', PARAM_INT);
$eventid = optional_param('eventid', null, PARAM_INT);
$post = optional_param('post', false, PARAM_BOOL);

$PAGE->set_url(new moodle_url('/local/xapievent/test.php', ['id'=>$id]));

echo $OUTPUT->header();

$listener = $DB->get_record('xapievent_listener', ['id'=>$id]);
if (!$listener) {
  die('No listener found');
}
$links = [];
$links['actor'] = "<a target='_blank' href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$listener->actor}'>".get_string('actor', 'local_xapievent')."</a>";
$links['verb'] = "<a target='_blank' href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$listener->verb}'>".get_string('verb', 'local_xapievent')."</a>";
$links['object'] = "<a target='_blank' href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$listener->object}'>".get_string('object', 'local_xapievent')."</a>";
$links['attachments'] = "<a target='_blank' href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$listener->attachments}'>".get_string('attachments', 'local_xapievent')."</a>";
$links['context'] = "<a target='_blank' href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$listener->context}'>".get_string('context', 'local_xapievent')."</a>";
$links['result'] = "<a target='_blank' href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$listener->result}'>".get_string('result', 'local_xapievent')."</a>";
$links['version'] = "<a target='_blank' href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$listener->version}'>".get_string('version', 'local_xapievent')."</a>";

echo "<div style='float: left; width: 49%;'><h4>".get_string('listener', 'local_xapievent')."</h4><textarea style='width: 100%; min-height: 300px;'>" . json_encode($listener, JSON_PRETTY_PRINT) . "</textarea>" . implode('<br>', $links) . "</div>";

if ($eventid) {
  $event = $DB->get_record_sql('select * from {logstore_standard_log} where eventname = :eventname and id = :id order by id desc', ['eventname'=>$listener->eventname,'id'=>$eventid], IGNORE_MULTIPLE);
} else {
  $event = $DB->get_record_sql('select * from {logstore_standard_log} where eventname = :eventname order by id desc', ['eventname'=>$listener->eventname], IGNORE_MULTIPLE);
}
local_xapievent_extend_event($event);
if (!$event) {
  die('No event ' . $listener->eventname);
}
if ($event->other) {
  //$event->other = unserialize($event->other);
}
echo "<div style='float: left; width: 49%;'><h4>".get_string('event', 'local_xapievent')."</h4><textarea style='width: 100%; min-height: 300px;'>" . json_encode($event, JSON_PRETTY_PRINT) . "</textarea></div>";

try {
  $statement = new stdClass();
  $statement->actor = json_decode(local_xapievent_fill_template($listener->actor, $event));
  $statement->verb = json_decode(local_xapievent_fill_template($listener->verb, $event));
  $statement->object = json_decode(local_xapievent_fill_template($listener->object, $event));
  $statement->version = json_decode(local_xapievent_fill_template($listener->version, $event));
  if ($listener->attachments) {
    $statement->attachments = json_decode(local_xapievent_fill_template($listener->attachments, $event));
  }
  if ($listener->context) {
    $statement->context = json_decode(local_xapievent_fill_template($listener->context, $event));
  }
  if ($listener->result) {
    if ($result = json_decode(local_xapievent_fill_template($listener->result, $event))) {
      $statement->result = $result;
    }

  }
  $statement->timestamp = date('c', $event->timecreated);
  echo "<div style='float: left; width: 49%;'><h4>".get_string('statement', 'local_xapievent')."</h4><textarea style='width: 100%; min-height: 300px;'>" . json_encode($statement, JSON_PRETTY_PRINT) . "</textarea></div>";
} catch (Exception $ex) {
  echo "Error building statement";
  print_r($ex);
}

echo "<div style='float: left; width: 49%;'><h4>".get_string('post', 'local_xapievent')."</h4>";
if ($post) {
  echo "<textarea style='width: 100%; min-height: 300px;'>";
  local_xapievent_post(json_encode($statement), true);
  echo "</textarea>";
} else {
  echo "<a href='{$CFG->wwwroot}/local/xapievent/test.php?id={$id}&post=1'>".get_string('poststatement', 'local_xapievent')."</a>";
}
echo "</div>";

echo $OUTPUT->footer();
