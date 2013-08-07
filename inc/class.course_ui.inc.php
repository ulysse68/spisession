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
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.course_bo.inc.php');



class course_ui extends course_bo{
	
	var $public_functions = array(
		'index'	=> true,
		'edit' 	=> true,
		'edit_comp' => true,
		'adv_search'=> true,
		'mail' => true,
		'pdf' => true,
		'pdf_list' => true,
	);
	
	/**
	 * Constructeur 
	 *
	 */
	function course_ui(){
		parent::course_bo();
		
		// Construction des droits - une seule fonction - dans class.acl_so.inc.php 
		$GLOBALS['egw_info']['user']['SpiSessionLevel'] = acl_so::get_spisession_level();
		// Gestion ACL - Simple utilisateur = Pas d'accès
		if ($GLOBALS['egw_info']['user']['SpiSessionLevel'] < 1){
			$GLOBALS['egw']->framework->render('<h1 style="color: red;">'.lang('Permission denied, please contact your administrator!!!')."</h1>\n",null,true);
			exit;
		}
		// Fin blocage au niveau du Constructeur 
		
		
	}
	
	function index($content = null){
	/**
	 * Charge le template index
	 */
		
	 // echo $GLOBALS['egw_info']['user']['SpiSessionLevel'];

		if(isset($_GET['msg'])){
			$msg = $_GET['msg'];
		}

		// Actions en masse
		if(isset($content['action'])){
			if (!count($content['nm']['rows']['checked']) && !$content['use_all']){
				$msg = lang('You need to select some courses first');
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
							$courses = implode(',',$content['nm']['rows']['checked']);
							echo "<html><body><script>window.open('".egw::link('/index.php','menuaction=spisession.course_ui.pdf&crs_id='.utf8_decode($courses))."','_blank','dependent=yes,width=750,height=600,scrollbars=yes,status=yes');</script></body></html>\n";
						}
						break;
					case 'pdf_list':
						if(is_array($content['nm']['rows']['checked'])){
							$courses = implode(',',$content['nm']['rows']['checked']);
							echo "<html><body><script>window.open('".egw::link('/index.php','menuaction=spisession.course_ui.pdf_list&crs_id='.utf8_decode($courses))."','_blank','dependent=yes,width=750,height=600,scrollbars=yes,status=yes');</script></body></html>\n";
						}
						break;
				}
			}

