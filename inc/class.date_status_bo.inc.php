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

 /**
 * 
 */
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.date_status_so.inc.php');	

class date_status_bo extends date_status_so{
	
	/**
	 * Constructeur 
	 *
	 */
	function date_status_bo(){
		parent::date_status_so();
	}
	
	function get_info($id){
	/**
	 * Retourne les informations d'un statut
	 *
	 * @param $id : identifiant du statut
	 * @return array
	 */
		$info = $this->date_so_status->read($id);

		// Récupération des transitions
		$transition = $this->date_so_transition_status->search(array('status_source' => $id),false);
		foreach((array)$transition as $keyTransition => $dataTransition){
			$info['status_childs'][$keyTransition] = $dataTransition['status_target'];
		}

		return $info;
	}
	
	function get_rows($query,&$rows,&$readonlys){
	/**
	 * Récupère et filtre les statuts
	 *
	 * @param array $query avec des clefs comme 'start', 'search', 'order', 'sort', 'col_filter'. Pour définir d'autres clefs comme 'filter', 'cat_id', vous devez créer une classe fille
	 * @param array &$rows lignes complétés
	 * @param array &$readonlys pour mettre les lignes en read only en fonction des ACL, non utilisé ici (à utiliser dans une classe fille)
	 * @return int
	 */
		if(!is_array($query['col_filter']) && empty($query['col_filter'])){
			$query['col_filter']=array();
		}
		
		$order=$query['order'].' '.$query['sort'];
		$id_only=false;
		$start=array(
			(int)$query['start'],
			(int) $query['num_rows']
		);
		$wildcard = '%';
		$op = 'OR';
		
		// Filtre sur les actifs/inactifs
		if(!empty($query['filter']) or ($query['filter']==0)){
			$query['col_filter']['status_active'] = $query['filter'];
		}
		
		// Recherche champ texte
		if(!is_array($query['search'])){
			$search = $this->construct_search($query['search']);
		}else{
			$search=$query['search'];
		}

		$rows = $this->date_so_status->search($search,false,$order,'',$wildcard,false,$op,$start,$query['col_filter']);
		if(!$rows){
			$rows = array();
		}
		foreach((array)$rows as $id=>$value){
			$transition = $this->date_so_transition_status->search(array('status_source' => $value['status_id']),false);
			foreach((array)$transition as $keyTransition => $dataTransition){
				$rows[$id]['status_childs'][$keyTransition] = $dataTransition['status_target'];
			}
		}
		$order = $query['order'];
		
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Status Management');
		if($query['search']){
			$GLOBALS['egw_info']['flags']['app_header'] .= ' - '.lang("Search for '%1'",$query['search']);
		}

		return $this->date_so_status->total;	
    }

    function get_possible_child($id){
	/**
	 * Retourne la liste des enfants possible pour un statut
	 *
	 * @param $id : identifiant du statut
	 * @return array
	 */
		$retour = array();
		$info = $this->date_so_status->search(array('status_active' => true),false,'status_label');
		$i = 0;
		foreach((array)$info as $key => $data){
			if($data['status_id'] != $id){
				$retour[$data['status_id']] = $data['status_label'];
			}
		}

		return $retour;
	}
}
?>