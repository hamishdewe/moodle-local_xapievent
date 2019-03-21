<?php

// Define template default values
namespace local_xapievent;

require_once($CFG->dirroot . '/local/xapievent/lib.php');

use stdClass;

class defaults {

  static function insert_defaults() {
    global $DB;

    $records = self::verb_all();
    $records[] = self::actor_realuser_account();
    $records[] = self::actor_realuser_mbox();
    $records[] = self::actor_relateduser_account();
    $records[] = self::actor_relateduser_mbox();
    $records[] = self::actor_user_account();
    $records[] = self::actor_user_mbox();
    $records[] = self::attachment_supporting_media();
    $records[] = self::context_activity();
    $records[] = self::context_block();
    $records[] = self::context_category();
    $records[] = self::object_assign_submission();
    $records[] = self::object_course_minimal();
    $records[] = self::object_course_module();
    $records[] = self::result_completion();
    $records[] = self::result_completion_duration();
    $records[] = self::result_course_module();
    $records[] = self::sub_extension_tags();
    $records[] = self::version_100();

    $templates = [];
    foreach ($records as $record) {
      $row = $DB->get_record('xapievent_template', ['shortname'=>$record->shortname]);
      if (!$row) {
        $templates[$record->shortname] = $DB->insert_record('xapievent_template', $record);
      } else {
        $templates[$record->shortname] = $row->id;
      }
    }
    $listeners = [];
    $listeners[] = (object)[
      "name"=>"Course completed",
      "eventname"=>'\core\event\course_completed',
      "impersonate"=>IMPERSONATE_DENY,
      "actor"=>$templates['actor-user-account'],
      "verb"=>$templates['verb-completed'],
      "object"=>$templates['object-course-minimal'],
      "version"=>$templates['version-100'],
      "attachments"=>0,
      "context"=>$templates['context-category'],
      "result"=>$templates['result-completion-duration'],
      "enabled"=>0];
    $listeners[] = (object)[
      "name"=>"User enrolled",
      "eventname"=>'\core\event\user_enrolment_created',
      "impersonate"=>IMPERSONATE_DENY,
      "actor"=>$templates['actor-user-account'],
      "verb"=>$templates['verb-start'],
      "object"=>$templates['object-course-minimal'],
      "version"=>$templates['version-100'],
      "attachments"=>0,
      "context"=>$templates['context-category'],
      "result"=>0,
      "enabled"=>0];
    $listeners[] = (object)[
      "name"=>"User assign submission",
      "eventname"=>'\mod_assign\event\assessable_submitted',
      "impersonate"=>IMPERSONATE_DENY,
      "actor"=>$templates['actor-user-account'],
      "verb"=>$templates['verb-submit'],
      "object"=>$templates['object-assign-submission'],
      "version"=>$templates['version-100'],
      "attachments"=>$templates['attachment-supporting-media'],
      "context"=>$templates['context-activity'],
      "result"=>0,
      "enabled"=>0];
    $listeners[] = (object)[
      "name"=>"Course module completion updated",
      "eventname"=>'\core\event\course_module_completion_updated',
      "impersonate"=>IMPERSONATE_DENY,
      "actor"=>$templates['actor-user-account'],
      "verb"=>$templates['verb-attempted'],
      "object"=>$templates['object-course-module'],
      "version"=>$templates['version-100'],
      "attachments"=>0,
      "context"=>$templates['context-activity'],
      "result"=>$templates['result-course-module'],
      "enabled"=>0];

    foreach ($listeners as $record) {
      $row = $DB->get_record('xapievent_listener', ['name'=>$record->name]);
      if (!$row) {
        $templates[$record->name] = $DB->insert_record('xapievent_listener', $record);
      } else {
        $templates[$record->name] = $row->id;
      }
    }
  }

