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

class reg_status_so {
	
	var $spisession_ref_status = 'spisession_ref_reg_status';
	var $spisession_transition_status = 'spisession_ref_reg_status_transition';
	
	var $reg_so_status;
	var $reg_so_transition_status;
	
	/**
	 * Constructeur 
	 *
	 */
	function reg_status_so(){
		$this->reg_so_status = new so_sql('spisession',$this->spisession_ref_status);
		$this->reg_so_transition_status = new so_sql('spisession',$this->spisession_transition_status);
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
		foreach((array)$this->reg_so_status->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}
		return $tab_search;
	}

	function add_upgrade_reg_status($info){
	/**
	 * Crée ou met à jour un statut
	 *
	 * @param $info : information concernant le statut
	 */
		$msg='';
		if(is_array($info)){
			unset($info['button']);
			unset($info['nm']);
			unset($info['msg']);
			$this->reg_so_status->data = $info;
			if(isset($this->reg_so_status->data['status_id'])){
				// Existant
				$this->reg_so_status->data['status_modified']=time();
				$this->reg_so_status->data['status_modifier']=$GLOBALS['egw_info']['user']['account_id'];
				$this->reg_so_status->update($this->reg_so_status->data,true);

				// Mise a jour des transitions
				$infoTransition['status_id'] = $this->reg_so_status->data['status_id'];
				$infoTransition['status_childs'] = explode(',',$info['status_childs']);
				$msg = $this->add_upgrade_reg_transition($infoTransition);
				
				$msg .= ' '.'Status of the date updated';
			}else{
				// Nouveau
				$this->reg_so_status->data['status_id'] = '';
				$this->reg_so_status->data['status_created']=time();
				$this->reg_so_status->data['status_creator']=$GLOBALS['egw_info']['user']['account_id'];
				$this->reg_so_status->save();

				// Mise a jour des transitions
				$infoTransition['status_id'] = $this->reg_so_status->data['status_id'];
				$infoTransition['status_childs'] = explode(',',$info['status_childs']);
				$msg = $this->add_upgrade_reg_transition($infoTransition);
				
				$msg .= ' '.'Status of the date created';
			}
		}
		return $msg;
	}

	function add_upgrade_reg_transition($info){
	/**
	 * Crée ou met à jour les transition d'un statut
	 *
	 * @param $info : information concernant la transition (statut_id, statut_enfants(array))
	 */
		$msg = '';
		if(is_array($info)){
			$this->reg_so_transition_status->delete(array('status_source' => $info['status_id']));
			foreach((array)$info['status_childs'] as $key => $child){
				if(!empty($child)){
					$this->reg_so_transition_status->data['status_source'] = $info['status_id'];
					$this->reg_so_transition_status->data['status_target'] = $child;
					$this->reg_so_transition_status->save();
				}
			}
		}
	}
	
	
}
?>