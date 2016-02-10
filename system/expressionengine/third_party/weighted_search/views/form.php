<?php
echo form_open('C=addons_extensions'.AMP.'M=save_extension_settings', '', $hidden);

foreach ($fields as $name => $details)
{

  $group = 'others';
  if(strpos($name, 'field_id_') !== FALSE || $name == 'channel_title')
    $group = 'fields';
  if(strpos($name, 'channel_id_') !== FALSE)
    $group = 'channels';

  $pref = '';
	switch ($details['type'])
	{
		case 's':
			$label = lang($name);
			$pref = form_dropdown($name, $details['value'], $details['selected'], 'id="'.$name.'"');
			break;
		case 'ms':
			$label = lang($name);
			$pref = form_multiselect($name.'[]', $details['value'], $details['selected'], 'id="'.$name.'" size="8"');
			break;
		case 'r':
			$label = lang($name);
			foreach ($details['value'] as $options)
			{
				$pref .= form_radio($options).NBS.lang($options['label'], $options['id']).NBS.NBS.NBS.NBS;
			}
			break;
		case 'c':
			$label = lang($name);
			foreach ($details['value'] as $options)
			{
				$pref .= form_checkbox($options).NBS.lang($options['label'], $options['id']).NBS.NBS.NBS.NBS;
			}
			break;
		case 't':
			$label = lang($name, $name);
			$pref = form_textarea($details['value']);
			break;
		case 'f':
			$label = lang($name, $name);
			break;
		case 'i':
			$label = lang($name, $name);
			$pref = form_input(array_merge($details['value'], array('id' => $name, 'class' => 'input', 'size' => 20, 'maxlength' => 120, 'style' => 'width:100%')));
			break;
	}

  $lang = array(
    'Expression' => lang('Expression'),
    'Value' => lang('Value'),
    'Weight' => lang('Weight'),
  );

  if(!function_exists('generate_specials')) {
    function generate_specials($specials, $name) {
      $rows = '';
      if(empty($specials) || count($specials) == 0)
        return $rows;
      foreach($specials as $key => $special) {
        $o = array();
        $o[] = '<input type="text" name="special['.$name.']['.$key.'][expression]" value="' . $special['expression'] . '" />';
        $o[] = '<input type="text" name="special['.$name.']['.$key.'][weight]" value="' . $special['weight'] . '" />';
        $o[] = '<img class="remove" src="/themes/cp_themes/default/images/remove_layout.png" onClick="jQuery(this).closest(\'tr\').remove()" />';
        $rows[] = '<td>' . implode('</td><td>', $o) . '</td>';
      }
      $rows = '<tr>' . implode('</tr><tr>', $rows) . '</tr>';
      return $rows;
    }
  }

  $special_rows = '';
  if(isset($specials) && is_array($specials) && array_key_exists($name, $specials))
    $special_rows = generate_specials($specials[$name], $name);

  $table_tmpl =<<<TABLE_TMPL
  <table class="add-field-value-table" width="100%" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <th>{$lang['Expression']}</th>
        <th>{$lang['Weight']}</th>
        <th width="5%">Remove</th>
      </tr>
    </thead>
    <tbody>
      $special_rows
    </tbody>
  </table>
TABLE_TMPL;

  $button = '<a class="submit add-field-value-weight" data-for="' . $name . '">' . lang('add_field_value_weight') . '</a>';

  if(strpos($name, 'field_id_') !== FALSE || $name == 'channel_title') {
    $pref .= '<br/><br/>';
    $pref .= $table_tmpl;
    // $pref .= '<br/>';
    $pref .= $button;
  }

  $types[$group][] = array(
    'first' => "<strong>{$label}</strong>".(($details['subtext'] != '') ? "<div class='subtext'>{$details['subtext']}</div>" : ''),
    'second' => $pref
  );

	// $this->table->add_row(
	// 					"<strong>{$label}</strong>".(($details['subtext'] != '') ? "<div class='subtext'>{$details['subtext']}</div>" : ''),
	// 					$pref
	// 					);
}

foreach(array_reverse($types) as $group => $rows) {
  $list[] = '<li id="" title="General" class="content_tab current"><a href="#'.$group.'" title="menu_general" class="menu_general ui-droppable">'. ucfirst($group) .'</a></li>';
  $this->table->set_template($cp_pad_table_template);
  $this->table->set_heading(
    array('data' => lang('preference'), 'style' => 'width:50%;'),
    lang('setting')
  );
  foreach($rows as $row) {
    $this->table->add_row($row['first'], $row['second']);
  }
  $container[] = '<div id="'. $group .'" class="main_tab ui-droppable ui-sortable">'. $this->table->generate() .'</div>';
}
?>

<div id="tabs">
  <ul class="tab_menu" id="tab_menu_tabs"><?php echo implode('', $list); ?></ul>
  <div id="holder"><?php echo implode('', $container); ?></div>
</div>

<?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))?>
<?=form_close()?>

<div class="clear_right"></div>
