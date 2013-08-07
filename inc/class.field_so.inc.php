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

class field_so {
	
	var $spisession_field = 'spisession_ref_field';
	
	var $so_field;
	
	/**
	 * Constructeur 
	 *
	 */
	function field_so(){
		$this->so_field = new so_sql('spisession',$this->spisession_field);
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
		foreach((array)$this->so_field->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}
		return $tab_search;
	}

	function add_update_field($info){
	/**
	 * Crée ou met à jour un theme
	 *
	 * @param $info : information concernant le statut
	 */
		$msg='';
		if(is_array($info)){
			unset($info['button']);
			unset($info['nm']);
			unset($info['msg']);
			$this->so_field->data = $info;
			if(isset($this->so_field->data['field_id'])){
				// Existant
				$this->so_field->data['field_modified']=time();
				$this->so_field->data['field_modifier']=$GLOBALS['egw_info']['user']['account_id'];
				$this->so_field->update($this->so_field->data,true);
				
				$msg .= ' '.'Field updated';
			}else{
				// Nouveau
				$this->so_field->data['field_id'] = '';
				$this->so_field->data['field_created']=time();
				$this->so_field->data['field_creator']=$GLOBALS['egw_info']['user']['account_id'];
				$this->so_field->save();
				
				$msg .= ' '.'Field created';
			}
		}
		return $msg;
	}
}
?>