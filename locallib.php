<?php

require_once('lib.php');
require_once("{$CFG->libdir}/tablelib.php");

class local_xapievent_queue_table extends table_sql {
  function col_listenerid($record) {
    global $DB;

    $listener = $DB->get_record('xapievent_listener', ['id'=>$record->listenerid]);
    return "{$listener->name} ({$listener->eventname})";
  }
  function col_statement($record) {
    return json_encode(json_decode($record->statement), JSON_PRETTY_PRINT);
  }
  function col_action($record) {
    global $CFG;

    $config = get_config('local_xapievent');
    $sesskey = sesskey();
    $rebuild = empty($record->builderror)
      ? ''
      : "<a href='{$CFG->wwwroot}/local/xapievent/queue.php?id={$record->id}&action=rebuild&sesskey={$sesskey}'>Rebuild</a><br>";
    $resend = $record->builderror || $record->sendcount < $config->maxretries
      ? ''
      : "<a href='{$CFG->wwwroot}/local/xapievent/queue.php?id={$record->id}&action=resend&sesskey={$sesskey}'>Send</a><br>";
    $remove = "<a href='{$CFG->wwwroot}/local/xapievent/queue.php?id={$record->id}&action=dequeue&sesskey={$sesskey}'>Remove</a>";
    return "{$rebuild}{$resend}{$remove}";
  }
}

class local_xapievent_listeners_table extends table_sql {
  function col_enabled($record) {
    return $record->enabled ? get_string('yes') : get_string('no');
  }
  function templatename($value) {
    global $DB, $CFG;

    if ($value < 1) {
      return '';
    }
    if ($record = $DB->get_record('xapievent_template', ['id'=>$value])) {
      return "<a href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$record->id}'>$record->shortname</a>";
    }
    return '';
  }
  function col_name($record) {
    global $CFG;

    return "<a href='{$CFG->wwwroot}/local/xapievent/listener/edit.php?id={$record->id}'>$record->name</a>";
  }
  function col_actor($record) {
    return $this->templatename($record->actor);
  }
  function col_verb($record) {
    return $this->templatename($record->verb);
  }
  function col_object($record) {
    return $this->templatename($record->object);
  }
  function col_attachments($record) {
    return $this->templatename($record->attachments);
  }
  function col_context($record) {
    return $this->templatename($record->context);
  }
  function col_result($record) {
    return $this->templatename($record->result);
  }
  function col_version($record) {
    return $this->templatename($record->version);
  }
  function col_action($record) {
    global $CFG;

    $sesskey = sesskey();
    if ($record->enabled) {
      return "<a href='{$CFG->wwwroot}/local/xapievent/listeners.php?id={$record->id}&action=observehistoric&sesskey={$sesskey}'>" . get_string('observehistoric', 'local_xapievent') . "</a>";
    }
    return "<a href='{$CFG->wwwroot}/local/xapievent/listeners.php?id={$record->id}&action=delete&sesskey={$sesskey}'>" . get_string('delete') . "</a>";
  }
}

class local_xapievent_templates_table extends table_sql {

  function col_name($record) {
    global $CFG;

    return "<a href='{$CFG->wwwroot}/local/xapievent/template/edit.php?id={$record->id}'>$record->name</a>";
  }

  function col_datatype($record) {
    switch ($record->datatype) {
      case DATATYPE_SINGLE: {
        return get_string('single', 'local_xapievent');
      }
      case DATATYPE_ARRAY: {
        return get_string('array', 'local_xapievent');
      }
      default: return '';
    }
  }

  function col_property($record) {
    switch ($record->property) {
      case PROPERTY_VERB: {
        return get_string('verb', 'local_xapievent');
      }
      case PROPERTY_RESULT: {
        return get_string('result', 'local_xapievent');
      }
      case PROPERTY_VERSION: {
        return get_string('version', 'local_xapievent');
      }
      case PROPERTY_SUBPROPERTY: {
        return get_string('subproperty', 'local_xapievent');
      }
      case PROPERTY_OBJECT: {
        return get_string('object', 'local_xapievent');
      }
      case PROPERTY_CONTEXT: {
        return get_string('context', 'local_xapievent');
      }
      case PROPERTY_ATTACHMENTS: {
        return get_string('attachments', 'local_xapievent');
      }
      case PROPERTY_ACTOR: {
        return get_string('actor', 'local_xapievent');
      }
    }
    return $record->property;
  }

  function used_by($id) {
    global $DB;

    return $DB->get_records_sql("select id, name from {xapievent_listener} where actor = {$id} or verb = {$id} or object = {$id} or version = {$id} or attachments = {$id} or context = {$id} or result = {$id}");
  }

  function referred_by($shortname) {
    global $DB;
    $like = $DB->sql_like('content', ":shortname");
    return $DB->get_records_sql("select id, shortname from {xapievent_template} where {$like}", ['shortname'=>"%[[{$shortname}]]%"]);
  }

