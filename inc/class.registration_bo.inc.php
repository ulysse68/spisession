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
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.registration_so.inc.php');



class registration_bo extends registration_so{
	
	/**
	 * Constructeur 
	 *
	 */
	function registration_bo(){
		parent::registration_so();
	}
	
	function get_rows_registrations($query,&$rows,&$readonlys){
	/**
	 * Récupère et filtre les inscriptions
	 *
	 * @param array $query avec des clefs comme 'start', 'search', 'order', 'sort', 'col_filter'. Pour définir d'autres clefs comme 'filter', 'cat_id', vous devez créer une classe fille
	 * @param array &$rows lignes complétés
	 * @param array &$readonlys pour mettre les lignes en read only en fonction des ACL, non utilisé ici (à utiliser dans une classe fille)
	 * @return int
	 */
		if(!is_array($query['col_filter']) && empty($query['col_filter'])){
			$query['col_filter']=array();
		}
		
		//Construction de la requête - pas de split sur les période mais il faut prendre la date de fin de journée...
		$tab_date[] = array(
				'start' => $query['start_date'],
				'end' => $query['end_date']  + 86400,
			);
		
		
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
			$query['col_filter']['reg_status'] = $query['filter'];
		}
		
		if(!empty($query['reg_contact'])){
			$query['col_filter']['reg_contact'] = $query['reg_contact'];
		}
		
		if(!empty($query['reg_role'])){
			$query['col_filter']['reg_role'] = $query['reg_role'];
		}

		// Simple utilisateur = uniquement ses propres données
		if ($GLOBALS['egw_info']['user']['SpiSessionLevel'] == 1){
			$query['col_filter']['reg_contact'] = $GLOBALS['egw_info']['user']['person_id'];
		}
		
		// Recherche champ texte
		if(!is_array($query['search'])){
			$search = $this->construct_search($query['search']);
		}else{
			$search=$query['search'];
		}
		
		
		$join = 'LEFT JOIN spisession_session ON (spisession_registration.reg_ses = spisession_session.ses_id) ';
		$join .= 'LEFT JOIN spisession_course ON (spisession_session.ses_crs = spisession_course.crs_id) ';
		$cond = '';
		$where = ' WHERE ';
		
		// Filtre de date
		if(!empty($query['start_date'])){
				$join .= $where;
				$where = '';
				$cond = ' AND ';
			if(!empty($query['end_date'])){
				// Les deux dates sont remplis
				$join .= ' (spisession_session.ses_start_date BETWEEN '.$query['start_date'].' AND '.$query['end_date'].' OR spisession_session.ses_end_date BETWEEN '.$query['start_date'].' AND '.$query['end_date'].')';
			}else{
				// Uniquement une date de début
				$join .= ' (spisession_session.ses_start_date > '.$query['start_date'].' AND spisession_session.ses_end_date > '.$query['start_date'].')';
			}
		}elseif(!empty($query['end_date'])){
			// Uniquement une date de fin
			$join .= $where;
			$where = '';
			$cond = ' AND ';
			$join .= ' (spisession_session.ses_start_date < '.$query['end_date'].' AND spisession_session.ses_end_date < '.$query['end_date'].')';
		}

		// Filtre cours
		if (!empty($query['ses_crs'])) {
			$join .= $where.$cond.' (spisession_session.ses_crs ='.$query['ses_crs'].')';
		};
		
		$order = 'GROUP BY '.$query['filter2'].' ORDER BY '.$order;
		
		$extra_cols = 'ses_crs,crs_title,sum(crs_nb_hours) as total_crs_nb_hours';
		
		$rows = $this->reg_so->search($search,false,$order,$extra_cols,$wildcard,false,$op,$start,$query['col_filter'],$join);
		if(!$rows){
			$rows = array();
		}
		foreach((array)$rows as $id=>$value){
			// pas de filtre sur role ni sur id
			if ($query['reg_role']<1 and $query['filter2']<>'reg_role' and $query['filter2']<>'reg_id'){
				unset($rows[$id]['reg_role']);
				unset($rows[$id]['reg_status']);
			}
			// Filtre contact
			if ($query['filter2']=='reg_contact'){
				$rows[$id]['reg_id']=$rows[$id]['reg_contact'];
				unset($rows[$id]['reg_created']);
				unset($rows[$id]['reg_ses']);
				unset($rows[$id]['reg_crs_title']);
				unset($rows[$id]['ses_crs']);
				if ($query['filter']==''){
					unset($rows[$id]['reg_status']);
				}
			}
			// filtre role
			if ($query['filter2']=='reg_role'){
				$rows[$id]['reg_id']=$rows[$id]['reg_role'];
				unset($rows[$id]['reg_contact']);
				unset($rows[$id]['reg_ses']);
				unset($rows[$id]['reg_crs_title']);
				unset($rows[$id]['ses_crs']);
				if ($query['filter']==''){
					unset($rows[$id]['reg_status']);
					}
			}
			// filtre cours
			if ($query['filter2']=='ses_crs'){
				$rows[$id]['reg_id']=$rows[$id]['ses_crs'];
				unset($rows[$id]['reg_contact']);
				unset($rows[$id]['reg_ses']);
				if ($query['filter']==''){
					unset($rows[$id]['reg_status']);
				}
			}
			
		}
		$order = $query['order'];
		
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Registrations');
		if($query['search']){
			$GLOBALS['egw_info']['flags']['app_header'] .= ' - '.lang("Search for '%1'",$query['search']);
		}
		
		return $this->reg_so->total;	
    }
}
?>