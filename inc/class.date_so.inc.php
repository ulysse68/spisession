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

class date_so {
	
	var $spisession_date = 'spisession_session_date';
	var $spisession_session = 'spisession_session';
	var $spisession_date_status = 'spisession_ref_date_status';
	var $spisession_date_status_transition = 'spisession_ref_date_status_transition';
	var $spisession_course = 'spisession_course';
	var $spireapi_site = 'spireapi_site';
	
	var $so_date;
	var $so_ses;
	var $so_date_status;
	var $so_date_status_transition;
	var $so_crs;
	var $so_sites;
	
	/**
	 * Constructeur 
	 *
	 */
	function date_so(){
		// Tables
		$this->so_date = new so_sql('spisession',$this->spisession_date);
		$this->so_ses = new so_sql('spisession',$this->spisession_session);
		$this->so_date_status = new so_sql('spisession',$this->spisession_date_status);
		$this->so_date_status_transition = new so_sql('spisession',$this->spisession_date_status_transition);
		$this->so_crs = new so_sql('spisession',$this->spisession_course);
		$this->so_sites = new so_sql('spireapi',$this->spireapi_site);

		$config = CreateObject('phpgwapi.config');
		$this->obj_config = $config->read('spisession');
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
		foreach((array)$this->so_date->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}
		return $tab_search;
	}

	function add_update_date($info){
	/**
	 * Crée ou met à jour un statut
	 *
	 * @param $info : information concernant le statut
	 */
		$msg='';
		if(is_array($info)){
			// Controle sur les dates
			$ses = $this->so_ses->read($info['ses_date_ses']);
			if($ses['ses_start_date'] > $info['ses_date_day'] || $ses['ses_end_date'] < $info['ses_date_day']){
				return lang('Error while saving').' : '.lang('Date must be beetween session start (%1) and end (%2) date',date('d/m/Y',$ses['ses_start_date']),date('d/m/Y',$ses['ses_end_date']));

			}

			// Date debut > date fin ?
			if($info['ses_date_start'] > $info['ses_date_end']){
				return lang('Error while saving').' : '.lang('End time must be after the start time');
			}

			$this->so_date->data = $info;
			if(isset($this->so_date->data['ses_date_id'])){
				// Existant
				$this->so_date->data['ses_date_modified']=time();
				$this->so_date->data['ses_date_modifier']=$GLOBALS['egw_info']['user']['account_id'];
				$this->so_date->update($this->so_date->data,true);
				
				$msg .= ' '.'Date updated';
			}else{
				// Nouveau
				$this->so_date->data['ses_date_id'] = '';
				$this->so_date->data['ses_date_created']=time();
				$this->so_date->data['ses_date_creator']=$GLOBALS['egw_info']['user']['account_id'];
				$this->so_date->save();
				
				$msg .= ' '.'Date created';
			}
		}
		return $msg;
	}
}
?>