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
 
class acl_spisession {

	function __construct(){
	/**
	 * Méthode appelée directement par le Constructeur . Charge les ACL de l'application
	 */		
		
		$config = CreateObject('phpgwapi.config');
		$this->obj_config = $config->read('spisession');
				
		$managers = array();
				// Récupération des groupes de l'utilisateur
		$groupeUser = array_keys($GLOBALS['egw']->accounts->memberships($GLOBALS['egw_info']['user']['account_id']));

		if($GLOBALS['egw_info']['user']['apps']['admin']){
			// Admin
			$GLOBALS['egw_info']['user']['SpiSessionLevel'] = 99;
		}elseif(in_array($this->obj_config['ManagementGroup'],$groupeUser)){
			// Groupe de gestion ganager
			$GLOBALS['egw_info']['user']['SpiSessionLevel'] = 59;
		}elseif(in_array($GLOBALS['egw_info']['user']['account_id'],$managers)){
			// Manager
			$GLOBALS['egw_info']['user']['SpiSessionLevel'] = 19;
		}else{
			// Utilisateur
			$GLOBALS['egw_info']['user']['SpiSessionLevel'] = 1;
		}
		
		return $GLOBALS['egw_info']['user']['SpiSessionLevel'];
	}
	
	function acl_spisession(){
	/**
	 * Constructeur 
	 */
		self::__construct();
	}
}

?>