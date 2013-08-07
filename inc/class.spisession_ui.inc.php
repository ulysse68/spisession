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


require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.spisession_bo.inc.php');	

class spisession_ui extends spisession_bo{
	/**
	 * Methods callable via menuaction
	 *
	 * @var array
	 */
	var $public_functions = array(
		'index' 	=> true,
		'edit' 		=> true,
		'about' 	=> true,
		'pdf' 		=> true,
		'pdf_list' 	=> true,
		'mail' 		=> true,
		'download' 	=> true,

		'search'	=> true,
	);

	/**
	 * Constructeur 
	 *
	 */
	function spisession_ui()
	{
		// Récupération des groupes de l'utilisateur
		$groupeUser = array_keys($GLOBALS['egw']->accounts->memberships($GLOBALS['egw_info']['user']['account_id']));
		

		parent::spisession_bo();
		
		// Construction des droits - une seule fonction - dans class.acl_so.inc.php 
		$GLOBALS['egw_info']['user']['SpiSessionLevel'] = acl_so::get_spisession_level();
		// Gestion ACL - Simple utilisateur = Pas d'accès
		if ($GLOBALS['egw_info']['user']['SpiSessionLevel'] < 1){
			$GLOBALS['egw']->framework->render('<h1 style="color: red;">'.lang('Permission denied, please contact your administrator!!!')."</h1>\n",null,true);
			exit;
		}
		// Fin blocage au niveau du Constructeur 


		/** CSS coloration des statuts des inscriptions et des dates**/
		$reg_status = $this->so_reg_status->search('',false);
		foreach((array)$reg_status as $status){
			$css_status[] = '.reg_status_'.$status['status_id'].' { background-color: '.$status['status_color'].'; }';
		}

		$date_status = $this->date_ui->so_date_status->search('',false);
		foreach((array)$date_status as $status){
			$css_status[] = '.date_status_'.$status['status_id'].' { background-color: '.$status['status_color'].'; }';
		}
		/*********/

		$css = '<STYLE type="text/css">
			<!--
				'.implode("\n",$css_status).'
			-->
		</STYLE>';
		$GLOBALS['egw_info']['flags']['java_script'].=$css."\n";		
		
		$GLOBALS['egw_info']['flags']['java_script'] .= $this->write_javascript();
	}

