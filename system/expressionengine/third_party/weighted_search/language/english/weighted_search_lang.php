<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//$EE =& get_instance();

$query_channels = ee()->db->get('channels');
foreach($query_channels->result() as $channel) {
  $lang['channel_id_' . $channel->channel_id] = "Channel '{$channel->channel_title}'";
}

$query_fields = ee()->db->get('channel_fields');
foreach($query_fields->result() as $field) {
  $lang['field_id_' . $field->field_id] = "Field '{$field->field_label}'";
}
$lang['channel_title'] = 'Field Title';
$lang['weighted_search_module_name'] = 'Weighted search';
$lang['preferences_updated'] = 'Weighted search preferences updated';
$lang['status_enabled'] = 'Enabled';
$lang['status_disabled'] = 'Disabled';