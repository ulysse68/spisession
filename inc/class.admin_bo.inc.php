<?php
/**
 * spisession - Integrated Sessions Management Modules for eGroupware (trainings, meetings, etc.)
 * See About folder and www.spirea.fr for further information
 *
 * @link http://www.spirea.fr
 * @package spisession
 * @author Spirea SARL <contact@spirea.fr>
 * @copyright (c) 2012-december by Spirea +33141192772
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */


require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.admin_so.inc.php');	

class admin_bo extends admin_so{
	
		
	function admin_bo(){
	/**
	 * Constructeur 
	 *
	 */
		parent::admin_so();
	}
	
	function add_update_config($info){
	/**
	 * Routine permettant de créer/modifier la config
	 *
	 * @param array $content=null
	 * @return string
	 */
		$obj = CreateObject('phpgwapi.config');
		foreach((array)$info as $id => $value){
			$obj->save_value($id,$value,'spisession');
		}
		$this->config = $obj->read('spisession');
		return lang('Configuration updated');
	}
	
	function get_crs_status(){
	/**
	 * Liste des statuts de cours
	 *
	 * @return array
	 */
		$return = array();
		
		$info = $this->so_crs_status->search('',false,'status_order');
		foreach((array)$info as $key => $data){
			$return[$data['status_id']] = $data['status_label'];
		}
		return $return;
	}

	function get_ses_status(){
	/**
	 * Liste des statuts de session
	 *
	 * @return array
	 */
		$return = array();
		
		$info = $this->so_ses_status->search('',false,'status_order');
		foreach((array)$info as $key => $data){
			$return[$data['status_id']] = $data['status_label'];
		}
		return $return;
	}

	function get_date_status(){
	/**
	 * Liste des statuts de dates de session
	 *
	 * @return array
	 */
		$return = array();
		
		$info = $this->so_date_status->search('',false,'status_order');
		foreach((array)$info as $key => $data){
			$return[$data['status_id']] = $data['status_label'];
		}
		return $return;
	}

	function get_reg_status(){
	/**
	 * Liste des statuts d'inscription
	 *
	 * @return array
	 */
		$return = array();
		
		$info = $this->so_reg_status->search('',false,'status_order');
		foreach((array)$info as $key => $data){
			$return[$data['status_id']] = $data['status_label'];
		}
		return $return;
	}

	function get_role(){
	/**
	 * Liste des roles
	 *
	 * @return array
	 */
		$return = array();
		
		$info = $this->so_role->search('',false,'role_order');
		foreach((array)$info as $key => $data){
			$return[$data['role_id']] = $data['role_label'];
		}
		return $return;
	}
}
?>