	function index($content=null){
	/**
	 * Charge le template index
	 */
		// Actions en masse
		if(isset($content['action'])){
			if (!count($content['nm']['rows']['checked']) && !$content['use_all']){
				$msg = lang('You need to select some sessions first');
			}else{
				if($content['use_all']){
					$query = $content['nm'];
					@set_time_limit(0);
					$query['num_rows'] = -1;
					$this->get_rows($query,$temp,$readonlys);
					foreach((array)$temp as $ses){
						$content['nm']['rows']['checked'][] = $ses['ses_id'];
					}
				}
				
				switch($content['action']){
					case 'pdf':
						if(is_array($content['nm']['rows']['checked'])){
							$sessions = implode(',',$content['nm']['rows']['checked']);
							echo "<html><body><script>window.open('".egw::link('/index.php','menuaction=spisession.spisession_ui.pdf&ses_id='.utf8_decode($sessions))."','_blank','dependent=yes,width=750,height=600,scrollbars=yes,status=yes');</script></body></html>\n";
						}
						break;
					case 'pdf_list':
						if(is_array($content['nm']['rows']['checked'])){
							$sessions = implode(',',$content['nm']['rows']['checked']);
							echo "<html><body><script>window.open('".egw::link('/index.php','menuaction=spisession.spisession_ui.pdf_list&ses_id='.utf8_decode($sessions))."','_blank','dependent=yes,width=750,height=600,scrollbars=yes,status=yes');</script></body></html>\n";
						}
						break;
				}
			}

			unset($content['action']);
			unset($content['nm']['rows']['checked']);
		}

		// Inscription a une session
		if(!empty($content['nm']['rows']['reg'])){
			foreach((array)$content['nm']['rows']['reg'] as $ses_id => $data){
				$exist = $this->so_reg->search(array('reg_account'=>$GLOBALS['egw_info']['user']['account_id'],'reg_ses'=>$ses_id));

				// Si le contact n'est pas lié a la session
				if(!is_array($exist)){
					$link_app = 'addressbook';
					$link_id = $GLOBALS['egw_info']['user']['person_id'];
					if (preg_match('/^[a-z_0-9-]+:[:a-z_0-9-]+$/i',$link_app.':'.$link_id)){
						$link = egw_link::link('spisession',$ses_id,$link_app,$link_id,$content['registration']['reg_role']);
					}

					// Recuperation du nombre de participants (non rejete et non desiste)
					$registrations = $this->so_reg->search(array('reg_ses' => $ses_id,'reg_role'=>$this->obj_config['student_role']),false);
					$nb_reg = 0;
					foreach((array)$registrations as $registration){
						if($registration != $this->obj_config['rejected_reg_status'] && $registration != $this->obj_config['pending_reg_status']){
							$nb_reg++;
						}
					}

					// Si le max de participant est atteint alors on inscrit l'utilisateur sur la liste d'attente
					if($nb_reg >= $content['ses_max_participant']){
						$status = $this->obj_config['pending_reg_status'];
					}

					$this->so_reg->data = array(
						'reg_ses' => $ses_id,
						'reg_contact' => $GLOBALS['egw_info']['user']['person_id'],
						'reg_account' => $GLOBALS['egw_info']['user']['account_id'],
						'reg_link' => $link,
						'reg_role' => $this->obj_config['student_role'],
						'reg_status' => $status ? $status : $this->obj_config['default_reg_status'],
						'reg_creator' => $GLOBALS['egw_info']['user']['account_id'],
						'reg_created' => time()
					);
					$this->so_reg->save();

					$msg = lang('Registration successfull');
				}else{
					$msg = lang('Error').' : '.lang('User already registered for this session');
				}

			}

			unset($content['nm']['rows']['reg']);
		}

		// Desinscription à une session
		if(!empty($content['nm']['rows']['dereg'])){
			foreach((array)$content['nm']['rows']['dereg'] as $ses_id => $data){
				$reg = $this->so_reg->search(array('reg_account'=>$GLOBALS['egw_info']['user']['account_id'],'reg_ses'=>$ses_id),false);
				$reg = $reg[0];

				$reg['reg_status'] = $this->obj_config['rejected_reg_status'];
				$reg['reg_modifier'] = $GLOBALS['egw_info']['user']['account_id'];
				$reg['reg_modified'] = time();
				$this->so_reg->data = $reg;
				$this->so_reg->save();

				$msg = lang('Deregistration successfull');
			}

			unset($content['nm']['rows']['dereg']);
		}

		// Recuperation des filtres de recherche existant
		if(empty($content['nm']))
			$content['nm'] = $GLOBALS['egw']->session->appsession('ses','spisession');

		if (!is_array($content['nm']))
		{
			$default_cols='ses_id,ses_crs,ses_start_date,ses_end_date,ses_location,ses_responsible,nb_date,registration,ses_status';
			$content['nm'] = array(                           // I = value set by the app, 0 = value on return / output
				'get_rows'       	=>	'spisession.spisession_bo.get_rows',	// I  method/callback to request the data for the rows eg. 'notes.bo.get_rows'
				'bottom_too'     	=> false,		// I  show the nextmatch-line (arrows, filters, search, ...) again after the rows
				'never_hide'     	=> true,		// I  never hide the nextmatch-line if less then maxmatch entrie
				'no_cat'         	=> true,
				'filter_no_lang' 	=> true,		// I  set no_lang for filter (=dont translate the options)
				'filter2_no_lang'	=> true,		// I  set no_lang for filter2 (=dont translate the options)
				'lettersearch'   	=> false,
				'options-cat_id' 	=> false,
				'start'          	=>	0,			// IO position in list
				'cat_id'         	=>	'',			// IO category, if not 'no_cat' => True
				'search'         	=>	'',// IO search pattern
				'order'          	=>	'ses_start_date',	// IO name of the column to sort after (optional for the sortheaders)
				'sort'           	=>	'ASC',		// IO direction of the sort: 'ASC' or 'DESC'
				'col_filter'     	=>	array(),	// IO array of column-name value pairs (optional for the filterheaders)
				'filter_label'   	=>	'',	// I  label for filter    (optional)
				'filter'         	=>	'',	// =All	// IO filter, if not 'no_filter' => True
				'default_cols'   	=> $default_cols,
				'filter_onchange' 	=> "this.form.submit();",
				'filter2_onchange' 	=> "this.form.submit();",
				'no_csv_export'		=> false,
				'csv_fields'		=> $this->export(),
				'no_columnselection'=> true,

				// SPIREA
				'index' => true,
			);
		}

		// Bascule du filtre de vue dans la liste filter2
		if(isset($_GET['view'])){
			$content['nm']['filter2'] = $_GET['view'];
			unset($content['nm']['col_filter']);
		}else{
			if(empty($content['nm']['filter2']))
				$content['nm']['filter2'] = 'all';
		}
		
		// Listes
		$sel_options = array(
			'filter' => array(''=>lang('All courses')) + $this->get_crs(),
			'filter2' => array(
				'own' => lang('My sessions'),
				'pending' => lang('Pending sessions'),
				'archived' => lang('Archived sessions'),
				'all' => lang('All sessions'),
				'all-in-time' => lang('All sessions at all times'),
			),
			'action' => array(
				'PDF' => array(
					'pdf_list' => lang('Print PDF list'),
					'pdf' => lang('Print PDF'),
				),
				'Status' => array(
					'cancel' => lang('Cancel sessions'),
					'archive' => lang('Archive sessions'),
				),
			),
			'ses_crs' => $this->get_crs(-1),
			'ses_status' => $this->get_ses_status(),
			'ses_location'	=> $this->get_sites(false),

			'filter_date' => $this->get_date_filter(),
			'filter_reg' => $this->get_reg_filter(),
			'filter_location' => $this->get_sites(false),
		);
		
		//Si gestionnaire ou admin, on affiche le bouton "add a session date"
		if($GLOBALS['egw_info']['user']['SpiSessionLevel'] > 10){
			$content['nm']['header_right'] = 'spisession.index.right';
		}else{ 
			unset ($content['nm']['header_right']);
		}

		$content['nm']['header_left'] = 'spisession.index.left';
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Sessions management');

		
		$content['nm']['start_date'] = $content['nm']['start_date'] ? $content['nm']['start_date'] : mktime(0,0,0,date("m"),1,date("Y"));
		$content['nm']['end_date'] = $content['nm']['end_date'] ? $content['nm']['end_date'] : mktime(0,0,0,date("m")+6,1,date("Y"))-1;

		
		$content['hide_adv_search'] = !$_GET['adv_search'];

		$content['msg'] = $msg."\n".$_GET['msg'];

		$tpl = new etemplate('spisession.index');
		$tpl->read('spisession.index');
		$tpl->exec('spisession.spisession_ui.index', $content, $sel_options, $readonlys, array('nm' => $content['nm']));
	}
	
