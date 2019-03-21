<?php

namespace local_xapievent;

defined('MOODLE_INTERNAL') || die();

define('PROPERTY_ACTOR', 0);
define('PROPERTY_VERB', 1);
define('PROPERTY_OBJECT', 2);
define('PROPERTY_VERSION', 3);
define('PROPERTY_ATTACHMENTS', 4);
define('PROPERTY_CONTEXT', 5);
define('PROPERTY_RESULT', 6);
define('PROPERTY_SUBPROPERTY', 7);

define('DATATYPE_SINGLE', 0);
define('DATATYPE_ARRAY', 1);

define('IMPERSONATE_DENY', 0);
define('IMPERSONATE_ONLY', 1);
define('IMPERSONATE_BOTH', 2);
