<?php

global $DB;

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $category_name='xapievent_category';
    $category = new admin_category($category_name, get_string('pluginname', 'local_xapievent'));
    $ADMIN->add('localplugins',$category);

    $config = new admin_settingpage('local_xapievent', get_string('generalsettings', 'local_xapievent'));
    $config->add(new admin_setting_configtext(
            'local_xapievent/lrsurl',
            get_string('lrsurl', 'local_xapievent'),
            '',
            null,
            PARAM_TEXT));
    $config->add(new admin_setting_configtext(
            'local_xapievent/lrsusername',
            get_string('lrsusername', 'local_xapievent'),
            '',
            null,
            PARAM_TEXT));
    $config->add(new admin_setting_configtext(
            'local_xapievent/lrsuserpass',
            get_string('lrsuserpass', 'local_xapievent'),
            '',
            null,
            PARAM_TEXT));
    $config->add(new admin_setting_configcheckbox('local_xapievent/enabled', get_string('enabled', 'local_xapievent'), '', 0));
    $config->add(new admin_setting_configtext('local_xapievent/ratelimit', get_string('ratelimit', 'local_xapievent'), '', 15, PARAM_INT));
    $config->add(new admin_setting_configtext('local_xapievent/historicbatchsize', get_string('historicbatchsize', 'local_xapievent'), '', 10000, PARAM_INT));
    $config->add(new admin_setting_configtext('local_xapievent/maxretries', get_string('maximumretries', 'local_xapievent'), '', 3, PARAM_INT));
    $config->add(new admin_setting_heading('local_xapievent/testsettings', get_string('testsettings', 'local_xapievent'), ''));
    $config->add(new admin_setting_configtext(
            'local_xapievent/lrsurltest',
            get_string('lrsurl', 'local_xapievent'),
            '',
            null,
            PARAM_TEXT));
    $config->add(new admin_setting_configtext(
            'local_xapievent/lrsusernametest',
            get_string('lrsusername', 'local_xapievent'),
            '',
            null,
            PARAM_TEXT));
    $config->add(new admin_setting_configtext(
            'local_xapievent/lrsuserpasstest',
            get_string('lrsuserpass', 'local_xapievent'),
            '',
            null,
            PARAM_TEXT));
    $ADMIN->add($category_name, $config);

    $listeners = new admin_externalpage('listeners', get_string('listeners', 'local_xapievent'),
        $CFG->wwwroot . '/local/xapievent/listeners.php' );
    $ADMIN->add($category_name, $listeners);

    $templates = new admin_externalpage('templates', get_string('templates', 'local_xapievent'),
        $CFG->wwwroot . '/local/xapievent/templates.php' );
    $ADMIN->add($category_name, $templates);

    $queue = new admin_externalpage('queue', get_string('statementqueue', 'local_xapievent'), $CFG->wwwroot . '/local/xapievent/queue.php');
    $ADMIN->add($category_name, $queue);
}
