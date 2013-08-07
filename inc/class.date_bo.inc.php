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
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.date_so.inc.php');	

class date_bo extends date_so{
	
	/**
	 * Constructeur 
	 *
	 */
	function date_bo(){
		parent::date_so();
	}
	
	function get_info($id){
	/**
	 * Retourne la liste des statuts avec les infos les concernant
	 *
	 * @return array
	 */
		return $this->so_date->read($id);
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
		// Application des droits 
		$GLOBALS['egw_info']['user']['SpiSessionLevel'] = acl_so::get_spisession_level();

		if($query['index']){
			unset($query['index']);
			$GLOBALS['egw']->session->appsession('date','spisession',$query);
		}

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

		// Filtre champs
		if(!empty($query['filter'])){
			$query['col_filter']['ses_date_ses'] = $query['filter'];
		}

		// Filtre statut
		if(!empty($query['filter2'])){
			$query['col_filter']['ses_date_status'] = $query['filter2'];
		}

		// recherche texte
		if(!is_array($query['search'])){
			$search = $this->construct_search($query['search']);
		}else{
			$search=$query['search'];
		}

		// Simple utilisateur = on masque les sessions archivés
		if($GLOBALS['egw_info']['user']['SpiSessionLevel'] == 1){
			$join .= ' INNER JOIN spisession_session ON ses_id = ses_date_ses WHERE ses_status <> '.$this->obj_config['archived_ses_status'];
		}

		// Filtre de date
		if(!empty($query['start_date'])){
			if(!empty($query['end_date'])){
			// Les deux dates sont remplis
				if(strpos($join, 'WHERE') !== FALSE){
					$join .= ' AND ses_date_day BETWEEN '.$query['start_date'].' AND '.$query['end_date'];
				}else{
					$join .= ' WHERE ses_date_day BETWEEN '.$query['start_date'].' AND '.$query['end_date'];
				}
			}else{
			// Uniquement une date de début
				if(strpos($join, 'WHERE') !== FALSE){
					$join .= ' AND ses_date_day > '.$query['start_date'];
				}else{
					$join .= ' WHERE ses_date_day > '.$query['start_date'];
				} 
			}
		}elseif(!empty($query['end_date'])){
		// Uniquement une date de fin
			if(strpos($join, 'WHERE') !== FALSE){
				$join .= ' AND ses_date_day < '.$query['end_date'];
			}else{
				$join .= ' WHERE ses_date_day < '.$query['end_date'];
			}
		}

		$rows = $this->so_date->search($search,false,$order,'',$wildcard,false,$op,$start,$query['col_filter'],$join);
		if(!$rows){
			$rows = array();
		}
		foreach((array)$rows as $id=>$value){
			$rows[$id]['day'] = lang(date('l',$rows[$id]['ses_date_day']));

			$readonlys['view['.$value['ses_date_id'].']'] = true;
			if($GLOBALS['egw_info']['user']['SpiSessionLevel'] < 10){
				$readonlys['edit['.$value['ses_date_id'].']']=true;
				$readonlys['view['.$value['ses_date_id'].']']=false;
			}
		}
		
		return $this->so_date->total;	
    }

    function get_date_status($status_id=''){
    /**
     * Retourne les statuts de date de session
     *
     * @return array
     */
    	$return = array();
		$info = $this->so_date_status->read($status_id);
		$return[$status_id] = $info['status_label'];
		if(!empty($status_id)){
			$transition = $this->so_date_status_transition->search(array('status_source' => $status_id),false);
			foreach((array)$transition as $key => $data){
				$childs[] = $data['status_target'];
			}
			
			foreach((array)$childs as $status_id){
				$info = $this->so_date_status->read($status_id);
				$return[$status_id] = $info['status_label'];
			}
		}else{
			$info = $this->so_date_status->search(array('status_active'=>'1'),false);
	    	foreach((array)$info as $data){
	    		$return[$data['status_id']] = $data['status_label'];
	    	}
		}
		
		return $return;
    }

	
	function get_sites($level=true){
    /**
     * Retourne la liste des sites
     *
     * @return array
     */
    	$site_ui = CreateObject('spireapi.site_ui');
		$return = $site_ui->get_possible_parents($level);
		
    	return $return;
    }

    function get_session($ses_id=''){
    /**
     * Retourne la liste des sessions
     *
     * @param $ses_id int : 
     * @return array
     */
    	$return = array();
    	
    	// Simple utilisateur = on masque les sessions archivés
		if($GLOBALS['egw_info']['user']['SpiSessionLevel'] == 1){
			$join = 'WHERE ses_status <> '.$this->obj_config['archived_ses_status'];
		}

    	if($ses_id == -1){
    		$info = $this->so_ses->search('',false,$order,'',$wildcard,false,$op,$start,$query['col_filter'],$join);
    	}else{
   			$info = $this->so_ses->search(array('ses_status' => explode(',',$this->obj_config['pending_ses_status'])),false,$order,'',$wildcard,false,$op,$start,$query['col_filter'],$join);

   			if(!empty($ses_id)){
   				$info[] = $this->so_ses->read($ses_id);
   			}
   		}

    	foreach((array)$info as $data){
    		$course = $this->so_crs->read($data['ses_crs']);
    		$return[$data['ses_id']] = '#'.$data['ses_id'].' - '.$course['crs_title'];
    	}

    	ksort($return);
    	return $return;
    }
}
?>