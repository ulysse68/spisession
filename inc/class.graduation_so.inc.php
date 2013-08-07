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

class graduation_so {
	
	var $spisession_graduation = 'spisession_ref_graduation';
	
	var $so_graduation;
	
	/**
	 * Constructeur 
	 *
	 */
	function graduation_so(){
		$this->so_graduation = new so_sql('spisession',$this->spisession_graduation);
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
		foreach((array)$this->so_graduation->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}
		return $tab_search;
	}

	function add_update_graduation($info){
	/**
	 * Crée ou met à jour un niveau
	 *
	 * @param $info : information concernant le niveau
	 */
		$msg='';
		if(is_array($info)){
			unset($info['button']);
			unset($info['nm']);
			unset($info['msg']);
			$this->so_graduation->data = $info;
			if(isset($this->so_graduation->data['grad_id'])){
				// Existant
				$this->so_graduation->data['grad_modified']=time();
				$this->so_graduation->data['grad_modifier']=$GLOBALS['egw_info']['user']['account_id'];
				$this->so_graduation->update($this->so_graduation->data,true);
				
				$msg .= ' '.'Graduation updated';
			}else{
				// Nouveau
				$this->so_graduation->data['grad_id'] = '';
				$this->so_graduation->data['grad_created']=time();
				$this->so_graduation->data['grad_creator']=$GLOBALS['egw_info']['user']['account_id'];
				$this->so_graduation->save();
				
				$msg .= ' '.'Graduation created';
			}
		}
		return $msg;
	}
}
?>