  function col_action($record) {
    global $CFG;
    $used = $this->used_by($record->id);
    $referred = $this->referred_by($record->shortname);
    $string = [];
    if ($used) {
      $string[] = '<p>Listeners using: ';
      foreach ($used as $listener) {
        $string[] = "<br><a href='{$CFG->wwwroot}/local/xapievent/listener/edit.php?id={$listener->id}'>{$listener->name}</a>";
      }
      $string[] = '</p>';
    }
    if ($referred) {
      $string[] = '<p>Templates using: ';
      foreach ($referred as $template) {
        $string[] = "<br><a href='{$CFG->wwwroot}/local/xapievent/listener/edit.php?id={$template->id}'>{$template->shortname}</a>";
      }
      $string[] = '</p>';
    }
    if (!empty($string)) {
      return implode('', $string);
    } else {
      $sesskey = sesskey();
      return "<a href='{$CFG->wwwroot}/local/xapievent/templates.php?id={$record->id}&action=delete&sesskey={$sesskey}'>" . get_string('delete') . "</a>";
    }
  }
}

function local_xapievent_extend_event(&$event) {
  global $DB;

  if (isset($event->objecttable) && $objecttable = $DB->get_record($event->objecttable, ['id'=>$event->objectid])) {
    local_xapievent_extend_event_with_table($objecttable, $event, $event->objecttable);
  }
  $userfields = implode(',', array_merge(['username', 'email'], get_all_user_name_fields()));
  if (isset($event->userid) && $user = $DB->get_record('user', ['id'=>$event->userid], $userfields)) {
    $user->fullname = fullname($user);
    local_xapievent_extend_event_with_table($user, $event, 'user');
  }
  if (isset($event->relateduserid) && $relateduser = $DB->get_record('user', ['id'=>$event->relateduserid], $userfields)) {
    $relateduser->fullname = fullname($relateduser);
    local_xapievent_extend_event_with_table($relateduser, $event, 'relateduser');
  }
  if (isset($event->realuserid) && $realuser = $DB->get_record('user', ['id'=>$event->realuserid], $userfields)) {
    $realuser->fullname = fullname($realuser);
    local_xapievent_extend_event_with_table($realuser, $event, 'realuser');
  }
  if (isset($event->courseid) && $course = $DB->get_record('course', ['id'=>$event->courseid])) {
    local_xapievent_extend_event_with_table($course, $event, 'course');
    if ($category = $DB->get_record('course_categories', ['id'=>$course->category])) {
      local_xapievent_extend_event_with_table($category, $event, 'category');
    }
  }

  if (!isset($event->contextlevel)) {
    return;
  }
  switch ($event->contextlevel) {
    case CONTEXT_SYSTEM: {
      break;
    }
    case CONTEXT_USER: {
      break;
    }
    case CONTEXT_COURSECAT: {
      break;
    }
    case CONTEXT_COURSE: {
      break;
    }
    case CONTEXT_MODULE: {
      if ($cm = $DB->get_record('course_modules', ['id'=>$event->contextinstanceid])) {
        local_xapievent_extend_event_with_table($cm, $event, 'course_modules');
        if ($modules = $DB->get_record('modules', ['id'=>$cm->module])) {
          local_xapievent_extend_event_with_table($modules, $event, 'modules');
          if ($instance = $DB->get_record($modules->name, ['id'=>$cm->instance])) {
            local_xapievent_extend_event_with_table($instance, $event, 'instance');
          }
          if ($gradeitem = $DB->get_record('grade_items', ['courseid'=>$event->courseid,'iteminstance'=>$cm->instance,'itemmodule'=>$modules->name])) {
            local_xapievent_extend_event_with_table($gradeitem, $event, 'grade_item');
            $grade_grades = $DB->get_record('grade_grades', ['itemid'=>$gradeitem->id,'userid'=>$event->userid]);
            if ($grade_grades) {
              local_xapievent_extend_event_with_table($grade_grades, $event, 'grade_grades');
            }
          }
        }
      }
      break;
    }
    case CONTEXT_BLOCK: {
      break;
    }
  }
}

function local_xapievent_extend_event_with_table($record, &$event, $prefix) {
  global $DB;

  if (!$record) {
    return;
  }
  foreach($record as $key=>$value) {
    $param = "{$prefix}_{$key}";
    $value = is_string($value) ? strip_tags($value) : $value;
    $event->$param = $value;
  }
}

function local_xapievent_get_uuid() {
  //
  // Based on code from
  // http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
  //
  $randomString = openssl_random_pseudo_bytes(16);
  $time_low = bin2hex(substr($randomString, 0, 4));
  $time_mid = bin2hex(substr($randomString, 4, 2));
  $time_hi_and_version = bin2hex(substr($randomString, 6, 2));
  $clock_seq_hi_and_reserved = bin2hex(substr($randomString, 8, 2));
  $node = bin2hex(substr($randomString, 10, 6));

  /**
   * Set the four most significant bits (bits 12 through 15) of the
   * time_hi_and_version field to the 4-bit version number from
   * Section 4.1.3.
   * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
  */
  $time_hi_and_version = hexdec($time_hi_and_version);
  $time_hi_and_version = $time_hi_and_version >> 4;
  $time_hi_and_version = $time_hi_and_version | 0x4000;

  /**
   * Set the two most significant bits (bits 6 and 7) of the
   * clock_seq_hi_and_reserved to zero and one, respectively.
   */
  $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
  $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
  $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

  return sprintf(
      '%08s-%04s-%04x-%04x-%012s',
      $time_low,
      $time_mid,
      $time_hi_and_version,
      $clock_seq_hi_and_reserved,
      $node
  );
}

