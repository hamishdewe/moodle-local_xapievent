<?php

namespace local_xapievent;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once('../lib.php');

use moodleform;
use report_eventlist_list_generator;

/**
 * Class audience_form
 */
class listener_form extends moodleform {

    function get_select($property) {
      global $DB;

      $arr = ['0' => get_string('choosedots')];
      foreach($DB->get_records('xapievent_template', ['property'=>$property], 'name') as $record) {

        $arr[$record->id] = $record->name;
      }
      return $arr;
    }

    /**
     * Form definition.
     */
    protected function definition() {
        global $CFG;

        $mform = $this->_form;

        $impersonate = [
          IMPERSONATE_DENY=>get_string('impersonate_donotprocess', 'local_xapievent'),
          IMPERSONATE_ONLY=>get_string('impersonate_onlyprocess', 'local_xapievent'),
          IMPERSONATE_BOTH=>get_string('impersonate_processboth', 'local_xapievent')
        ];

        $events = [];
        foreach (report_eventlist_list_generator::get_all_events_list(true) as $event) {
          //var_dump($event); die;
          if ($event['eventname'] && $event['fulleventname']) {
            //$events[$event['eventname']] = explode('\\', strip_tags($event['fulleventname']))[0] . ' ' . $event['eventname'];
            $events[$event['eventname']] = $event['eventname'];
          }
        }
        if ($id = optional_param('id', null, PARAM_INT)) {
          $mform->addElement('html',
            "<div><a class='btn btn-warning text-white' href='{$CFG->wwwroot}/local/xapievent/test.php?id={$id}' target='_blank'>Test listener</a></div>");
        }
        $mform->addElement('header', 'header_listener', get_string('listener', 'local_xapievent'));
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('text', 'name', get_string('name', 'local_xapievent'), ['size'=>60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addElement('autocomplete','eventname',get_string('eventname', 'local_xapievent'),
            array_merge(['' => get_string('choosedots')], $events),
            ['noselectionstring' => get_string('choosedots')]
        );
        $mform->addElement('select', 'impersonate', get_string('impersonatedbehaviour', 'local_xapievent'), $impersonate);
        $mform->addElement('autocomplete','actor', get_string('atemplate', 'local_xapievent', get_string('actor', 'local_xapievent')),
            $this->get_select(PROPERTY_ACTOR),
            ['noselectionstring' => get_string('choosedots')]
        );
        $mform->addElement('autocomplete','verb',get_string('atemplate', 'local_xapievent', get_string('verb', 'local_xapievent')),
            $this->get_select(PROPERTY_VERB),
            ['noselectionstring' => get_string('choosedots')]
        );
        $mform->addElement('autocomplete','object',get_string('atemplate', 'local_xapievent', get_string('object', 'local_xapievent')),
            $this->get_select(PROPERTY_OBJECT),
            ['noselectionstring' => get_string('choosedots')]
        );
        $mform->addElement('autocomplete','version',get_string('atemplate', 'local_xapievent', get_string('version', 'local_xapievent')),
            $this->get_select(PROPERTY_VERSION),
            ['noselectionstring' => get_string('choosedots')]
        );
        $mform->addElement('autocomplete','attachments',get_string('atemplate', 'local_xapievent', get_string('attachments', 'local_xapievent')),
            $this->get_select(PROPERTY_ATTACHMENTS),
            ['noselectionstring' => get_string('choosedots')]
        );
        $mform->addElement('autocomplete','context',get_string('atemplate', 'local_xapievent', get_string('context', 'local_xapievent')),
            $this->get_select(PROPERTY_CONTEXT),
            ['noselectionstring' => get_string('choosedots')]
        );
        $mform->addElement('autocomplete','result', get_string('atemplate', 'local_xapievent', get_string('result', 'local_xapievent')),
            $this->get_select(PROPERTY_RESULT),
            ['noselectionstring' => get_string('choosedots')]
        );
        $mform->addElement('advcheckbox', 'enabled', '', get_string('enabled', 'local_xapievent'));

        $mform->addRule('eventname', get_string('error'), 'required');
        $mform->addRule('actor', get_string('error'), 'required');
        $mform->addRule('verb', get_string('error'), 'required');
        $mform->addRule('object', get_string('error'), 'required');
        $mform->addRule('version', get_string('error'), 'required');

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        return $errors;
    }

}
