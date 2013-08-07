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
 
class date_status_so {
	
	var $spisession_ref_status = 'spisession_ref_date_status';
	var $spisession_transition_status = 'spisession_ref_date_status_transition';
	
	var $date_so_status;
	var $date_so_transition_status;
	
	/**
	 * Constructeur 
	 *
	 */
	function date_status_so(){
		$this->date_so_status = new so_sql('spisession',$this->spisession_ref_status);
		$this->date_so_transition_status = new so_sql('spisession',$this->spisession_transition_status);
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
		foreach((array)$this->date_so_status->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}
		return $tab_search;
	}

	function add_update_status($info){
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
			$this->date_so_status->data = $info;
			if(isset($this->date_so_status->data['status_id'])){
				// Existant
				$this->date_so_status->data['status_modified']=time();
				$this->date_so_status->data['status_modifier']=$GLOBALS['egw_info']['user']['account_id'];
				$this->date_so_status->update($this->date_so_status->data,true);

				// Mise a jour des transitions
				$infoTransition['status_id'] = $this->date_so_status->data['status_id'];
				$infoTransition['status_childs'] = explode(',',$info['status_childs']);
				$msg = $this->add_update_transition($infoTransition);
				
				$msg .= ' '."Registration' status updated";
			}else{
				// Nouveau
				$this->date_so_status->data['status_id'] = '';
				$this->date_so_status->data['status_created']=time();
				$this->date_so_status->data['status_creator']=$GLOBALS['egw_info']['user']['account_id'];
				$this->date_so_status->save();

				// Mise a jour des transitions
				$infoTransition['status_id'] = $this->date_so_status->data['status_id'];
				$infoTransition['status_childs'] = explode(',',$info['status_childs']);
				$msg = $this->add_update_transition($infoTransition);
				
				$msg .= ' '."Registration' status created";
			}
		}
		return $msg;
	}

	function add_update_transition($info){
	/**
	 * Crée ou met à jour les transition d'un statut
	 *
	 * @param $info : information concernant la transition (statut_id, statut_enfants(array))
	 */
		$msg = '';
		if(is_array($info)){
			// On supprime les transitions existantes avant de reprendre les nouvelles
			$this->date_so_transition_status->delete(array('status_source' => $info['status_id']));
			foreach((array)$info['status_childs'] as $key => $child){
				if(!empty($child)){
					$this->date_so_transition_status->data['status_source'] = $info['status_id'];
					$this->date_so_transition_status->data['status_target'] = $child;
					$this->date_so_transition_status->save();
				}
			}
		}
	}
	
	
}
?>