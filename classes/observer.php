<?php

namespace local_xapievent;

global $CFG;

require_once($CFG->dirroot . '/local/xapievent/lib.php');
require_once($CFG->dirroot . '/local/xapievent/locallib.php');

use stdClass;
use local_xapievent_post;
use local_xapievent_allow_process;
use IMPERSONATE_ONLY;
use IMPERSONATE_DENY;

//define('BATCH_MAX_SIZE', 1000);

/**
 * Event observer for course
 */
class observer {

  final static function route($originalevent, $listenerid = null) {
    global $DB;

    // Return early if not enabled, or no listeners
    $config = get_config('local_xapievent');
    if (!isset($config->enabled) || !$config->enabled) {
      return;
    }
    // Can't modify event, so make a copy
    $event = new stdClass();
    $event->id = 0;
    foreach ($originalevent as $key=>$value) {
      $event->$key = $value;
    }
    if ($listenerid) {
      $listeners = $DB->get_records('xapievent_listener', ['id'=>$listenerid]);
    } else {
      $listeners = $DB->get_records('xapievent_listener', ['eventname'=>$event->eventname]);
    }
    if (!$listeners) {
      return;
    }

    local_xapievent_extend_event($event);
    foreach ($listeners as $listener) {
      // Check if impersonated and listener allows impersonation
      switch ($listener->impersonate) {
        case IMPERSONATE_DENY: {
          if (isset($event->realuserid)) {
            continue;
          }
        }
        case IMPERSONATE_ONLY: {
          if (!isset($event->realuserid)) {
            continue;
          }
        }
      }
      $errors = [];
      try {
        if (!$actor = json_decode(local_xapievent_fill_template($listener->actor, $event))) {
          $errors[] = get_string('error:decode', 'local_xapievent', get_string('actor', 'local_xapievent'));
        }
        if (!$verb = json_decode(local_xapievent_fill_template($listener->verb, $event))) {
          $errors[] = get_string('error:decode', 'local_xapievent', get_string('verb', 'local_xapievent'));
        }
        if (!$object = json_decode(local_xapievent_fill_template($listener->object, $event))) {
          $errors[] = get_string('error:decode', 'local_xapievent', get_string('object', 'local_xapievent'));
        }
        if (!$version = json_decode(local_xapievent_fill_template($listener->version, $event))) {
          $errors[] = get_string('error:decode', 'local_xapievent', get_string('version', 'local_xapievent'));
        }
        $statement = new stdClass();
        $statement->id = local_xapievent_get_uuid();
        $statement->actor = $actor;
        $statement->verb = $verb;
        $statement->object = $object;
        $statement->version = $version;
        if ($listener->attachments) {
          if (!$attachments = json_decode(local_xapievent_fill_template($listener->attachments, $event))) {
            $errors[] = get_string('error:decode', 'local_xapievent', get_string('attachments', 'local_xapievent'));
          }
          $statement->attachments = $attachments;
        }
        if ($listener->context) {
          if (!$context = json_decode(local_xapievent_fill_template($listener->context, $event))) {
            $errors[] = get_string('error:decode', 'local_xapievent', get_string('context', 'local_xapievent'));
          }
          $statement->context = $context;
        }
        if ($listener->result) {
          if ($result = json_decode(local_xapievent_fill_template($listener->result, $event))) {
            $statement->result = $result;
          }
        }
        $statement->timestamp = date('c', $event->timecreated);
        self::queue_statement($event, $listener, $statement, $errors);
      } catch (Exception $ex) {
        throw $ex;
      }
    }
  }

    /**
     * Process historic events from logstore_standard_log.
     *
     * @global type $DB
     * @param string    $eventname   classname of event or '*' for all events
     * @param int       $startdate   timestamp (non-inclusive)
     * @param int       $enddate     timestamp (non-inclusive)
     * @param bool      $recursive   should continue processing in batches by limit
     * @param int       $limit       number of records per batch
     * @return array with timestamp (timecreated of last record processed) and integer (number of records remaining to process)
     */
    final static function observe_historic_logstore($listenerid, $id = 0) {
      global $DB;

      $config = get_config('local_xapievent');
      $listener = $DB->get_record('xapievent_listener', ['id'=>$listenerid]);
      $events = $DB->get_records_sql(
          "SELECT *
              FROM {logstore_standard_log}
              WHERE id > :id
              and eventname = :eventname
              ORDER BY id ASC
              LIMIT {$config->historicbatchsize}",
              array('eventname'=>$listener->eventname, 'id'=>$id)
      );
      $last = false;
      foreach ($events as $event) {
          $last = $event->id;
          self::route($event, $listenerid);
      }
      unset($events);
      return $last;
    }

    final public static function process_queue($id = null) {
      global $DB;

      $config = get_config('local_xapievent');
      if ($id) {
        $queued = $DB->get_records('xapievent_queue', ['id'=>$id]);
      } else {
        $queued = $DB->get_records_sql(
          "select * from {xapievent_queue}
          where builderror is null
          and sendcount <= {$config->maxretries}
          limit {$config->ratelimit}");
      }

      if (!$queued) {
        return;
      }
      $batch = [];
      foreach ($queued as $item) {
        $batch[] = json_decode($item->statement);
      }
      $response = local_xapievent_post(json_encode($batch));
      $response = json_encode($response);
      if (!$response) {
        foreach ($queued as $statement) {
          self::process_error($statement, $response);
        }
      } else {
        foreach ($queued as $statement) {
          $DB->delete_records('xapievent_queue', array('id'=>$statement->id));
        }
      }
    }

    final static function process_error($statement, $response) {
        global $DB;

        $message = $response->content;
        $statement->senderror = (is_array($message)) ? implode('\n', $message) : $message;
        $statement->sendcount = $statement->sendcount + 1;
        $DB->update_record('xapievent_queue', $statement);
    }

    final static function queue_statement($event, $listener, $statement, $errors) {
        global $DB;

        $record = new stdClass();
        $record->listenerid = $listener->id;
        $record->eventdata = json_encode($event);
        $record->builderror = count($errors) > 0 ? implode(', ', $errors) : null;
        $record->statement = json_encode($statement, JSON_PRETTY_PRINT);
        $record->senderror = null;
        $record->sendcount = 0;

        $DB->insert_record('xapievent_queue', $record);
    }
}
