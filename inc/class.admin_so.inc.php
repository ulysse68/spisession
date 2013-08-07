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

require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.acl_so.inc.php');

class admin_so {
	
	var $spisession_crs_status = 'spisession_ref_crs_status';
	var $spisession_ses_status = 'spisession_ref_ses_status';
	var $spisession_date_status = 'spisession_ref_date_status';
	var $spisession_reg_status = 'spisession_ref_reg_status';
	var $spisession_role = 'spisession_ref_role';

	var $so_crs_status;
	var $so_ses_status;
	var $so_date_status;
	var $so_reg_status;
	var $so_role;

	var $config;
	
	function admin_so(){
	/**
	 * Constructeur 
	 *
	 */
		
		// /* Récupération les infos de configurations */
		$config = CreateObject('phpgwapi.config');
		$this->config = $config->read('spisession');
			
		// Tables
		$this->so_crs_status = new so_sql('spisession',$this->spisession_crs_status);
		$this->so_ses_status = new so_sql('spisession',$this->spisession_ses_status);
		$this->so_date_status = new so_sql('spisession',$this->spisession_date_status);
		$this->so_reg_status = new so_sql('spisession',$this->spisession_reg_status);
		$this->so_role = new so_sql('spisession',$this->spisession_role);
	}

}
?>