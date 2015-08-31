<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Weighted Search Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Andreas Bilz
 * @link		
 */

class Weighted_search_ext {

  /**
   * @var array
   */
  public $settings 		= array();
  /**
   * @var string
   */
  public $description		= 'Custom weight search query';
  /**
   * @var string
   */
  public $docs_url		= '';
  /**
   * @var string
   */
  public $name			= 'Weighted Search';
  /**
   * @var string
   */
  public $settings_exist	= 'y';
  /**
   * @var string
   */
  public $version			= '1.0';

  /**
   * @var CI_Controller
   */
  private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();

    $this->EE->load->library('logger');

    $settings_query = $this->EE->db->select('settings')->where('class', __CLASS__)->limit(1)->get('extensions');
    $settings_object = $settings_query->row();
    $settings_array = unserialize($settings_object->settings);

    if(empty($settings))
      $settings = array();

		$this->settings = array_merge($settings, $settings_array);
	}// ----------------------------------------------------------------------

  /**
   * @return array
   */
  public function settings()
  {

    return $this->settings;
//    return array(
//      'weighted_search_title_weight' => array('i', '', '100')
//    );
  }

	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
    include_once "install/weighted_search.inc.php";
    extract($weighted_search_settings);

    $status_options = array(
      'enabled' => lang('status_enabled'),
      'disbabled' => lang('status_disabled'),
    );

    $settings['status'] = array('r', $status_options, $ext_status);
    $settings['channel_title'] = array('i', '', $title_weight);
    foreach($fields as $field_id => $factor) {
      $settings['field_id_' . $field_id] = array('i', '', $factor);
    }
    foreach($channels as $channel_id => $factor) {
      $settings['channel_id_' . $channel_id] = array('i', '', $factor);
    }

		$this->settings = $settings;

		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'weight_search_query',
			'hook'		=> 'channel_search_modify_search_query',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);			
		
	}	

	// ----------------------------------------------------------------------

  /**
   * Customize the search query for weighted results
   * Base query is borrowed from mod.search.php
   *
   * @param $sql
   * @param $hash
   * @return string
   * @internal param $
   */
	public function weight_search_query($sql, $hash)
	{
    $search_term = $this->get_search_term($hash);
    /**
     * Add custom query attributes
     * * Calculated weight column & order by `weight`
     * * Join the transcribe language table
     * * Condition to meet language conditions
     */
    $sql = $this->add_weight_column($sql, $search_term);
    $sql = $this->add_lang_join($sql);
    $sql = $this->add_lang_where($sql);

    // Get all resources which match the search query
    $query = $this->EE->db->query($sql);

    // Generate search query
    // Will be stored in `exp_search` for later use
    $sql = "SELECT DISTINCT(t.entry_id), t.entry_id, t.channel_id, t.forum_topic_id, t.author_id, t.ip_address, t.title, t.url_title, t.status, t.view_count_one, t.view_count_two, t.view_count_three, t.view_count_four, t.allow_comments, t.comment_expiration_date, t.sticky, t.entry_date, t.year, t.month, t.day, t.entry_date, t.edit_date, t.expiration_date, t.recent_comment_date, t.comment_total, t.site_id as entry_site_id,
				w.channel_title, w.channel_name, w.search_results_url, w.search_excerpt, w.channel_url, w.comment_url, w.comment_moderate, w.channel_html_formatting, w.channel_allow_img_urls, w.channel_auto_link_urls, w.comment_system_enabled,
				m.username, m.email, m.url, m.screen_name, m.location, m.occupation, m.interests, m.aol_im, m.yahoo_im, m.msn_im, m.icq, m.signature, m.sig_img_filename, m.sig_img_width, m.sig_img_height, m.avatar_filename, m.avatar_width, m.avatar_height, m.photo_filename, m.photo_width, m.photo_height, m.group_id, m.member_id, m.bday_d, m.bday_m, m.bday_y, m.bio,
				md.*,
				wd.*
			FROM exp_channel_titles		AS t
			LEFT JOIN exp_channels 		AS w  ON t.channel_id = w.channel_id
			LEFT JOIN exp_channel_data	AS wd ON t.entry_id = wd.entry_id
			LEFT JOIN exp_members		AS m  ON m.member_id = t.author_id
			LEFT JOIN exp_member_data	AS md ON md.member_id = m.member_id
			WHERE t.entry_id IN (";

    // Add all IDs which meet the query
    // Mind the above IN statement
    foreach ($query->result_array() as $row)
    {
      $sql .= $row['entry_id'].',';
      $order_ids[] = $row['entry_id'];
    }
    // Sort the result by the given IDs
    // Needs to be done because EE/Transcribe trashes it else
    $order_ids_string = implode(',', $order_ids);
    $end = " ORDER BY FIELD(t.entry_id, {$order_ids_string})";

    // Query concat
    $sql = substr($sql, 0, -1).') '.$end;

    $this->EE->logger->developer('Logged in: ' . $this->EE->session->userdata('member_id'));

    // Exit any further extension and
    // save this query to `exp_search`
    $this->EE->extensions->end_script = TRUE;

    return $sql;
	}

	// ----------------------------------------------------------------------

  /**
   * Get the current search term
   * As the search query is not yet stored rely on post argument
   *
   * @param $hash
   * @return mixed
   */
  public function get_search_term($hash)
  {
    /**
     * Search query not yet saved in exp_search
     * So we need to get the post parameter storing the search query
     */
//    $search_result = $this->EE->db->get_where('search', array('search_id' => $hash), 1);
//    $result = $search_result->row_array();
//    $search_result = $search_result->row();
//    $this->EE->logger->developer($this->EE->db->last_query());
//    $this->EE->logger->developer('GET SEARCH TERM: ' . json_encode($result));
//    return $result;
    return $this->EE->input->post('keywords');
//    return $_POST['keywords'];
  }

  /**
   * Adds language join to search query
   * @param $sql
   * @return mixed
   */
  public function add_lang_join($sql)
  {
    // Inject the additional relationship query
    $sql = str_replace('FROM exp_channel_titles', 'FROM exp_channel_titles LEFT JOIN exp_transcribe_entries_languages tel ON tel.entry_id = exp_channel_titles.entry_id', $sql);
    return $sql;
  }


  /**
   * Adds language condition to search query
   * Due to wrong sorting through `transcribe` this must be added already in
   * stored search query
   *
   * @param $sql
   * @return mixed
   */
  public function add_lang_where($sql){

    // Get the current language session variables
    $lang = $_SESSION['transcribe'];
    // Inject the additional condition
    $sql = str_replace('WHERE', "WHERE tel.language_id = {$lang['id']} AND", $sql);
    return $sql;
  }

  /**
   * @param $sql
   * @param $term
   * @return mixed|string
   */
  public function add_weight_column($sql, $term)
  {
    /**
     * @todo  Get extension settings from DB
     */
    $fields = array();
    $channels = array();

    $title_weight = $this->settings['channel_title'];
    /**
     * Iterate settings and generate 2 arrays
     * * Field weights
     * * Channel weights
     * Identify by prefixes
     * * 'field_id'
     * * 'channel_id'
     */
    foreach($this->settings as $key => $value) {
      if(strpos($key, 'field_id_') !== FALSE)
        $fields[str_replace('field_id_', '', $key)] = $value;
      if(strpos($key, 'channel_id_') !== FALSE)
        $channels[str_replace('channel_id_', '', $key)] = $value;
    }

    /**
     * Create the array which holds all weighted columns
     * and creates a calculated column which will be used to
     * sort the result (IDs) correct
     */
    $weight_column = array();
    foreach($fields as $field_id => $factor) {
      $weight_column[] = "IF(exp_channel_data.field_id_{$field_id} LIKE '%{$term}%', $factor, 0)";
    }
    foreach($channels as $channel_id => $factor) {
      $weight_column[] = "IF(exp_channel_titles.channel_id = $channel_id, $factor, 0)";
    }

    $weight_column[]= "IF(exp_channel_titles.title LIKE '%$term%', $title_weight, 0)";
    // Stringify and prepend addition-sign (+)
    $weight_str = '(' . implode(' + ', $weight_column) . ') AS weight';

    // Do some replacements to make the query valid
    $sql = str_replace('DISTINCT(exp_channel_titles.entry_id)', '(exp_channel_titles.entry_id)', $sql);
    $sql = str_replace('SELECT', 'SELECT ' . $weight_str . ', ', $sql);
    // Order by `weight` to get correct search resulsts
    $sql .= ' ORDER BY weight DESC';

//    $this->EE->logger->developer('WEIGHT COLUMN SQL READY: ' .$sql);
    return $sql;
  }

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}
	
	// ----------------------------------------------------------------------

  /**
   * Well, save the settings
   * and set flash message
   */
  function save_settings()
  {
    if (empty($_POST))
    {
      show_error(lang('unauthorized_access'));
    }

    unset($_POST['submit']);

    ee()->lang->loadfile('weighted_search');

    ee()->db->where('class', __CLASS__);
    ee()->db->update('extensions', array('settings' => serialize($_POST)));

    ee()->session->set_flashdata(
      'message_success',
      lang('preferences_updated')
    );
  }
}



/* End of file ext.weighted_search.php */
/* Location: /system/expressionengine/third_party/weighted_search/ext.weighted_search.php */