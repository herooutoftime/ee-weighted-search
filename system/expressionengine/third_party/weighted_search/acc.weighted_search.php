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
 * Channel Language Column Accessory
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Accessory
 * @author		Andreas Bilz
 * @link
 */
class Weighted_search_acc {

	public $name			= 'Weighted Search';
	public $id				= 'weigthed_search';
	public $version			= '1.0';
	public $description		= 'Adds weighted search';
	public $sections		= array();

	/**
	 * Set Sections
	 */
	public function set_sections()
	{
		$EE =& get_instance();


		//$this->sections['Channel Language Column'] = $EE->load->view('accessory_channel_language_column', '', TRUE);
    $EE->cp->add_js_script(array('ui' => array('tabs')));
    $EE->cp->load_package_js('weighted_search');

	}

	// ----------------------------------------------------------------

}

/* End of file acc.channel_language_column.php */
/* Location: /system/expressionengine/third_party/channel_language_column/acc.channel_language_column.php */