			unset($content['action']);
			unset($content['nm']['rows']['checked']);
		}

		// Reprendre les filtres existant s'il y en a (permet de garder la recherche courante lorsqu'on actualise via popup)
		$content['nm'] = $GLOBALS['egw']->session->appsession('course','spisession');
		
		if (!is_array($content['nm']))
		{
			$default_cols='crs_id,crs_label,crs_desc,crs_active,crs_order';
			$content['nm'] = array(                           // I = value set by the app, 0 = value on return / output
				'get_rows'       	=> 'spisession.course_bo.get_rows',	// I  method/callback to request the data for the rows eg. 'notes.bo.get_rows'
				'bottom_too'     	=> false,		// I  show the nextmatch-line (arrows, filters, search, ...) again after the rows
				'never_hide'     	=> true,		// I  never hide the nextmatch-line if less then maxmatch entrie
				'no_cat'         	=> true,
				'filter_no_lang' 	=> false,		// I  set no_lang for filter (=dont translate the options)
				'filter2_no_lang'	=> false,		// I  set no_lang for filter2 (=dont translate the options)
				'lettersearch'   	=> false,
				'options-cat_id' 	=> false,
				'start'          	=> 0,			// IO position in list
				'cat_id'         	=> '',			// IO category, if not 'no_cat' => True
				'search'         	=> '',// IO search pattern
				'order'          	=> 'crs_id',	// IO name of the column to sort after (optional for the sortheaders)
				'sort'           	=> 'ASC',		// IO direction of the sort: 'ASC' or 'DESC'
				'col_filter'     	=> array(),	// IO array of column-name value pairs (optional for the filterheaders)
				'filter_label'   	=> '',	// I  label for filter    (optional)
				'filter2_label'   	=> '',	// I  label for filter    (optional)
				'default_cols'   	=> $default_cols,
				'filter_onchange' 	=> "this.form.submit();",
				'filter2_onchange' 	=> "this.form.submit();",
				'no_csv_export'		=> true,
				'csv_fields'		=> false,
			);
		}
		
		$content['msg'] = $msg;

		// Listes
		$sel_options = array(
			'action' => array(
				'PDF' => array(
					// 'pdf_list' => lang('Print PDF list'),
					'pdf' => lang('Print PDF'),
				),
			),
			'crs_status'	=> $this->get_crs_status(),
			'crs_field'		=> $this->get_field(),
			'crs_grad'		=> $this->get_grad(),
			'comp_id'		=> $this->get_comp(),

			'filter'		=> array('' => lang('All subjects')) + $this->get_field(),
			'filter2'		=> array('' => lang('All status')) + $this->get_crs_status(),
		);
		
		$tpl = new etemplate('spisession.course.index');
		
		//Si gestionnaire ou admin, on affiche le bouton "add a session date"
		if($GLOBALS['egw_info']['user']['SpiSessionLevel'] > 10){
			$content['nm']['header_right'] = 'spisession.course.index.right';
		}else{ 
			unset ($content['nm']['header_right']);
		}

		
		
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Course Catalog');
		$tpl->read('spisession.course.index');
		$tpl->exec('spisession.course_ui.index', $content, $sel_options, $readonlys, array('nm' => $content['nm']));
	}
	
	function edit($content = null){
	/**
	 * Charge le template edit
	 */
		$msg='';
		$tabs = 'general|session|component|description|history';
		$current_tab = $content[$tabs];
	
		// Appuie sur un bouton (apply/save/cancel)
		if(is_array($content)){
			list($button) = @each($content['button']);
			switch($button){
				case 'save':
				case 'apply':
					$msg = $this->add_update_course($content);
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
			$id = $this->data['crs_id'];
			
			$content['msg']=$msg;
		}else{
			if(isset($_GET['id'])){
				$id=$_GET['id'];
			}else{
				$id='';
				
			}
		}

		// Ajout d'une composante de cours (On vérifie avant que la composante n'est pas deja presente pour le cours)
		if(isset($content['component']['button']['add_component'])){
			$exist = $this->so_crs_comp->search(array('crs_id'=>$content['crs_id'],'comp_id' => $content['component']['comp_id_add']),false);

			if(!is_array($exist)){
				$this->so_crs_comp->data = array(
					'comp_id' => $content['component']['comp_id_add'],
					'crs_comp_required' => $content['component']['crs_comp_required'],
					'crs_id' => $content['crs_id'],
				);
				$this->so_crs_comp->save();
			}else{
				$msg = lang('Error').' : '.lang('Component already existing for this course !');
			}
			$id = $content['crs_id'];
			unset($content);
		}

		// Suppression d'une composante
		if(isset($content['component']['delete'])){
			foreach((array)$content['component']['delete'] as $row_id => $data){
				$crs_comp = array(
					'comp_id' => $content['component'][$row_id]['comp_id'],
					'crs_id' => $content['component'][$row_id]['crs_id'],
				);
				// Contrôle si le composant n'est pas vide...
				if (!empty($crs_comp['comp_id']) AND !empty($crs_comp['crs_id']))
					{
					$this->so_crs_comp->delete($crs_comp);
					}
			}

			$id = $content['crs_id'];
			unset($content);
		}

		
		//Création refusée pour les non gestionaires...
		if(empty($id) and $GLOBALS['egw_info']['user']['SpiSessionLevel'] < 10){
			$GLOBALS['egw']->framework->render('<h1 style="color: red;">'.lang('Permission denied, please contact your administrator!!!')."</h1>\n",null,true);
			exit;
		}

		if(isset($id)){
			$content = array(
				'msg'         => $msg,
			);
			if(empty($id)){
				// Nouveau
				$GLOBALS['egw_info']['flags']['app_header'] = lang('Add Course');
				$content['crs_status'] = $this->obj_config['default_crs_status'];
				$content['crs_responsible'] = $GLOBALS['egw_info']['user']['account_id'];
					// On masque les onglets inutiles...
					$readonlys[$tabs]['history'] = true;
					$readonlys[$tabs]['session'] = true;
					$readonlys[$tabs]['component'] = true;
					
				
				
			}else{
				// Existant
				$content += $this->get_info($id);
				$content['history'] = array(
					'id'  => $id,
					'app' => 'spisession_crs',
				);
				$content['component'] = $this->get_crs_comp($id);	
				$content['session'] = $this->get_session($id, $readonlys);

				// Cours : si l'utilisateur n'est pas admin ou responsable, on le bloque...
				if($content['crs_responsible'] != $GLOBALS['egw_info']['user']['account_id'] and $GLOBALS['egw_info']['user']['SpiSessionLevel'] < 10){
					// $readonlys['__ALL__'] = true;
					$readonlys = array_merge($this->set_readonlys(),(array)$readonlys);
					$readonlys['component'] = true;

					$content['hideadd'] = true;
					$content['component']['hide_comp_add'] = true;
					$readonlys[$tabs]['history'] = true;					
				}

				$GLOBALS['egw_info']['flags']['app_header'] = lang('Edit Course');	
			}
		}
		$content['mode'] = $GLOBALS['egw_info']['flags']['app_header'];

		// Masque l'ajout de session lorsque le cours est archivé
		$content['hideadd'] = $content['hideadd'] ? true : $content['crs_status'] == $this->obj_config['archived_crs_status'] ? true : false;

		// Masque les données de relations avec spiclient si l'option n'est pas activé
		$content['hide_spiclient'] = $GLOBALS['egw_info']['apps']['spiclient'] ? true : $this->obj_config['use_spiclient'] == false ? true : false;

		$content[$tabs] = $current_tab;

		// Liste
		$sel_options = array(
			'crs_status'	=> $this->get_crs_status($content['crs_status']),
			'crs_field'		=> $this->get_field(),
			'crs_grad'		=> $this->get_grad(),
			'comp_id_add'	=> $this->get_comp($content['crs_id']),
			'comp_id'		=> $this->get_comp(),
			'ses_status' 	=> $this->get_ses_status(),
			'ses_location'	=> $this->get_sites(),
			'crs_provider'  => $this->get_provider(),
		);
		
		$tpl = new etemplate('spisession.course.edit');
		$tpl->read('spisession.course.edit');
		$tpl->exec('spisession.course_ui.edit', $content, $sel_options, $readonlys, $content,2);
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

			$query = $GLOBALS['egw']->session->appsession('crs_search','spisession');

			$query['advanced_search'] = array_intersect_key($_content,array_flip(array_merge($this->db_data_cols,array('startdate','enddate','account_id_interne','contact_id_externe','primary_group_externe','tel_externe','ticket_id','orga'))));
			foreach ($query['advanced_search'] as $key => $value)
			{
				if(!$value) unset($query['advanced_search'][$key]);
			}
			
			$query['start'] = 0;
			$query['search'] = '';
			
			// store the index state in the session
			
			$GLOBALS['egw']->session->appsession('crs_search','spisession',$query);
			
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
		$tpl = new etemplate('spisession.course.search');
		return $tpl->exec('spisession.course_ui.search', $content,$sel_options,$readonlys,$preserv,2);
	}

	function mail($content=null){
	/**
	* Charge l'e-template de mail, l'exécute avec les paramètres donnés.
	*
	* @param array $content = NULL
	*/
		// Appuie sur un bouton (apply/save/cancel)
		if(is_array($content)){
			list($button) = @each($content['button']);
			switch ($button){
				case 'cancel' :
					echo "<html><body><script>window.close();</script></body></html>\n";
					$GLOBALS['egw']->common->egw_exit();
					break;
				case 'send' :
					$msg = spisession_so::send_mail($content, true);
					unset($content['button']);
					break;
				default :
			}
		}
		if(($_GET['id'])){

			//infos concernant le client et l'émetteur de la facture
			$content['sendby'] = $GLOBALS['egw_info']['user']['email'];
			
			// Destinataires du fichier
			$content['sendcc'] = implode(',',$destinaires);
			
			//sujet du mail
			$content['subject']= lang('Folder download notification');
						
			// Contenu du message à envoyé
			$content['message'] = str_replace("\n","<br/>",$this->obj_config['mail_folder_notification']);
		}
		$content['msg'] = $msg;
		$tpl = new etemplate('spisession.mail');
		$tpl->exec('spisession.spisession_ui.mail', $content,$sel_options,$readonlys,$content,2);
	}

	function pdf($courses = null,$path = ''){
	/**
	 * Génère le fichier pdf correspondant à l'affaire
	 */
		// ob_start permet de faire une temporisation de sortie (permet d'éviter l'erreur (FPDF error: Some data has already been output, can't send PDF file)
		ob_start();
		if(isset($_GET['id'])){
			$pdf = CreateObject('spisession.generate_pdf_course',$_GET['id']);
			$pdf->generate($path);
		}else{
			$param = array();
			$courses = explode(',',$_GET['crs_id']);
			foreach((array)$courses as $crs_id){
				$param[] = $this->read($crs_id);
			}
			$pdf = CreateObject('spisession.generate_pdf_course',$param);
			$pdf->generate($path,$_GET['header']);
		}
	}

	function pdf_list($courses = null){
	/**
	 * Génère le fichier pdf correspondant à l'affaire
	 */
		// ob_start permet de faire une temporisation de sortie (permet d'éviter l'erreur (FPDF error: Some data has already been output, can't send PDF file)
		ob_start();
		if(isset($_GET['crs_id'])){
			$param = array();
			$courses = explode(',',$_GET['crs_id']);
			foreach((array)$courses as $crs_id){
				$param[] = $this->read($crs_id);
			}

			// $pdf = CreateObject('spisession.generate_pdf_session',$param);
			include('class.generate_pdf_course.inc.php');
			$pdf = new generate_pdf_course($param,true);
			$pdf->generate_list();
		}
		
		
	}
}
?>