	function edit($content=null){
	/**
	 * Fonction de création/modification d'une session
	 *
	 * @param $content
	 */
		$tabs = 'date|registration|description|cost|link|history';

		// récupération de l'onglet sur lequel on se trouvait
		$tab = $content[$tabs];
	
		// Appuie sur un bouton (apply/save/cancel)
		if(is_array($content)){
			list($button) = @each($content['button']);
			switch($button){
				case 'save':
				case 'apply':
					$msg = $this->add_update_session($content);
					if($button=='save'){
						echo "<html><body><script>var referer = opener.location;opener.location.href = referer+(referer.search?'&':'?')+'msg=".
							addslashes(urlencode($msg))."'; window.close();</script></body></html>\n";
						$GLOBALS['egw']->common->egw_exit();
					}
					$GLOBALS['egw_info']['flags']['java_script'] .= "<script language=\"JavaScript\">
						var referer = opener.location;
						opener.location.href = referer+(referer.search?'&':'?')+'msg=".addslashes(urlencode($msg))."';</script>";
					break;
				case 'cancel':
					echo "<html><body><script>window.close();</script></body></html>\n";
					$GLOBALS['egw']->common->egw_exit();
					break;
			}
			$id = $content['ses_id'];
		}else{

			if(isset($_GET['id'])){
				$id=$_GET['id'];
			}else{
				$id='';
				
			}
		}

		// Ajout d'un contact a la session
		if($content['registration']['button']['add_contact'] and (!empty($content['registration']['reg_contact']) || !empty($content['registration']['reg_account']))){
			if(!empty($content['registration']['reg_contact']) && !empty($content['registration']['reg_account'])){
				$msg = lang('Error while saving').' '.lang('Please select an account or a contact but not both at the same time');
			}else{
				// Vérifie si le contact est lié a la session
				if(!empty($content['registration']['reg_contact']))
					$exist_contact = $this->so_reg->search(array('reg_contact'=>$content['registration']['reg_contact'],'reg_ses'=>$content['ses_id']),false);

				// Vérifie si le compte utilisateur est lié a la session
				if(!empty($content['registration']['reg_account']))
					$exist_account = $this->so_reg->search(array('reg_account'=>$content['registration']['reg_account'],'reg_ses'=>$content['ses_id']),false);
				
				// Si le contact n'est pas deja lie a la session
				if((!is_array($exist_contact) || $exist_contact[0]['reg_status'] == $this->obj_config['rejected_reg_status']) && (!is_array($exist_account) || $exist_account[0]['reg_status'] == $this->obj_config['rejected_reg_status'])){
					$link_app = 'addressbook';
					$link_id = $content['registration']['reg_contact'];
					if (preg_match('/^[a-z_0-9-]+:[:a-z_0-9-]+$/i',$link_app.':'.$link_id)){
						$link = egw_link::link('spisession',$content['ses_id'],$link_app,$link_id,$content['registration']['reg_role']);
					}
					
					// Si on ajoute avec un statut différent de rejeté
					if($content['registration']['reg_role'] == $this->obj_config['student_role'] && $content['registration']['reg_status'] != $this->obj_config['rejected_reg_status']){

						// Recuperation du nombre d'eleve qui sont ni rejeté ni desisté)
						$registrations = $this->so_reg->search(array('reg_ses' => $content['ses_id'],'reg_role'=>$this->obj_config['student_role']),false);
						$nb_reg = 0;
						foreach((array)$registrations as $registration){
							if($registration == $this->obj_config['validated_reg_status']){
								$nb_reg++;
							}
						}

						// Si le max de participants est atteint on mets le contact sur liste d'attente
						if($nb_reg >= $content['ses_max_participant']){
							$content['registration']['reg_status'] = $this->obj_config['pending_reg_status'];
						}
					}

					// Contrôle sur les inscriptions, rôles et statuts obligatoire...
					if ($content['registration']['reg_role'] >0 && $content['registration']['reg_status'] > 0)
					{
						if(empty($content['registration']['reg_contact'])){
							$account = $GLOBALS['egw']->accounts->read($content['registration']['reg_account']);
							$content['registration']['reg_contact'] = $account['person_id'];
						}

						$this->so_reg->data = array(
							'reg_id' => $exist[0]['reg_id'],
							'reg_ses' => $content['ses_id'],
							'reg_contact' => $content['registration']['reg_contact'],
							'reg_account' => $content['registration']['reg_account'],
							'reg_link' => $link,
							'reg_role' => $content['registration']['reg_role'],
							'reg_status' => $content['registration']['reg_status'],
							'reg_creator' => $GLOBALS['egw_info']['user']['account_id'],
							'reg_created' => time()
						);

						$this->so_reg->save();
						$msg = $this->notify($this->so_reg->data);
					}else{
						$msg = lang('Error while saving').' '.lang('The contact must have one role and one status to be registered');
					}					
				}else{
					$msg = lang('Error').' : '.lang('User already linked to this session');
				}
			}
			
			$id = $content['ses_id'];
			unset($content);
		}

		// Rejeté le contact choisi
		if(isset($content['registration']['delete'])){
			foreach((array)$content['registration']['delete'] as $reg_id => $value){
				$reg = $this->so_reg->read($reg_id);

				$reg['reg_status'] = $this->obj_config['rejected_reg_status'];
				$reg['reg_modifier'] = $GLOBALS['egw_info']['user']['account_id'];
				$reg['reg_modified'] = time();
				$this->so_reg->data = $reg;
				$this->so_reg->save();

				$msg = $this->notify($this->so_reg->data);
			}

			$id = $content['ses_id'];
			unset($content);
		}

		// Désistement du contact
		if(isset($content['registration']['desist'])){
			foreach((array)$content['registration']['desist'] as $reg_id => $value){
				$reg = $this->so_reg->read($reg_id);

				$reg['reg_status'] = $this->obj_config['desistement_reg_status'];
				$reg['reg_modifier'] = $GLOBALS['egw_info']['user']['account_id'];
				$reg['reg_modified'] = time();
				$this->so_reg->data = $reg;
				$this->so_reg->save();

				$msg = $this->notify($this->so_reg->data);
			}

			$id = $content['ses_id'];
			unset($content);
		}

		// Mettre le contact choisi sur liste d'attente
		if(isset($content['registration']['wait'])){
			foreach((array)$content['registration']['wait'] as $reg_id => $value){
				$reg = $this->so_reg->read($reg_id);

				$reg['reg_status'] = $this->obj_config['pending_reg_status'];
				$reg['reg_modifier'] = $GLOBALS['egw_info']['user']['account_id'];
				$reg['reg_modified'] = time();
				$this->so_reg->data = $reg;
				$this->so_reg->save();

				$msg = $this->notify($this->so_reg->data);
			}

			$id = $content['ses_id'];
			unset($content);
		}

		// Confirmer le contact choisi
		if(isset($content['registration']['confirm'])){
			foreach((array)$content['registration']['confirm'] as $reg_id => $value){
				$reg = $this->so_reg->read($reg_id);

				$reg['reg_status'] = $this->obj_config['validated_reg_status'];
				$reg['reg_modifier'] = $GLOBALS['egw_info']['user']['account_id'];
				$reg['reg_modified'] = time();
				$this->so_reg->data = $reg;
				$this->so_reg->save();

				$msg = $this->notify($this->so_reg->data);
			}

			$id = $content['ses_id'];
			unset($content);
		}

		// Gestion des liens (onglet liens)
		if(isset($_REQUEST['exec']['link_to']['app']) && isset($content['link_to']['to_id']) && isset($content['ses_id'])){
			$link_ids = is_array($content['link_to']['to_id']) ? $content['link_to']['to_id'] : array($content['link_to']['to_id']);
			foreach(is_array($content['link_to']['to_id']) ? $content['link_to']['to_id'] : array($content['link_to']['to_id']) as $n => $link_app){
				$link_id = $link_ids[$n]['id'];
				$link_app = $link_ids[$n]['app'];
				if (preg_match('/^[a-z_0-9-]+:[:a-z_0-9-]+$/i',$link_app.':'.$link_id)){
					egw_link::link('spisession',$content['ses_id'],$link_app,$link_id);
				}
			}
		}

		
		// Création refusée pour les non gestionaires
		if(empty($id) and $GLOBALS['egw_info']['user']['SpiSessionLevel'] < 10){
			$GLOBALS['egw']->framework->render('<h1 style="color: red;">'.lang('Permission denied, please contact your administrator!!!')."</h1>\n",null,true);
			exit;
		}
		
		// On actualise le $content uniquement s'il est vide ou si on vient d'appeler save/apply
		if(isset($id)){
			// unset($content['button']);
			$content = array(
				'msg'         => $msg,
				'link_to' => array(
					'to_id' => $id,
					'to_app' => 'spisession',
				),
			);

			if(isset($id)){
				if(empty($id)){
				// Si l'id est vide - c'est une création
					$GLOBALS['egw_info']['flags']['app_header'] = lang('Add session');
					$content['ses_crs'] = $_GET['crs_id'];
					$content['ses_status'] = $this->obj_config['default_ses_status'];
					$content['ses_responsible'] = $GLOBALS['egw_info']['user']['account_id'];
					$content['ses_lang'] = $GLOBALS['egw_info']['user']['preferences']['common']['lang'];
					
					$readonlys['crs_link'] = true;

					// On masque les onglets inutiles...
					$readonlys[$tabs]['history'] = true;
					$readonlys[$tabs]['link'] = true;
					$readonlys[$tabs]['registration'] = true;
					$readonlys[$tabs]['date'] = true;
					

				}else{
					$content += $this->get_info($id);

					// Utilisateur = acces refusé si archivé
					if ($GLOBALS['egw_info']['user']['SpiSessionLevel'] == 1 && $content['ses_status'] == $this->obj_config['archived_ses_status']){
						$GLOBALS['egw']->framework->render('<h1 style="color: red;">'.lang('Permission denied, please contact your administrator!!!')."</h1>\n",null,true);
						exit;
					}

					$content['registration'] = $this->get_contact($id);
					$content['date'] = $this->get_date($id, $readonlys);

					$content['history'] = array(
						'id'  => $id,
						'app' => 'spisession',
					);

					// Recupération du nombre d'eleve inscrit sur la session
					$confirmed_participants = $this->get_contact($content['ses_id'],$this->obj_config['student_role'],$this->obj_config['validated_reg_status']);
					// Max participant atteint
					if(count($confirmed_participants) >= $content['ses_max_participant'] && $content['ses_max_participant'] != 0){
						$max_reached = true;
					}

					// Supprimer les boutons de changement de statut d'inscription en fonction du statut actuel
					foreach ($content['registration'] as $key => $reg_data) {
						switch ($reg_data['reg_status']) {
							case $this->obj_config['validated_reg_status']:
								$readonlys['confirm['.$reg_data['reg_id'].']'] = true;
								break;
							case $this->obj_config['rejected_reg_status']:
								$readonlys['delete['.$reg_data['reg_id'].']'] = true;
								break;
							case $this->obj_config['pending_reg_status']:
								$readonlys['wait['.$reg_data['reg_id'].']'] = true;
								break;
							case $this->obj_config['desistement_reg_status']:
								$readonlys['desist['.$reg_data['reg_id'].']'] = true;
								break;
						}
						if($max_reached && $reg_data['reg_role'] == $this->obj_config['student_role']) $readonlys['confirm['.$reg_data['reg_id'].']'] = true;
					}
					
					// Cours : si l'utilisateur n'est pas admin ou responsable, on le bloque...
					if($content['ses_responsible'] != $GLOBALS['egw_info']['user']['account_id'] AND $GLOBALS['egw_info']['user']['SpiSessionLevel'] < 10){
						// $readonlys['__ALL__'] = true;
						$readonlys = array_merge($this->set_readonlys(),(array)$readonlys);
						$readonlys['link_to'] = true;
						$content['hideadd'] = true;
						$content['registration']['no_add'] = true;
						$content['hidemail'] = true;
						$content['no_links'] = true;

						$content['date']['hide_comp_add'] = true;
						$readonlys[$tabs]['history'] = true;

						$readonlys['date']['view'] = false;
					}

					$readonlys['ses_crs'] = true;
					
					$GLOBALS['egw_info']['flags']['app_header'] = lang('Edit session');	
				}
			}
		}
		$content['mode'] = $GLOBALS['egw_info']['flags']['app_header'];

		// Masque l'ajout de session lorsque le cours est archivé
		$content['hideadd'] = $content['hideadd'] ? true : $content['ses_status'] == $this->obj_config['archived_ses_status'] ? true : false;
		
		// Masque les données de relations avec spiclient si l'option n'est pas activé
		$content['hide_spiclient'] = $GLOBALS['egw_info']['apps']['spiclient'] ? true : $this->obj_config['use_spiclient'] == false ? true : false;

		// Retour sur l'onglet où l'utilisateur se trouvait
		$content[$tabs] = $tab;

		// Listes
		$sel_options = array(
			'ses_crs' => $this->get_crs($content['ses_crs']),
			'ses_status' => $this->get_ses_status($content['reg_role']),
			'reg_status' => $this->get_reg_status(),

			'ses_date_ses' => $this->date_ui->get_session(),
			'ses_date_status' => $this->date_ui->get_date_status(),
			'reg_role' => $this->get_role(),
			'ses_location' => $this->get_sites(),
			'ses_date_site' => $this->get_sites(),

			'ses_provider' => $this->get_provider(),
			'ses_client' => $this->get_client(),
			'ses_vat' => $this->get_vat(),
		);
		
		$tpl = new etemplate('spisession.edit');
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Edit session');
		$tpl->read('spisession.edit');
		$tpl->exec('spisession.spisession_ui.edit', $content, $sel_options, $readonlys, $content,2);
	}

