# XapiEvent Templates

Query
* must be formatted for use with $DB->get_record_sql or $DB->get_records_sql
* placeholders must be wrapped in [[...]]
  * e.g. query is: select 'some text' "value"
       content is: "key" : "[[value]]"
* placeholder values may be further parsed using markup. Options are
  * |date -- parse a unix timestamp as to a supported date
    [https://github.com/adlnet/xAPI-Spec/blob/master/xAPI-Data.md#45-iso-8601-timestamps]
  * |duration -- parse an integer as supported duration
    [https://github.com/adlnet/xAPI-Spec/blob/master/xAPI-Data.md#46-iso-8601-durations]
  * |template-blank-if-empty -- if this value is empty do not parse the template, return an empty string
* may use other templates as placeholders, where the placeholder name is the
  template shortname
  * e.g. template shortname is: "extension-tag"
                    content is: "context": { [[extension-tag]] }
