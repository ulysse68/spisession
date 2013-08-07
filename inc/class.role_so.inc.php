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
 
class role_so {
	
	var $spisession_role = 'spisession_ref_role';
	
	var $so_role;
	
	/**
	 * Constructeur 
	 *
	 */
	function role_so(){
		$this->so_role = new so_sql('spisession',$this->spisession_role);
	}
	
	function construct_search($search){
	/**
	 * Crée une recherche. Le tableau de retour contiendra toutes les colonnes de la table en cours, en leur faisant correspondre la valeur $search 
	 *
	 * La requête ainsi crée est prête à être utilisée comme filtre
	 *
	 * @param int $search tableau des critères de recherche
	 * @return array
	 */
		$tab_search=array();
		foreach((array)$this->so_role->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}
		return $tab_search;
	}

	function add_update_role($info){
	/**
	 * Crée ou met à jour un role
	 *
	 * @param $info : information concernant le role
	 */
		$msg='';
		if(is_array($info)){
			unset($info['button']);
			unset($info['nm']);
			unset($info['msg']);
			$this->so_role->data = $info;
			if(isset($this->so_role->data['role_id'])){
				// Existant
				$this->so_role->data['role_modified']=time();
				$this->so_role->data['role_modifier']=$GLOBALS['egw_info']['user']['account_id'];
				$this->so_role->update($this->so_role->data,true);
				
				$msg .= ' '.'Role updated';
			}else{
				// Nouveau
				$this->so_role->data['role_id'] = '';
				$this->so_role->data['role_created']=time();
				$this->so_role->data['role_creator']=$GLOBALS['egw_info']['user']['account_id'];
				$this->so_role->save();
				
				$msg .= ' '.'Role created';
			}
		}
		return $msg;
	}
}
?>