	function pdf($sessions = null,$path = ''){
	/**
	 * Génère le fichier pdf correspondant à l'affaire
	 */
		// ob_start permet de faire une temporisation de sortie (permet d'éviter l'erreur (FPDF error: Some data has already been output, can't send PDF file)
		ob_start();
		if(isset($_GET['id'])){
			$pdf = CreateObject('spisession.generate_pdf_session',$_GET['id']);
			$pdf->generate($path);
		}else{
			$param = array();
			$sessions = explode(',',$_GET['ses_id']);
			foreach((array)$sessions as $ses_id){
				$param[] = $this->read($ses_id);
			}

			$pdf = CreateObject('spisession.generate_pdf_session',$param);
			$pdf->generate($path,$_GET['header']);
		}
	}
	
	function pdf_list($sessions = null){
	/**
	 * Génère le fichier pdf correspondant à l'affaire
	 */
		// ob_start permet de faire une temporisation de sortie (permet d'éviter l'erreur (FPDF error: Some data has already been output, can't send PDF file)
		ob_start();
		if(isset($_GET['ses_id'])){
			$param = array();
			$sessions = explode(',',$_GET['ses_id']);
			foreach((array)$sessions as $ses_id){
				$param[] = $this->read($ses_id);
			}

			// $pdf = CreateObject('spisession.generate_pdf_session',$param);
			include('class.generate_pdf_session.inc.php');
			$pdf = new generate_pdf_session($param,true);
			$pdf->generate_list();
		}
	}
	
