<?php
/**
*
* @package phpBB Extension - View Friends
* @copyright (c) 2019 Ady
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace ady\viewfriends\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
/**
* Assign functions defined in this class to event listeners in the core
*
* @return array
* @static
* @access public
*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'              => 'load_language_on_setup',
			"core.memberlist_view_profile" => "render_memberlist_view_profile"
		);
	}

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var string table_prefix */
	protected $table_prefix;

	/**
	* Constructor
	*/
	public function __construct(
        \phpbb\db\driver\driver_interface   $db,
		\phpbb\template\template 			$template,
		\phpbb\language\language			$lang,
											$table_prefix
	)
	{
		$this->db           = $db;
		$this->template     = $template;
		$this->lang         = $lang;
		$this->table_prefix = $table_prefix;
	}

	/**
	 * Adds local language to global language
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'ady/viewfriends',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Adds the template variables for memberlist view profile
	 */
	public function render_memberlist_view_profile($event)
	{
		$member       = $event['member'];
		$friends      = [];
		$zebra_result = [];
		$table_zebra  = $this->table_prefix."zebra";
		$table_users  = $this->table_prefix."users";
		$sql_user     = "SELECT username, user_avatar, user_colour FROM $table_users WHERE user_id = ";

		$sql          = "SELECT zebra_id FROM $table_zebra WHERE user_id = {$member['user_id']} AND friend = 1";
		$zebra_result = $this->db->sql_query($sql);

		if ($zebra_result->num_rows > 0) {
			while ($row = $this->db->sql_fetchrow($zebra_result)) {
				$user_result = $this->db->sql_query($sql_user.$row['zebra_id']);
				$user        = $this->db->sql_fetchrow($user_result);

				$friends[$row['zebra_id']] = $user;
			}
			$this->db->sql_freeresult($user_result);
			$this->db->sql_freeresult($zebra_result);
		}

		$template_data['FRIENDS']    = $friends;
		$template_data['L_USERNAME'] = $this->lang->lang('L_USERNAME').$member['username'];
        $this->template->assign_vars($template_data);
	}
}

// Function debug for dev
// function d($data) {
// 	die(print_r($data, 1));
// }
