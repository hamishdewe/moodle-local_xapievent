<?php

namespace local_xapievent;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once('../lib.php');

use moodleform;

/**
 * Class audience_form
 */
class template_form extends moodleform {

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;

        $textarea = ['rows'=>10,'cols'=>80];
        $property = [
          PROPERTY_ACTOR=>get_string('actor', 'local_xapievent'), PROPERTY_VERB=>get_string('verb', 'local_xapievent'),
          PROPERTY_OBJECT=>get_string('object', 'local_xapievent'), PROPERTY_VERSION=>get_string('version', 'local_xapievent'),
          PROPERTY_ATTACHMENTS=>get_string('attachments', 'local_xapievent'), PROPERTY_CONTEXT=>get_string('context', 'local_xapievent'),
          PROPERTY_RESULT=>get_string('result', 'local_xapievent'), PROPERTY_SUBPROPERTY=>get_string('subproperty', 'local_xapievent')];
        $datatype = [DATATYPE_SINGLE=>get_string('single', 'local_xapievent'),DATATYPE_ARRAY=>get_string('array', 'local_xapievent')];
        $mform->addElement('header', 'header_template', get_string('template', 'local_xapievent'));
        $mform->addElement('text', 'name', get_string('name', 'local_xapievent'), ['size'=>80]);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setType('name', PARAM_TEXT);
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_xapievent'), ['size'=>80]);
        $mform->setType('shortname', PARAM_ALPHANUMEXT);
        $mform->addElement('select', 'property', get_string('statementproperty', 'local_xapievent'), $property);
        $mform->addElement('select', 'datatype', get_string('datatype', 'local_xapievent'), $datatype);
        $mform->addElement('textarea', 'content', get_string('content', 'local_xapievent'), $textarea);  // JSON template
        $mform->setType('content', PARAM_TEXT);
        $mform->addElement('textarea', 'query', get_string('query', 'local_xapievent'), $textarea); // Uses event params
        $mform->setType('query', PARAM_TEXT);

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        return $errors;
    }

}
