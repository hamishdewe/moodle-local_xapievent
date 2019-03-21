<?php
require_once($CFG->dirroot . '/local/xapievent/classes/defaults.php');

function xmldb_local_xapievent_upgrade($oldversion) {
    global $DB;

    \local_xapievent\defaults::insert_defaults();
    //$dbman = $DB->get_manager();

    if ($oldversion < 0) {

        // //$dbman->drop_table(new xmldb_table('tincantranslator_track'));
        //
        // // Define table tincantranslator_queue to be created.
        // $table = new xmldb_table('tincantranslator_queue');
        //
        // // Adding fields to table tincantranslator_queue.
        // $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        // $table->add_field('statement', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        // $table->add_field('sendtype', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'statement');
        // $table->add_field('queuedtime', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        // $table->add_field('lastsendtime', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        // $table->add_field('lastsendcode', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        // $table->add_field('lastsendmessage', XMLDB_TYPE_TEXT, null, null, null, null, null);
        // $table->add_field('sendattemptcount', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        //
        // // Adding keys to table tincantranslator_queue.
        // $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        //
        // // Conditionally launch create table for tincantranslator_queue.
        // if (!$dbman->table_exists($table)) {
        //     $dbman->create_table($table);
        // }
        //
        // // Tincantranslator savepoint reached.
        // upgrade_plugin_savepoint(true, 2017092700, 'local', 'tincantranslator');
    }

    return true;
}