	function mail($content=null){
	/**
	* Charge l'e-template de mail, l'exécute avec les paramètres donnés.
	*
	* @param array $content = NULL
	*/
		// Appuie sur un bouton (send/cancel)
		if(is_array($content)){
			list($button) = @each($content['button']);
			switch ($button){
				case 'cancel' :
					echo "<html><body><script>window.close();</script></body></html>\n";
					$GLOBALS['egw']->common->egw_exit();
					break;
				case 'send' :
					$msg = $this->send_mail($content, true);

					echo "<html><body><script>var referer = opener.location;opener.location.href = referer+(referer.search?'&':'?')+'msg=".
							addslashes(urlencode($msg))."'; window.close();</script></body></html>\n";

					break;
				default :
			}
		}
		if(($_GET['id'])){
			// Informations de la session
			$session = $this->read($_GET['id']);
			$content['title'] = $session['ses_title'];
			
			// Emetteur
			$content['sendby'] = $GLOBALS['egw_info']['user']['email'];
			
			// Destinataires
			$ses_contact = $this->so_reg->search(array('reg_ses' => $session['ses_id']),false);
			
			foreach((array)$ses_contact as $reg_info){
				$contact = $GLOBALS['egw']->contacts->read($reg_info['reg_contact']);
				
				if(!empty($contact['email'])){
					$receivers[$contact['email']] = $contact['email'];
				}
			}
			$content['sendto'] = implode(',',$receivers);
			
			// Sujet du mail
			$content['subject']= lang('Session notification');
						
			// Contenu du message à envoyer
			$content['message'] = str_replace("\n","<br/>",$this->obj_config['mail_ses_notif']);
			
			$url = $GLOBALS['egw_info']['server']['webserver_url'].'/index.php?menuaction=spisession.spisession_ui.edit&id='.$session['ses_id'];
			$content['message'] .= "<hr>".lang('Link to the session').' : <a href="'.$url.'">'.lang('Click here').'</a>';
		}
		$content['msg'] = $msg;
		$tpl = new etemplate('spisession.mail');
		$tpl->exec('spisession.spisession_ui.mail', $content,$sel_options,$readonlys,$content,2);
	}
	
