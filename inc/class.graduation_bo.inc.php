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
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.graduation_so.inc.php');	

class graduation_bo extends graduation_so{
	
	/**
	 * Constructeur 
	 *
	 */
	function graduation_bo(){
		parent::graduation_so();
	}
	
	function get_info($id){
	/**
	 * Retourne les informations d'un niveau
	 *
	 * @param $id : identifiant du niveau
	 * @return array
	 */
		return $this->so_graduation->read($id);
	}
	
	function get_rows($query,&$rows,&$readonlys){
	/**
	 * Récupère et filtre les niveaux
	 *
	 * @param array $query avec des clefs comme 'start', 'search', 'order', 'sort', 'col_filter'. Pour définir d'autres clefs comme 'filter', 'cat_id', vous devez créer une classe fille
	 * @param array &$rows lignes grad_létés
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
			$query['col_filter']['grad_active'] = $query['filter'];
		}
		
		// Recherche champ texte
		if(!is_array($query['search'])){
			$search = $this->construct_search($query['search']);
		}else{
			$search=$query['search'];
		}

		$rows = $this->so_graduation->search($search,false,$order,'',$wildcard,false,$op,$start,$query['col_filter']);
		if(!$rows){
			$rows = array();
		}
		foreach((array)$rows as $id=>$value){
			if(isset($query['view'])){
				$readonlys['edit['.$value['grad__id'].']']=true;
				$readonlys['delete['.$value['grad__id'].']']=true;
				$readonlys['add['.$value['grad__id'].']']=true;
			}
		}
		$order = $query['order'];
		
		return $this->so_graduation->total;	
    }
}
?>