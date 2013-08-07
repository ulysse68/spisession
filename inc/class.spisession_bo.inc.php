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

require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.spisession_so.inc.php');	

class spisession_bo extends spisession_so{
	
	var $obj_config;

	function spisession_bo()
	{
		$config = CreateObject('phpgwapi.config');
		$this->obj_config = $config->read('spisession');
		parent::spisession_so();
	}
	
	function get_info($id){
	/**
	 * Retourne les informations de la session
	 *
	 * @param int $id : identifiant de la session
	 * @return array
	 */
		$info = $this->read($id);

		// Lien vers le cours
		$info['crs_link'] = '<a href="index.php?menuaction=spisession.course_ui.edit&amp;id='.$info['ses_crs'].'" onclick="window.open(this,this.target,\'width=990,height=600,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false;">'.lang('See course').'</a>';
		
		return $info;
	}

	function get_rows($query,&$rows,&$readonlys){
	/**
	 * Récupère et filtre les sessions
	 *
	 * @param array $query avec des clefs comme 'start', 'search', 'order', 'sort', 'col_filter'. Pour définir d'autres clefs comme 'filter', 'cat_id', vous devez créer une classe fille
	 * @param array &$rows lignes complétés
	 * @param array &$readonlys pour mettre les lignes en read only en fonction des ACL, non utilisé ici (à utiliser dans une classe fille)
	 * @return int
	 */
		if($query['index']){
			// unset($query['index']);
			$GLOBALS['egw']->session->appsession('ses','spisession',$query);
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
		$join = '';
		
		// Filter2 - Type de session
		switch ($query['filter2']) {
			case 'own':
				$query['filter_resp'] = $GLOBALS['egw_info']['user']['account_id'];
				$query['col_filter']['ses_responsible'] = $GLOBALS['egw_info']['user']['account_id'];
				break;
			case 'archived':
				$query['col_filter']['ses_status'] = $this->obj_config['archived_ses_status'];
				break;
			case 'pending':
				$query['col_filter']['ses_status'] = explode(',',$this->obj_config['pending_ses_status']);
				break;
			case 'default':
			case 'all-in-time':
				unset($content['nm']['start_date']);
				unset($content['nm']['end_date']);
				unset($query['start_date']);
				unset($query['end_date']);
				break;
		}

		// Filtre cours
		if(!empty($query['filter'])){
			$query['col_filter']['ses_crs'] = $query['filter'];
		}

		// Recherche champ texte
		if(!is_array($query['search'])){
			$search = $this->construct_search($query['search']);
			$search['crs_title'] = $query['search'];
			$join = 'INNER JOIN spisession_course ON crs_id = ses_crs ';
		}else{
			$search=$query['search'];
		}
			
		/*** Recherche avancée ***/
		// Langue
		if(!empty($query['filter_lang'])){
			$query['col_filter']['ses_lang'] = $query['filter_lang'];
		}

		// Lieu
		if(!empty($query['filter_location'])){
			$query['col_filter']['ses_location'] = $query['filter_location'];
		}

		// responsable
		if(!empty($query['filter_resp'])){
			$query['col_filter']['ses_responsible'] = $query['filter_resp'];
		}

		// Inscription
		if(!empty($query['filter_reg'])){
			switch ($query['filter_reg']) {
				case 'registered':
					// Registered as a participant + Not rejected AND Not on the waiting list
					// Participant + non rejete et pas sur la liste d'attente
					$join .= 'INNER JOIN spisession_registration ON ses_id = reg_ses WHERE reg_account = '.$GLOBALS['egw_info']['user']['account_id'].' AND reg_status <> '.$this->obj_config['rejected_reg_status'].' AND reg_status <> '.$this->obj_config['pending_reg_status'].' AND reg_role = '.$this->obj_config['student_role'];
					break;
				case 'waiting_list':
					// Participant + liste d'attente
					$join .= 'INNER JOIN spisession_registration ON ses_id = reg_ses WHERE reg_account = '.$GLOBALS['egw_info']['user']['account_id'].' AND reg_status = '.$this->obj_config['pending_reg_status'].' AND reg_role = '.$this->obj_config['student_role'];
					break;
				case 'reg_possible':
					// Inscription ouverte + nb_participant < max_participant + pas deja inscrit
					$join .= 'WHERE ses_start_reg < '.time().' AND ses_end_reg > '.time().' AND ses_id NOT IN (SELECT reg_ses FROM spisession_registration WHERE reg_account = '.$GLOBALS['egw_info']['user']['account_id'].' AND reg_role = '.$this->obj_config['student_role'].' AND reg_status <> '.$this->obj_config['rejected_reg_status'].')';
					break;
				case 'waiting_possible':
					// Inscription ouverte + nb_participant >= max_participant + pas deja inscrit
					$join .= 'WHERE ses_start_reg < '.time().' AND ses_end_reg > '.time().' AND ses_id NOT IN (SELECT reg_ses FROM spisession_registration WHERE reg_account = '.$GLOBALS['egw_info']['user']['account_id'].' AND reg_role = '.$this->obj_config['student_role'].' AND reg_status <> '.$this->obj_config['rejected_reg_status'].')';
					break;
			}
		}

		// Date de session ?
		if(!empty($query['filter_date'])){
			switch ($query['filter_date']) {
				case 'futur_date':
					$join .= 'INNER JOIN spisession_session_date ON ses_date_ses = ses_id WHERE ses_date_day > '.time();
					break;
				case 'past_date':
					$join .= 'WHERE ses_id IN (SELECT A.ses_date_ses FROM spisession_session_date A WHERE A.ses_date_day < '.time().' AND ses_date_ses NOT IN ( SELECT B.ses_date_ses FROM spisession_session_date B WHERE B.ses_date_day > '.time().'))';
					break;
				case 'without_date':
					$join .= 'WHERE ses_id NOT IN (SELECT ses_date_ses FROM spisession_session_date)';
					break;
			}
		}

		/*** fin recherche avancee ***/

		// Filtre de date
		if(!empty($query['start_date'])){
			if(!empty($query['end_date'])){
			// Les deux dates sont remplis
				if(strpos($join, 'WHERE') !== FALSE){
					$join .= ' AND (ses_start_date BETWEEN '.$query['start_date'].' AND '.$query['end_date'].' OR ses_end_date BETWEEN '.$query['start_date'].' AND '.$query['end_date'].')';
				}else{
					$join .= 'WHERE (ses_start_date BETWEEN '.$query['start_date'].' AND '.$query['end_date'].' OR ses_end_date BETWEEN '.$query['start_date'].' AND '.$query['end_date'].')';
				}
			}else{
			// Uniquement une date de début
				if(strpos($join, 'WHERE') !== FALSE){
					$join .= ' AND (ses_start_date > '.$query['start_date'].' AND ses_end_date > '.$query['start_date'].')';
				}else{
					$join .= ' WHERE (ses_start_date > '.$query['start_date'].' AND ses_end_date > '.$query['start_date'].')';
				} 
			}
		}elseif(!empty($query['end_date'])){
		// Uniquement une date de fin
			if(strpos($join, 'WHERE') !== FALSE){
				$join .= ' AND (ses_start_date < '.$query['end_date'].' AND ses_end_date < '.$query['end_date'].')';
			}else{
				$join .= ' WHERE (ses_start_date < '.$query['end_date'].' AND ses_end_date < '.$query['end_date'].')';
			}
		}

		// Simple utilisateur = on masque les sessions archivés
		if($GLOBALS['egw_info']['user']['SpiSessionLevel'] == 1){
			if(strpos($join, 'WHERE') !== FALSE){
				$join .= ' AND ses_status <> '.$this->obj_config['archived_ses_status'];
			}else{
				$join .= ' WHERE ses_status <> '.$this->obj_config['archived_ses_status'];
			}
		}

		$rows = $this->search($search,false,$order,'',$wildcard,false,$op,$start,$query['col_filter'],$join);
		if(!$rows){
			$rows = array();
		}

		foreach((array)$rows as $id=>$value){
			$dates = $this->get_date($value['ses_id']);
			$rows[$id]['nb_date'] = count($dates);
		
			
			$confirmed_participants = $this->get_contact($value['ses_id'],$this->obj_config['student_role'],$this->obj_config['validated_reg_status']);
			$unconfirmed_participants = $this->get_contact($value['ses_id'],$this->obj_config['student_role'],$this->obj_config['unvalidated_reg_status']);
			$pending_participants = $this->get_contact($value['ses_id'],$this->obj_config['student_role'],$this->obj_config['pending_reg_status']);
			

			$rows[$id]['nb_participants'] = count($confirmed_participants);
			
			$rows[$id]['label_nb_participants'] = count($confirmed_participants);
			
			$rows[$id]['stats_nb_participants'] = $rows[$id]['nb_participants'] .' '.lang('confirmed participants').'&#10;';
			
			if (count($unconfirmed_participants) > 0){
				$rows[$id]['stats_nb_participants'] .= count($unconfirmed_participants).' '.lang('unconfirmed participants').'&#10;';
			}
			
			if ($rows[$id]['ses_max_participant'] > 0){
				$rows[$id]['label_nb_participants'] .= ' / '.$rows[$id]['ses_max_participant'];
				$rows[$id]['stats_nb_participants'] .= $rows[$id]['ses_max_participant'].' '.lang('participants at max').'&#10;';
			}
			
			if (count($pending_participants) > 0){
				$rows[$id]['stats_nb_participants'] .= count($pending_participants).' '.lang('pending inscriptions').'&#10;';
			}
						
			// Début - gestion des droits
			$readonlys['view['.$value['ses_id'].']'] = false;
			$readonlys['edit['.$value['ses_id'].']'] = true;
			$readonlys['adddate['.$value['ses_id'].']'] = true;
			$readonlys['delete['.$value['ses_id'].']'] = true;
			$readonlys['mail['.$value['ses_id'].']'] = true;
			
			// Si responsable du cours ou bien admin, on affiche en mode edition
			if($value['crs_responsible'] == $GLOBALS['egw_info']['user']['account_id'] || $GLOBALS['egw_info']['user']['SpiSessionLevel'] > 10){
				$readonlys['view['.$value['ses_id'].']']=true;
				$readonlys['edit['.$value['ses_id'].']']=false;
				$readonlys['adddate['.$value['ses_id'].']'] = false;
				$readonlys['delete['.$value['ses_id'].']'] = false;
				$readonlys['mail['.$value['ses_id'].']'] = false;
			}

			// Sessions archivés, on retire le bouton d'ajout de date...
			if(in_array($value['ses_status'],explode(',',$this->obj_config['archived_ses_status']))){
				$readonlys['adddate['.$value['ses_id'].']'] = true;
			}
			//Fin gestion des droits
			
			
			// Inscription / desinscription en fonction des statuts
			$reg = $this->so_reg->search(array('reg_account'=>$GLOBALS['egw_info']['user']['account_id'],'reg_ses'=>$rows[$id]['ses_id']),false);
			if(is_array($reg)){
				if($reg[0]['reg_status'] != $this->obj_config['rejected_reg_status']){
					// Inscrit + non rejete => Desinscription + date de session
					$readonlys['reg['.$rows[$id]['ses_id'].']'] = true;
					$readonlys['dereg['.$rows[$id]['ses_id'].']'] = false;
					$readonlys['ses_date['.$rows[$id]['ses_id'].']'] = false;
				}else{
					// Inscrit + rejete => date de session
					$readonlys['reg['.$rows[$id]['ses_id'].']'] = true;
					$readonlys['dereg['.$rows[$id]['ses_id'].']'] = true;
					$readonlys['ses_date['.$rows[$id]['ses_id'].']'] = false;
				}
			}else{
				// Pas inscrit => Inscription
				$readonlys['reg['.$rows[$id]['ses_id'].']'] = false;
				$readonlys['dereg['.$rows[$id]['ses_id'].']'] = true;
				$readonlys['ses_date['.$rows[$id]['ses_id'].']'] = true;
			}
			
			// Inscription ouverte ?
			$currentTime = time();
			if($currentTime < $rows[$id]['ses_start_reg'] || $currentTime > $rows[$id]['ses_end_reg']+86399){
				// Inscription fermée => masque inscription et desinscription
				$readonlys['reg['.$rows[$id]['ses_id'].']'] = true;
				$readonlys['dereg['.$rows[$id]['ses_id'].']'] = true;
			}
			
			/* champs pour l'export csv */
			if($query['csv_export']==true)
			{
				$rows[$id]['ses_start_date_export'] = date('d/m/Y',$rows[$id]['ses_start_date']);
				$rows[$id]['ses_end_date_export'] = date('d/m/Y',$rows[$id]['ses_end_date']);
				$rows[$id]['ses_start_reg_export'] = date('d/m/Y',$rows[$id]['ses_start_reg']);
				$rows[$id]['ses_end_reg_export'] = date('d/m/Y',$rows[$id]['ses_end_reg']);
				$rows[$id]['ses_end_date_export'] = date('d/m/Y',$rows[$id]['ses_end_date']);
				// Champs issus du referentiel...
				$ses_crs_temp = $this->so_crs->read($rows[$id]['ses_crs']);
				$rows[$id]['ses_crs_export'] = $ses_crs_temp['crs_title'];
				unset($ses_crs_temp);
				$ses_status_temp = $this->so_ses_status->read($rows[$id]['ses_status']);
				$rows[$id]['ses_status_export'] =  lang($ses_status_temp['status_label']);
				unset($ses_status_temp);
				// Champs site issus de SpireAPI
				$site_ui = CreateObject('spireapi.site_ui');
				$ses_location_temp = $site_ui->so_site->read($rows[$id]['ses_location']);
				$rows[$id]['ses_location'] =  $ses_location_temp['site_label'];
				unset($ses_location_temp);
				
			}
			// fin traitement export csv
		
		}

		return $this->total;	
    }

	function get_crs($crs_id='',$crs_status=''){
    /**
     * Retourne la liste des cours disponible
     *
     * @param $crs_id int : identifiant de cours (-1 = tous)
     * @param $crs_status int : statut de cours
     * @return array
     */
    	$return = array();
    	
		if ($crs_status == ''){
			$criteria = array('crs_status' => explode(',',$this->obj_config['pending_crs_status']));
		}
		if ($crs_status == 'all'){
			$criteria = array();
		}
		
		
    	if($crs_id == -1){
    		$info = $this->so_crs->search('',false);
    	}else{
   			$info = $this->so_crs->search($criteria,false);

   			if(!empty($crs_id)){
   				$info[] = $this->so_crs->read($crs_id);
   			}
   		}

    	foreach((array)$info as $data){
    		$return[$data['crs_id']] = $data['crs_title'];
    	}

    	return $return;
    }

    function get_ses_status($status_id=''){
    /**
     * Retourne la liste des statuts de sessions
     *
     * @param $status_id : identifiant de statut
     * @return array
     */
    	$return = array();
		$info = $this->so_ses_status->read($status_id);
		$return[$status_id] = $info['status_label'];
		if(!empty($status_id)){
			$transition = $this->so_ses_status_transition->search(array('status_source' => $status_id),false);
			foreach((array)$transition as $key => $data){
				$childs[] = $data['status_target'];
			}
			
			foreach((array)$childs as $status_id){
				$info = $this->so_ses_status->read($status_id);
				$return[$status_id] = $info['status_label'];
			}
		}else{
			$info = $this->so_ses_status->search(array('status_active'=>'1'),false);
	    	foreach((array)$info as $data){
	    		$return[$data['status_id']] = $data['status_label'];
	    	}
		}
		
		return $return;
    }

    function get_reg_status(){
    /**
     * Retourne la liste des statuts d'inscription
     *
     * @return array
     */
    	$return = array();
    	$info = $this->so_reg_status->search(array('status_active'=>'1'),false);
    	foreach((array)$info as $data){
    		$return[$data['status_id']] = $data['status_label'];
    	}

    	return $return;
    }

    function get_role(){
    /**
     * Retourne la liste des roles
     *
     * @return array
     */
    	$return = array();
    	$info = $this->so_role->search(array('role_active'=>'1'),false,'role_order');
    	foreach((array)$info as $data){
    		$return[$data['role_id']] = $data['role_label'];
    	}

    	return $return;
    }

	function get_sites($level=true){
    /**
     * Retourne la liste des sites
     *
     * @param $level : le sous site apparaitront differement
     * @return array
     */
    	$site_ui = CreateObject('spireapi.site_ui');
		$return = $site_ui->get_possible_parents($level);
		
    	return $return;
    }
	
	
    function get_contact($ses_id,$reg_role='',$reg_status=''){
	/**
	 * Retourne la liste des contacts de la session en cours
	 *
	 * @param $ses_id : Id de la session
	 * @param $reg_role : role 
	 * @param reg_status : statut d'inscription
	 * @return array
	 */
		$return = $registrations = array();
		$i = 3;
		
		$registrations = $this->so_reg->search(array('reg_ses' => $ses_id,'reg_role' =>$reg_role,'reg_status'=>$reg_status),false,'role_order','',$wildcard,false,'AND',$start,$query['col_filter'],'INNER JOIN spisession_ref_role ON role_id = reg_role');

		foreach((array)$registrations as $registration){
			// $contact = $GLOBALS['egw']->contacts->read($registration['reg_contact']);
			// if(!empty($registration['reg_contact'])){
				// Contact
				$return[$i] = $registration + array(
					'link' => '<a href=\'\' onclick="window.open(\''.$GLOBALS['egw_info']['server']['webserver_url'].'/index.php?menuaction=addressbook.addressbook_ui.edit&contact_id='.$registration['reg_contact'].'\',\'\',\'width=600,height=600,scrollbars=1\')">'.$this->get_contact_fn($registration['reg_contact']).'</a>',
				);
			// }else{
			// 	// Compte
			// 	$return[$i] = $registration + array(
			// 		'link' => $this->get_contact_fn($registration['reg_account'],true),
			// 	);
			// }
			++$i;
		}

		return $return;
	}

	function truncate_word($string, $limit=30, $break="-", $pad="...") { 
	/**
	 * Retourne le mot $string tronqué a $limit caractere
	 */
		if(strlen($string) <= $limit) return $string; 
		$string = substr($string, 0, $limit) . $pad; 
		return $string; 
	}

	function get_contact_fn($id,$account = false){
	/**
	 * Retourne le fullname (n_fn) du contact ayant l'id $id
	 *
	 * @param $id : identifiant du contact
	 * @param $account : le contact est en fait un compte ?
	 *
	 * @return string : n_fn du contact
	 */
		if($account){
			$account = $GLOBALS['egw']->accounts->read($id);
			return $this->truncate_word($account['account_lastname'].' '.$account['account_firstname']);
		}else{
			$contact = $GLOBALS['egw']->contacts->read($id);
			return empty($contact['n_fn']) ? $this->truncate_word($contact['n_family'].' '.$contact['n_given']) : $this->truncate_word($contact['n_fn']);
		}
	}

	function get_date($ses_id, &$readonlys=array()){
	/**
	 * Retourne les dates de sessions
	 *
	 * @param $ses_id : session id
	 * @return array
	 */
		$sessions = $return = array();
		$i = 1;
		$query = array(
			'col_filter' => array('ses_date_ses'=>$ses_id),
			'order' => 'ses_date_day, ses_date_start',
		);
		$this->date_ui->get_rows($query,$dates,$readonlys);
		foreach((array)$dates as $date){
			$return[$i] = $date;
			++$i;
		}

		return $return;
	}

	function get_date_filter(){
	/**
	 * Retourne les valeurs du filtre date dans la recherche avancée
	 *
	 * @return array
	 */
		$return = array(
			'futur_date' => lang('With at least one future date'),
			'past_date' => lang('With past dates only'),
			'without_date' => lang('Without any dates'),
		);
		return $return;
	}

	function get_reg_filter(){
	/**
	 * Retourne les valeurs du filtre inscription dans la recherche avancée
	 *
	 * @return array
	 */
		$return = array(
			'registered' => lang('Registered'),
			'waiting_list' => lang('Registered on the waiting list'),
			'reg_possible' => lang('Available for direct registration'),
			// Prochaine version - 'waiting_possible' => lang('Available for waiting list'),
		);
		return $return;
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

	function get_client(){
	/**
	 * Retourne la liste des clients possible
	 *
	 * @return array
	 */
		if($GLOBALS['egw_info']['apps']['spiclient']){
			$client_ui = CreateObject('spiclient.client_ui');
			
			$config = CreateObject('phpgwapi.config');
			$config_spiclient = $config->read('spiclient');

			return $client_ui->get_all_clients($config_spiclient['ClientType']);
		}
	}

	function get_vat(){
	/**
	 * Retourne la liste des taux de tva utilisable (depuis spireapi)
	 *
	 * @return array
	 */
		require_once(EGW_INCLUDE_ROOT. '/spireapi/inc/class.vat_bo.inc.php');	
		return vat_bo::get_vat();
	}
	
	/**
	 * get title for an tracker item identified by $entry
	 *
	 * Is called as hook to participate in the linking
	 *
	 * @param int/array $entry int ts_id or array with tracker item
	 * @return string/boolean string with title, null if tracker item not found, false if no perms to view it
	 */
	function link_title($entry)
	{
		if (!is_array($entry))
		{
			$entry = $this->read($entry);
		}
		if (!$entry)
		{
			return $entry;
		}

		$course = $this->so_crs->read($entry['ses_crs']);

		return '#'.$entry['ses_id'].': '.$course['crs_title'];
	}

	/**
	 * get titles for multiple tracker items
	 *
	 * Is called as hook to participate in the linking
	 *
	 * @param array $ids array with tracker id's
	 * @return array with titles, see link_title
	 */
	function link_titles($ids)
	{
		$titles = array();
		if (($references = $this->search(array('ses_id' => $ids),'ses_id,ses_crs')))
		{
			foreach((array)$references as $reference)
			{
				$titles[$reference['ses_id']] = $this->link_title($reference);
			}
		}
		// we assume all not returned tickets are not readable by the user, as we notify egw_link about each deleted ticket
		foreach((array)$ids as $id)
		{
			if (!isset($titles[$id])) $titles[$id] = false;
		}
		return $titles;
	}

	/**
	 * query clients for entries matching $pattern
	 *
	 * Is called as hook to participate in the linking
	 *
	 * @param string $pattern pattern to search
	 * @return array with client_id - client_company pairs of the matching entries
	 */
	function link_query($pattern)
	{
		$result = array();
		foreach((array) $this->search(array('ses_id' => $pattern),false,'ses_id ASC','','%',false,'OR',false,'') as $item )
		{
			if ($item) $result[$item['ses_id']] = $this->link_title($item);
		}
		return $result;
	}
	
	function export(){
	/**
	 * Retourne la liste des champs a exporter
	 *
	 * Voir la fonction get_rows pour le traitement special si le flag d'export est defini
	 *
	 * @return array
	 */
		$retour = array(
			'ses_id' => 'ses_id',
			'ses_crs' => 'ses_crs',
			'ses_crs_export' => 'ses_crs_title',
			'ses_start_date_export' => 'ses_start_date',
			'ses_end_date_export' => 'ses_end_date',
			'ses_start_reg_export' => 'ses_start_reg',
			'ses_end_reg_export' => 'ses_end_reg',
			'ses_location' => 'ses_location',
			'ses_responsible' => 'ses_responsible',
			'ses_status_export' => 'ses_status',
			'ses_min_participant' => 'ses_min_participant',
			'ses_max_participant' => 'ses_max_participant',
			'ses_client' => 'ses_client',
			'ses_provider' => 'ses_provider',
			'ses_amount' => 'ses_amount',
			'ses_vat' => 'ses_vat',
			'ses_conv_date' => 'ses_conv_date',
			'nb_participants' => 'nb_participants',

		);
		/* Champs non retournés
			'ses_cost' => 'ses_cost',
			'ses_lang' => 'ses_lang',
			'ses_desc' => 'ses_desc',
			'ses_creator' => 'ses_creator',
			'ses_created' => 'ses_created',
			'ses_modifier' => 'ses_modifier',
			'ses_modified' => 'ses_modified',
			'nb_date' => 'nb_date',
		*/
		
		return $retour;
	}
	
}
?>