	function write_javascript(){
	/**
	* Initialise le code javascript (tableaux)
	*
	* @return string
	*/
		return '';
	}

	function about(){
	/**
	* Affiche le boite de dialogue 'A propos ...'
	*/
		$lg = 'en';
		if ($GLOBALS['egw_info']['user']['preferences']['common']['lang'] == 'fr'){
			$lg = 'fr';
		}

		$content=$sel_options=$readonlys=array();
		$lines=file(EGW_INCLUDE_ROOT.'/spisession/about/about_'.$lg.'.txt');
		$content['about']="";
		foreach ($lines as $line_num => $line) {
			$content['about'].=htmlspecialchars($line) . "<br />\n";
		}
				
		$tpl = new etemplate('spisession.about');
		$tpl->exec('spisession.spisession_ui.about', $content,$sel_options,$readonlys,$content,0);
	}

	function adv_search($_content=array()){
	/**
	* Recherche $_content si défini et affiche le résultat ($_content est recherché en comparaison avec lec colonnes actuellement affichées. La recherche est 'intelligente')
	* Sinon, affiche tous les tickets concernant l'utilisateur courant
	*
	* @param array $_content=array()
	* @return bool
	*/
	
		if(!empty($_content)) {
			$response = new xajaxResponse();

			$query = $GLOBALS['egw']->session->appsession('ses_search','spisession');

			$query['advanced_search'] = array_intersect_key($_content,array_flip(array_merge($this->db_data_cols,array('startdate','enddate','account_id_interne','contact_id_externe','primary_group_externe','tel_externe','ticket_id','orga'))));
			foreach ($query['advanced_search'] as $key => $value)
			{
				if(!$value) unset($query['advanced_search'][$key]);
			}
			
			$query['start'] = 0;
			$query['search'] = '';
			
			// store the index state in the session
			$GLOBALS['egw']->session->appsession('ses_search','spisession',$query);
			
			// store the advanced search in the session to call it again
			$GLOBALS['egw']->session->appsession('advanced_search','spisession',$query['advanced_search']);
			
			$response->addScript("
				var link = opener.location.href;
				link = link.replace(/#/,'');
				opener.location.href=link.replace(/\#/,'');
				xajax_eT_wrapper();
			");
			
			 return $response->getXML();
				exit;
		}
		else {
			
		}
		
		/* on recharge le template */
		$tpl = new etemplate('spisession.search');
		return $tpl->exec('spisession.session_ui.search', $content,$sel_options,$readonlys,$preserv,2);
	}
}
?>