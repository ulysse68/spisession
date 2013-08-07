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

class course_so extends so_sql{
	
	var $spisession_course = 'spisession_course';
	var $spisession_crs_status = 'spisession_ref_crs_status';
	var $spisession_crs_transition_status = 'spisession_ref_crs_status_transition';
	var $spisession_field = 'spisession_ref_field';
	var $spisession_grad = 'spisession_ref_graduation';
	var $spisession_comp = 'spisession_ref_component';
	var $spisession_crs_comp = 'spisession_crs_component';
	var $spisession_session = 'spisession_session';
	
	var $so_course;
	var $so_crs_status;
	var $so_crs_status_transition;
	var $so_field;
	var $so_grad;
	var $so_comp;
	var $so_crs_comp;
	var $so_ses;
	
	/**
	 * Constructeur 
	 *
	 */
	function course_so(){
		// Tables
		parent::so_sql('spisession',$this->spisession_course);
		$this->so_crs_status = new so_sql('spisession',$this->spisession_crs_status);
		$this->so_crs_status_transition = new so_sql('spisession',$this->spisession_crs_transition_status);
		$this->so_field = new so_sql('spisession',$this->spisession_field);
		$this->so_grad = new so_sql('spisession',$this->spisession_grad);
		$this->so_comp = new so_sql('spisession',$this->spisession_comp);
		$this->so_crs_comp = new so_sql('spisession',$this->spisession_crs_comp);
		$this->so_ses = new so_sql('spisession',$this->spisession_session);

		$this->ses_ui = CreateObject('spisession.spisession_ui');

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
		foreach((array)$this->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}
		return $tab_search;
	}

	function set_readonlys(){
	/**
	 * Genere la liste des informations a mettre en readonly
	 */
		foreach((array)$this->db_data_cols as $key => $value){
			$retour[$key] = true;
		}
		return $retour;
	}

	function add_update_course($info){
	/**
	 * Crée ou met à jour un cours
	 *
	 * @param $info : information concernant le cours
	 */
		$msg='';
		if(is_array($info)){
			
			// Mets à jour les composantes
			$this->add_update_crs_comp($info['component']);

			if(isset($info['crs_id'])){
				// Existant
				$this->history($info);
				$this->data = $info;
				$this->data['crs_modified']=time();
				$this->data['crs_modifier']=$GLOBALS['egw_info']['user']['account_id'];
				$this->update($this->data,true);
				
				$msg = lang('Course updated');
			}else{
				// Nouveau
				$this->data = $info;
				$this->data['crs_id'] = '';
				$this->data['crs_created']=time();
				$this->data['crs_creator']=$GLOBALS['egw_info']['user']['account_id'];
				$this->save();
				
				$msg = lang('Course created');
			}
		}
		return $msg;
	}

	function add_update_crs_comp($components){
	/** 
	 * Mets à jour les composantes de cours
	 */
		foreach((array)$components as $key => $data){
			if(is_numeric($key)){
				$this->so_crs_comp->data = $data;
				$this->so_crs_comp->save();
			}
		}
	}

	function history($content){
	/**
	 * Fonction permettant l'historisation des valeurs (lors de la mise a jour d'une reference)
	 *
	 * @param $content : info concernant la référence (contient les infos avec les nouvelles valeurs)
	 */
		// Valeur actuel du contrat
		$id = $content['crs_id'];
		$old = $this->read($id);

		// Nouvelles valeurs
		$history = array_diff_assoc($content,$old);
		$infoHistory = $history['history'];

		$champsIgnore = array('session','msg','history','component','general|session|component|description|history','mode','hideadd','button','hide_spiclient');
		$champsDate = array('crs_modified');
		$champsExterne = array(
			'crs_status' => array('table' => $this->so_crs_status,'field' => 'status_label'),
			'crs_field' => array('table' => $this->so_field,'field' => 'field_label'),
			'crs_grad' => array('table' => $this->so_grad,'field' => 'grad_label'),
		);
		$champsUser = array('crs_responsible');
		
		$historylog = CreateObject('phpgwapi.historylog','spisession_crs');


		// Historisation des champs 
		foreach((array)$history as $field => $value){
			if(!in_array($field,$champsIgnore)){				
				// test afin de savoir si on est sur une valeur qui etait null (mais qui apparait avec la valeur 0) cas des listes
				if(!($value == null && $old[$field] == '0')){
					if(in_array($field, $champsDate)){
						$historylog->add(lang($field),$id,date('d/m/Y',$value),date('d/m/Y',$old[$field]));
					}else{
						if(array_key_exists($field,$champsExterne)){
							$new_value = $champsExterne[$field]['table']->read($value);
							$old_value = $champsExterne[$field]['table']->read($old[$field]);
							$historylog->add(lang($field),$id,$new_value[$champsExterne[$field]['field']],$old_value[$champsExterne[$field]['field']]);
						}else{
							if(in_array($field,$champsUser)){
								$new_contact = $GLOBALS['egw']->accounts->read($value);
								$old_contact = $GLOBALS['egw']->accounts->read($old[$field]);
								
								$new_name = $new_contact['account_firstname'].' '.$new_contact['account_lastname'];
								$old_name = $old_contact['account_firstname'].' '.$old_contact['account_lastname'];
								$historylog->add(lang($field),$id,$new_name,$old_name);
							}else{
								$historylog->add(lang($field),$id,$value,$old[$field]);
							}
						}
					}
				}
			}
		}
	}
}
?>