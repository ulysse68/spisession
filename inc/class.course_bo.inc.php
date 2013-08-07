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
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.course_so.inc.php');	

class course_bo extends course_so{
	
	/**
	 * Constructeur 
	 *
	 */
	function course_bo(){
		parent::course_so();
	}
	
	function get_info($id){
	/**
	 * Retourne les informations d'un cours
	 *
	 * @param $id : identifiant du cours
	 * @return array
	 */
		return $this->read($id);
	}
	
	function get_rows($query,&$rows,&$readonlys){
	/**
	 * Récupère et filtre les cours
	 *
	 * @param array $query avec des clefs comme 'start', 'search', 'order', 'sort', 'col_filter'. Pour définir d'autres clefs comme 'filter', 'cat_id', vous devez créer une classe fille
	 * @param array &$rows lignes complétés
	 * @param array &$readonlys pour mettre les lignes en read only en fonction des ACL, non utilisé ici (à utiliser dans une classe fille)
	 * @return int
	 */
		$GLOBALS['egw']->session->appsession('course','spisession',$query);

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
		$join = '';
		
		// Field filter
		if(!empty($query['filter'])){
			$query['col_filter']['crs_field'] = $query['filter'];
		}

		// Status filter
		if(!empty($query['filter2'])){
			$query['col_filter']['crs_status'] = $query['filter2'];
		}

		// Simple utilisateur = on masque les cours archivés
		if($GLOBALS['egw_info']['user']['SpiSessionLevel'] == 1){
			$join = 'WHERE crs_status <> '.$this->obj_config['archived_crs_status'];
		}

		// Recherche texte
		if(!is_array($query['search'])){
			$search = $this->construct_search($query['search']);
		}else{
			$search=$query['search'];
		}

		$rows = $this->search($search,false,$order,'',$wildcard,false,$op,$start,$query['col_filter'],$join);
		if(!$rows){
			$rows = array();
		}
		
		foreach((array)$rows as $id=>$value){
			$sessions = $this->get_session($value['crs_id']);
			$rows[$id]['nb_sessions'] = count($sessions);
			
			// Début - gestion des droits
			// par défaut - on masque les deux...
			$readonlys['view['.$value['crs_id'].']'] = false;
			$readonlys['edit['.$value['crs_id'].']'] = true;
			$readonlys['add['.$value['crs_id'].']'] = true;
			// Si responsable du cours ou bien admin, on affiche en mode edition
			if($value['crs_responsible'] == $GLOBALS['egw_info']['user']['account_id'] || $GLOBALS['egw_info']['user']['SpiSessionLevel'] > 10){
				$readonlys['view['.$value['crs_id'].']']=true;
				$readonlys['edit['.$value['crs_id'].']']=false;
				$readonlys['add['.$value['crs_id'].']'] = false;
			}

			// Cours archivés, on retire le bouton d'ajout de sessions...
			if(in_array($value['crs_status'],explode(',',$this->obj_config['archived_crs_status']))){
				$readonlys['add['.$value['crs_id'].']'] = true;
			}
			//Fin gestion des droits
	
		}
		
		$order = $query['order'];
		
		return $this->total;	
    }

    function get_crs_status($status_id=''){
    /**
     * Retourne la liste des statuts de cours disponible
     *
     * @return array
     */
    	$return = array();
		$info = $this->so_crs_status->read($status_id);
		$return[$status_id] = $info['status_label'];
		if(!empty($status_id)){
			$transition = $this->so_crs_status_transition->search(array('status_source' => $status_id),false);
			foreach((array)$transition as $key => $data){
				$childs[] = $data['status_target'];
			}
			
			foreach((array)$childs as $status_id){
				$info = $this->so_crs_status->read($status_id);
				$return[$status_id] = $info['status_label'];
			}
		}else{
			$info = $this->so_crs_status->search(array('status_active'=>'1'),false);
	    	foreach((array)$info as $data){
	    		$return[$data['status_id']] = $data['status_label'];
	    	}
		}
		
		return $return;
    }

	function get_field(){
	/**
     * Retourne la liste des champs disponible
     *
     * @return array
     */
		$return = array();
    	$info = $this->so_field->search(array('field_active'=>'1'),false,'field_order');
    	foreach((array)$info as $data){
    		$return[$data['field_id']] = $data['field_label'];
    	}

    	return $return;
	}

	function get_grad(){
	/**
     * Retourne la liste des natations
     *
     * @return array
     */
		$return = array();
    	$info = $this->so_grad->search(array('grad_active'=>'1'),false,'grad_order');
    	foreach((array)$info as $data){
    		$return[$data['grad_id']] = $data['grad_label'];
    	}

    	return $return;
	}

	function get_comp($crs_id=''){
	/**
     * Retourne la liste des composantes
     *
     * @return array
     */
		$return = array();
    	$info = $this->so_comp->search(array('comp_active'=>'1'),false,'comp_order');

    	if(!empty($crs_id)){
	    	$used_comp = $this->so_crs_comp->search(array('crs_id'=> $crs_id),'comp_id');
	    	foreach((array)$used_comp as $data){
	    		$comp[] = $data['comp_id'];
	    	}
	    }

    	foreach((array)$info as $data){
    		if(!in_array($data['comp_id'], (array)$comp)){
    			$return[$data['comp_id']] = $data['comp_label'];
    		}
    	}

    	return $return;
	}

	function get_crs_comp($crs_id){
	/**
	 * Retourne la liste des composantes pour le cours $crs_id
	 *
	 * @param $crs_id : identifiant du cours
	 * @return array
	 */
		$return = array();
		$info = $this->so_crs_comp->search(array('crs_id'=> $crs_id),false);

		$i = 1;
		foreach ($info as $key => $value) {
			$return[$i] = $value;
			++$i;
		}

		return $return;
	}

	function get_session($crs_id, &$readonlys=array()){
	/**
	 * Retourne la liste des sessions d'un cours
	 *
	 * @param $crs_id int : id of the course
	 * @return array
	 */
		$sessions = $return = array();
		$i = 1;
		$query = array(
			'col_filter' => array('ses_crs'=>$crs_id),
			'order' => 'ses_id',
		);
		$this->ses_ui->get_rows($query,$sessions,$readonlys);
		foreach((array)$sessions as $session){
			$return[$i] = $session;
			++$i;
		}

		return $return;
	}

	function get_ses_status(){
	/**
	 * Retourne la liste des statuts de session
	 *
	 * @return array
	 */
		return $this->ses_ui->get_ses_status();
	}

	function get_sites(){
	/**
	 * Retourne la liste des sites
	 *
	 * @return array
	 */
		return $this->ses_ui->get_sites();
	}

	function get_provider(){
	/**
	 * Retourne la liste des fournisseurs possible
	 *
	 * @return array
	 */
		if($GLOBALS['egw_info']['apps']['spiclient']){
			$client_ui = CreateObject('spiclient.client_ui');
		
			$config = CreateObject('phpgwapi.config');
			$config_spiclient = $config->read('spiclient');

			return $client_ui->get_all_clients($config_spiclient['ProviderType']);
		}
	}
}
?>