  static private function verb_all() {
    $verbs = [];
    foreach (['accept','access','acknowledge','add','agree','append','approve',
      'archive','assign','at','attach','attend','author','authorize','borrow',
      'build','cancel','checkin','close','complete','confirm','consume',
      'create','delete','deliver','deny','disagree','dislike','experience',
      'favorite','find','flag-as-inappropriate','follow','give','host','ignore',
      'insert','install','interact','invite','join','leave','like','listen',
      'lose','make-friend','open','play','present','purchase','qualify','read',
      'receive','reject','remove','remove-friend','replace','request',
      'request-friend','resolve','retract','return','rsvp-maybe','rsvp-no',
      'rsvp-yes','satisfy','save','schedule','search','sell','send','share',
      'sponsor','start','stop-following','submit','tag','terminate','tie',
      'unfavorite','unlike','unsatisfy','unsave','unshare','update','use',
      'watch','win'] as $verb) {
      $verbs[] = self::verb_generic('http://activitystrea.ms/schema/1.0/', $verb);
    }
    foreach (['answered','asked','attempted','attended','commented','completed',
      'exited','experienced','failed','imported','initialized','interacted',
      'launched','mastered','passed','preferred','progressed','registered',
      'responded','resumed','scored','shared','suspended','terminated','voided'
      ] as $verb) {
      $verbs[] = self::verb_generic('http://adlnet.gov/expapi/verbs/', $verb);
    }
    foreach (['edited','voted-down','voted-up'] as $verb) {
      $verbs[] = self::verb_generic('http://curatr3.com/define/verb/', $verb);
    }
    foreach (['pressed','released'] as $verb) {
      $verbs[] = self::verb_generic('http://future-learning.info/xAPI/verb/', $verb);
    }
    foreach (['adjourned','applauded','arranged','bookmarked','called',
      'closed-sale','created-opportunity','defined','disabled','discarded',
      'downloaded','earned','enabled','estimated-duration','expected','expired',
      'focused','frame/entered','frame/exited','hired','interviewed','laughed',
      'marked-unread','mentioned','mentored','paused','performed-offline',
      'personalized','previewed','promoted','rated','replied',
      'replied-to-tweet','requested-attention','retweeted','reviewed','secured',
      'selected','skipped','talked-with','terminated-employment-with','tweeted',
      'unfocused','unregistered','viewed','voted-down','voted-up',
      'was-assigned-job-title','was-hired-by'] as $verb) {
      $verbs[] = self::verb_generic('http://id.tincanapi.com/verb/', $verb);
    }
    foreach (['annotated','modified'] as $verb) {
      $verbs[] = self::verb_generic('http://risc-inc.com/annotator/verbs/', $verb);
    }
    $verbs[] = self::verb_generic('http://specification.openbadges.org/xapi/verbs/', 'earned');
    $verbs[] = self::verb_generic('http://www.digital-knowledge.co.jp/tincanapi/verbs/', 'drew');
    foreach (['cancelled_planned_learning','planned_learning'] as $verb) {
      $verbs[] = self::verb_generic('http://www.tincanapi.co.uk/pages/verbs.html#', $verb);
    }
    foreach (['enrolled_onto_learning_plan','evaluated'] as $verb) {
      $verbs[] = self::verb_generic('http://www.tincanapi.co.uk/verbs/', $verb);
    }
    foreach (['added','loggedin','loggedout','ran','removed','reviewed',
      'walked'] as $verb) {
      $verbs[] = self::verb_generic('https://brindlewaye.com/xAPITerms/verbs/', $verb);
    }
    return $verbs;
  }