function local_xapievent_post($json_statement, $test = false) {
    $config = get_config('local_xapievent');
    // Silently fail if not configured
    if (!($config->lrsurl && $config->lrsusername && $config->lrsuserpass)) {
      return null;
    }
    $url = $test ? "{$config->lrsurltest}statements" : "{$config->lrsurl}statements";

    $password = $test ? $config->lrsusernametest . ":" . $config->lrsuserpasstest : $config->lrsusername . ":" . $config->lrsuserpass;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, $password);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json; charset=utf-8",
        "X-Experience-API-Version: 1.0.0"
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_statement);

    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    if ($info['http_code'] == 200 && empty($response)) {
        return $response;
    }
    $info = curl_getinfo($ch);
    if ($info['http_code'] > 250) {
        throw new Exception($response);
    }
    return $response;
}

function local_xapievent_fill_template($templateid, $event) {
  global $DB, $CFG;

  $record = $DB->get_record('xapievent_template', ['id'=>$templateid]);
  $content = $record->content;
  // replace any event info
  foreach ($event as $key=>$value) {
    $content = str_replace("[[$key]]", $value, $content);
  }
  // replace wwwroot
  $content = str_replace('[[wwwroot]]', $CFG->wwwroot, $content);
  // fill from query
  if ($record->query) {
    if ($record->property == 4 || $record->datatype == 1) { // prop 4 is attachments
      $attachments = [];
      $results = $DB->get_records_sql($record->query, (array)$event);
      foreach ($results as $result) {
        $attachments[] = local_xapievent_push_results($result, $content);
      }
      $content = '[' . implode(', ', $attachments) . ']';
    } else {
      if ($result = $DB->get_record_sql($record->query, (array)$event, IGNORE_MULTIPLE)) {
        $content = local_xapievent_push_results($result, $content);
      }
    }
  }
  // fill from subproperty
  $subproperties = [];
  preg_match('/\[\[(.*)\]\]/', $content, $subproperties);
  if (!empty($subproperties)) {
    $keys = explode(',', $subproperties[0]);
    $templatename = explode(',', $subproperties[1]);
    for ($i = 0; $i < count($keys); $i++) {
      $template = $DB->get_record('xapievent_template', ['shortname'=>$templatename[$i]]);
      if ($template) {
        $content = str_replace($keys[$i], local_xapievent_fill_template($template->id, $event), $content);
      } else {
        $content = str_replace($keys[$i], '', $content);
      }
    }
  }
  // remove trailling commas
  $content = preg_replace('/(,\s*)}/', '}', $content);
  $content = preg_replace('/(,\s*)\]/', ']', $content);
  $content = preg_replace('/(,\s*)\]/', ',', $content);

  return $content;
}

function local_xapievent_push_results($result, $content) {
  foreach ($result as $key=>$value) {
    $parts = explode('|', $key);
    if (count($parts) > 1 && $parts[1] == 'template-blank-if-empty') {
      if (empty($value)) {
        return '';
      }
    } else if (count($parts) > 1 && $parts[1] == 'duration') {
      // as years, weeks, days, hours, seconds
      $second = 1;
      $minute = $second * 60;
      $hour = $minute * 60;
      $day = $hour * 24;
      $week = $day * 7;
      $year = $week * 52;
      $duration = "P";
      $years = floor($value / $year);
      $value = $value - ($years * $year);
      $weeks = floor($value / $week);
      $value = $value - ($weeks * $week);
      $days = floor($value / $day);
      $value = $value - $days * $day;
      $hours = floor($value / $hour);
      $value = $value - $hours * $hours;
      $minutes = floor($value / $minute);
      $value = $value - $minutes * $minute;
      $seconds = $value;
      if ($years > 0) {
        $duration .= "{$years}Y";
      }
      if ($weeks > 0) {
        $duration .= "{$weeks}W";
      }
      if ($days > 0) {
        $duration .= "{$days}D";
      }
      if ($hours > 0 || $minutes > 0 || $seconds > 0) {
        $duration .= "T";
      }
      if ($hours > 0) {
        $duration .= "{$hours}H";
      }
      if ($minutes > 0) {
        $duration .= "{$minutes}M";
      }
      $duration .= "{$seconds}S";
      $duration = $duration == "P0S" ? "PT0S" : "P0S";
      $value = $duration;
    } else if (count($parts) > 1 && $parts[1] == 'date') {
      $value = date('c', $value);
    }

    $content = str_replace("[[$key]]", $value, $content);
  }
  return $content;
}