  static private function verb_generic($base, $name, $localised = [], $display = null) {
    $o = new stdClass();
    $display = $display ? $display : $name;
    $extralang = '';
    if (!empty($localised)) {
      $arr = [];
      foreach ($localised as $lang=>$text) {
        $arr[] = "\"$lang\": \"$text\"";
      }
      $extralang = ',' . implode(',', $arr);
    }
    $o->name = "Verb: {$name}";
    $o->shortname = "verb-{$name}";
    $o->property = PROPERTY_VERB;
    $o->datatype = DATATYPE_SINGLE;
    $o->content = '{
      "id": "' . $base . $name . '",
      "display": {
        "en-US": "' . $display . '"' . $extralang . '
      }
    }';
    return $o;
  }

  // Templates
  static private function actor_realuser_account() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Actor: realuser (account)";
    $o->shortname = "actor-realuser-account";
    $o->property = PROPERTY_ACTOR;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "objectType": "Agent",
        "name": "[[realuser_fullname]]",
        "account": {
          "homePage": "[[wwwroot]]",
          "name": "[[realuser_username]]"
        }
      }';
    return $o;
  }
  static private function actor_realuser_mbox() {
    $o = new stdClass();
    $o->name = "User to actor (mbox)";
    $o->shortname = "actor-realuser-mbox";
    $o->property = PROPERTY_ACTOR;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "objectType": "Agent",
        "name": "[[realuser_fullname]]",
        "mbox": "mailto:[[realuser_email]]"
      }';
    return $o;
  }
  static private function actor_relateduser_account() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Actor: relateduser (account)";
    $o->shortname = "actor-relateduser-account";
    $o->property = PROPERTY_ACTOR;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "objectType": "Agent",
        "name": "[[relateduser_fullname]]",
        "account": {
          "homePage": "[[wwwroot]]",
          "name": "[[relateduser_username]]"
        }
      }';
    return $o;
  }
  static private function actor_relateduser_mbox() {
    $o = new stdClass();
    $o->name = "Actor: relateduser (mbox)";
    $o->shortname = "actor-relateduser-mbox";
    $o->property = PROPERTY_ACTOR;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "objectType": "Agent",
        "name": "[[relateduser_fullname]]",
        "mbox": "mailto:[[relateduser_email]]"
      }';
    return $o;
  }
  static private function actor_user_account() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Actor: user (account)";
    $o->shortname = "actor-user-account";
    $o->property = PROPERTY_ACTOR;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "objectType": "Agent",
        "name": "[[user_fullname]]",
        "account": {
          "homePage": "[[wwwroot]]",
          "name": "[[user_username]]"
        }
      }';
    return $o;
  }
  static private function actor_user_mbox() {
    $o = new stdClass();
    $o->name = "Actor: user (mbox)";
    $o->shortname = "actor-user-mbox";
    $o->property = PROPERTY_ACTOR;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "objectType": "Agent",
        "name": "[[user_fullname]]",
        "mbox": "mailto:[[user_email]]"
      }';
    return $o;
  }
  static private function attachment_supporting_media() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Supporting media";
    $o->shortname = "attachment-supporting-media";
    $o->property = PROPERTY_ATTACHMENTS;
    $o->datatype = DATATYPE_ARRAY;
    $o->content =
      '{
        "usageType": "http://id.tincanapi.com/attachment/supporting_media",
        "display": { "en-US": "[[filename]]" },
        "contentType": "[[mimetype]]",
        "length": [[filesize]],
        "sha2": "[[contenthash]]",
        "fileUrl": "[[wwwroot]]/[[contextid]]/[[component]]/[[filearea]]/[[itemid]]/[[filename]]?forcedownload=1"
      }';
    $o->query =
      'select
        filename, mimetype, filesize, contenthash, contextid, component,
        filearea, itemid, filename
      from {files}
      where contextid = :contextid
      and itemid = :objectid
      and filesize > 0';
    return $o;
  }
  static private function context_activity() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Context: activity";
    $o->shortname = "context-activity";
    $o->property = PROPERTY_CONTEXT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "contextActivities": {
          "category": [
            {
              "objectType": "Activity",
              "id": "[[wwwroot]]/course/index.php?categoryid=[[course_category]]"
            }
          ],
          "parent": [
            {
              "id": "[[wwwroot]]/course/view.php?id=[[course_id]]"
            },
            {
              "id": "[[wwwroot]]/mod/[[modules_name]]/view.php?id=[[modules_id]]"
            }
          ]
        },
        "extensions": {
          "http://id.tincanapi.com/extension/tags": [
            [[sub-extension-tags]]
          ]
        }
      }';
    return $o;
  }
  static private function context_block() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Context: block";
    $o->shortname = "context-block";
    $o->property = PROPERTY_CONTEXT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "contextActivities": {
          "category": [
            {
              "objectType": "Activity",
              "id": "[[wwwroot]]/course/index.php?categoryid=[[course_category]]"
            }
          ],
          "parent": [
            {
              "id": "[[wwwroot]]/course/view.php?id=[[course_id]]"
            }
          ]
        },
        "extensions": {
          "http://id.tincanapi.com/extension/tags": [
            [[sub-extension-tags]]
          ]
        }
      }';
    return $o;
  }
  static private function context_category() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Context (category)";
    $o->shortname = "context-category";
    $o->property = PROPERTY_CONTEXT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "contextActivities": {
          "category": [
            {
              "objectType": "Activity",
              "id": "[[wwwroot]]/course/index.php?categoryid=[[category_id]]",
              "definition": {
                "name": {
                  "en-US": "[[category_name]]"
                }
              }
            }
          ]
        },
        "extensions": {
          "http://id.tincanapi.com/extension/tags": [
            [[sub-extension-tags]]
          ]
        }
      }';
    return $o;
  }
  static private function object_assign_submission() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Object: assign submission";
    $o->shortname = "object-assign-submission";
    $o->property = PROPERTY_OBJECT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "id": "[[wwwroot]]/assign/view.php?id=[[course_modules.id]]&action=grader&userid=[[userid]]",
        "definition": {
          "name": {
            "en-US": "[[instance_name]]"
          },
          "type": "http://activitystrea.ms/schema/1.0/file"
        },
        "objectType": "Activity"
      }';
    return $o;
  }
  static private function object_course_minimal() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Object: course(minimal)";
    $o->shortname = "object-course-minimal";
    $o->property = PROPERTY_OBJECT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "id": "[[wwwroot]]/course/view.php?id=[[course_id]]",
        "definition": {
          "name": {
            "en-US": "[[course_fullname]]"
          },
          "type": "http://adlnet.gov/expapi/activities/course"
        },
        "objectType": "Activity"
      }';
    return $o;
  }
  static private function object_course_module() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Object: course_module";
    $o->shortname = "object-course-module";
    $o->property = PROPERTY_OBJECT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "objectType": "Activity",
        "id": "[[wwwroot]]/mod/[[modules_name]]/view.php?id=[[course_modules_id]]"
      }';
    return $o;
  }
  static private function result_completion() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Result: completion";
    $o->shortname = "result-completion";
    $o->property = PROPERTY_RESULT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "completion": [[completed]]
      }';
    $o->query =
      'select
        case timecompleted
          when null then \'false\'
          else \'true\'
        end "completed"
      from {course_completions}
      where userid = :userid
      and course = :courseid';
    return $o;
  }
  static private function result_completion_duration() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Completion and duration";
    $o->shortname = "result-completion-duration";
    $o->property = PROPERTY_RESULT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "completion": [[completed]],
        "duration": "[[elapsed|duration]]"
      }';
    $o->query =
      'select
        case timecompleted
          when null then \'false\'
          else \'true\'
        end "completed",
        case
          when timeenrolled  = 0 then timecompleted - timestarted
          else timecompleted - timeenrolled
        end "elapsed|duration"
      from {course_completions}
      where userid = :userid
      and course = :courseid';
    return $o;
  }
  static private function result_course_module() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Result: course module";
    $o->shortname = "result-course-module";
    $o->property = PROPERTY_RESULT;
    $o->datatype = DATATYPE_SINGLE;
    $o->content =
      '{
        "success": [[success]],
        "completion": [[completion]],
        "response": "[[response]]",
        "score": {
          "raw": [[raw|template-blank-if-empty]],
          "min": [[min|template-blank-if-empty]],
          "max": [[max|template-blank-if-empty]],
          "scaled": [[scaled|template-blank-if-empty]]
        }
      }';
    $o->query =
      'select
        case cc.completionstate
          when 1 then \'true\'
      	  when 2 then \'true\'
      	  when 3 then \'true\'
          else \'false\'
        end "completion",
        case cc.completionstate
          when 1 then \'true\'
          when 2 then \'true\'
      	  when 3 then \'false\'
      	  else \'false\'
        end "success",
        gg.rawgrademax "max|template-blank-if-empty",
        gg.rawgrademin "min|template-blank-if-empty",
        case
          when gg.rawgrade is not null then gg.rawgrade
      	else gg.finalgrade
        end "raw|template-blank-if-empty",
        case
        	when
      	  case
              when gg.rawgrade is not null then gg.rawgrade
      	    else gg.finalgrade
            end is not null then round(
      	    case
                when gg.rawgrade is not null then gg.rawgrade
      	      else gg.finalgrade
              end / gg.rawgrademax, 2)
      	  else null
        end "scaled|template-blank-if-empty",
        gg.feedback "response"
        from {course_modules_completion} cc
        join {grade_items} gi
        on gi.itemmodule = :modules_name
        and gi.iteminstance = :instance_id
        left join {grade_grades} gg
        on gg.itemid = gi.id
        where cc.coursemoduleid = :contextinstanceid
        and gg.userid = cc.userid
        and cc.userid = :userid
        and gi.itemmodule != \'reengagement\'';
    return $o;
  }
  static private function sub_extension_tags() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Extension: tags";
    $o->shortname = "sub-extension-tags";
    $o->property = PROPERTY_SUBPROPERTY;
    $o->datatype = DATATYPE_ARRAY;
    $o->content = '"[[tag|template-blank-if-empty]]"';
    $o->query =
      'select
        t.id, t.name "tag|template-blank-if-empty"
      from {tag_instance} ti
      join {tag} t
      on t.id = ti.tagid
      where ti.contextid = :contextid
      and ti.itemid = :contextinstanceid';
    return $o;
  }static private function version_100() {
    global $CFG;

    $o = new stdClass();
    $o->name = "Version 1.0.0";
    $o->shortname = "version-100";
    $o->property = PROPERTY_VERSION;
    $o->datatype = DATATYPE_SINGLE;
    $o->content = '"1.0.0"';
    return $o;
  }
}
