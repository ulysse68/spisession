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
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.date_bo.inc.php');

class date_ui extends date_bo{
	
	var $public_functions = array(
		'index'	=> true,
		'edit' 	=> true,
		'search'=> true,
	);
	
	/**
	 * Constructeur 
	 *
	 */
	function date_ui(){
		parent::date_bo();
		
		// Construction des droits - une seule fonction - dans class.acl_so.inc.php 
		$GLOBALS['egw_info']['user']['SpiSessionLevel'] = acl_so::get_spisession_level();
		// Gestion ACL - Simple utilisateur = Pas d'accès
		if ($GLOBALS['egw_info']['user']['SpiSessionLevel'] < 1){
			$GLOBALS['egw']->framework->render('<h1 style="color: red;">'.lang('Permission denied, please contact your administrator!!!')."</h1>\n",null,true);
			exit;
		}
		// Fin blocage au niveau du Constructeur 

		/** CSS coloration en fonction des statuts **/
		$date_status = $this->so_date_status->search('',false);
		foreach((array)$date_status as $status){
			$css_status[] = '.date_status_'.$status['status_id'].' { background-color: '.$status['status_color'].'; }';
		}
		/*********/

		$css = '<STYLE type="text/css">
			<!--
				'.implode("\n",$css_status).'
			-->
		</STYLE>';
		$GLOBALS['egw_info']['flags']['java_script'] .= $css."\n";
		
	}
	
	function index($content = null){
	/**
	 * Charge le template index
	 */
		if(isset($_GET['msg'])){
			$msg = $_GET['msg'];
		}
		
		// Recupération des filtres existant s'il y en a
		$content['nm'] = $GLOBALS['egw']->session->appsession('date','spisession');

		if (!is_array($content['nm']))
		{
			$default_cols='ses_date_id,ses_date_ses,ses_date_title,ses_date_site,ses_date_day,ses_date_start,ses_date_end,ses_date_responsible,ses_date_status';
			$content['nm'] = array(                           // I = value set by the app, 0 = value on return / output
				'get_rows'       	=> 'spisession.date_bo.get_rows',	// I  method/callback to request the data for the rows eg. 'notes.bo.get_rows'
				'bottom_too'     	=> false,		// I  show the nextmatch-line (arrows, filters, search, ...) again after the rows
				'never_hide'     	=> true,		// I  never hide the nextmatch-line if less then maxmatch entrie
				'no_cat'         	=> true,
				'filter_no_lang' 	=> false,		// I  set no_lang for filter (=dont translate the options)
				'filter2_no_lang'	=> false,		// I  set no_lang for filter2 (=dont translate the options)
				'lettersearch'   	=> false,
				'options-cat_id' 	=> false,
				'start'          	=> 0,			// IO position in list
				'order'          	=> 'ses_date_day',	// IO name of the column to sort after (optional for the sortheaders)
				'sort'           	=> 'ASC',		// IO direction of the sort: 'ASC' or 'DESC'
				'filter_label'   	=> lang('Session'),	// I  label for filter    (optional)
				'filter2_label'   	=> lang('Status'),	// I  label for filter    (optional)
				'filter'         	=> $_GET['filter'],	// =All	// IO filter, if not 'no_filter' => True
				'default_cols'   	=> $default_cols,
				'filter_onchange' 	=> "this.form.submit();",
				'filter2_onchange' 	=> "this.form.submit();",
				'no_csv_export'		=> true,
				'csv_fields'		=> false,

				'index' => true,
			);
		}
	
		if(!empty($_GET['filter']))	$content['nm']['filter'] = $_GET['filter'];

		$content['msg'] = $msg;

		// Listes
		$sel_options = array(
			'ses_date_status' => $this->get_date_status(),
			'ses_date_ses' => $this->get_session(-1),
			'ses_date_site' => $this->get_sites(false),
			'filter' => array(''=>lang('All')) + $this->get_session(),
			'filter2' => array(''=>lang('All')) + $this->get_date_status(),
		);
		
		$tpl = new etemplate('spisession.date.index');
		
		//Si gestionnaire ou admin, on affiche le bouton "add a session date"
		if($GLOBALS['egw_info']['user']['SpiSessionLevel'] > 10){
			$content['nm']['header_right'] = 'spisession.date.index.right';
		}else{ 
			unset ($content['nm']['header_right']);
		}
		
		$content['nm']['header_left'] = 'spisession.index.left';
		
		// Date par defaut
		$content['nm']['start_date'] = mktime(0,0,0,date("m"),1,date("Y"));
		$content['nm']['end_date'] = mktime(0,0,0,date("m")+3,1,date("Y"))-1;

		$GLOBALS['egw_info']['flags']['app_header'] = lang('Session dates management');
		$tpl->read('spisession.date.index');
		$tpl->exec('spisession.date_ui.index', $content, $sel_options, $readonlys, array('nm' => $content['nm']));
	}
	
	function edit($content = null){
	/**
	 * Charge le template edit
	 */
		$msg='';
	
		// Clic sur un bouton (apply/save/cancel)
		if(is_array($content)){
			list($button) = @each($content['button']);
			switch($button){
				case 'save':
				case 'apply':
					$msg = $this->add_update_date($content);
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
			$id = $this->so_date->data['date_id'];
			
			$content['msg']=$msg;
		}else{
			if(isset($_GET['id'])){
				$id=$_GET['id'];
			}else{
				$id='';
				
			}
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
				$GLOBALS['egw_info']['flags']['app_header'] = lang('Add date');
				$content['ses_date_ses'] = $_GET['ses_id'];
				$content['ses_date_status'] = $this->obj_config['default_date_status'];
				$content['ses_date_responsible'] = $GLOBALS['egw_info']['user']['account_id'];
			}else{
				// Existant
				$content += $this->get_info($id);
				
				// Date de session : si l'utilisateur n'est pas admin ou responsable, on le bloque...
				if($content['ses_date__responsible'] != $GLOBALS['egw_info']['user']['account_id'] and $GLOBALS['egw_info']['user']['SpiSessionLevel'] < 10){
					 $readonlys['__ALL__'] = true;
					 $content['component']['hide_comp_add'] = true;
					 $readonlys[$tabs]['history'] = true;					
				}
				
				$GLOBALS['egw_info']['flags']['app_header'] = lang('Edit date');	
			}
		}
		
		// Listes
		$sel_options = array(
			'ses_date_status' => $this->get_date_status(),
			'ses_date_ses' => $this->get_session($content['ses_date_ses']),
			'ses_date_site' => $this->get_sites(),
		);
		
		$content['mode'] = $GLOBALS['egw_info']['flags']['app_header'];

		$tpl = new etemplate('spisession.date.edit');
		$tpl->read('spisession.date.edit');
		$tpl->exec('spisession.date_ui.edit', $content, $sel_options, $readonlys, $content,2);
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

			$query = $GLOBALS['egw']->session->appsession('date_search','spisession');

			$query['advanced_search'] = array_intersect_key($_content,array_flip(array_merge($this->db_data_cols,array('startdate','enddate','account_id_interne','contact_id_externe','primary_group_externe','tel_externe','ticket_id','orga'))));
			foreach ($query['advanced_search'] as $key => $value)
			{
				if(!$value) unset($query['advanced_search'][$key]);
			}
			
			$query['start'] = 0;
			$query['search'] = '';
			
			// store the index state in the session
			
			$GLOBALS['egw']->session->appsession('date_search','spisession',$query);
			
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
		$tpl = new etemplate('spisession.date.search');
		return $tpl->exec('spisession.date_ui.search', $content,$sel_options,$readonlys,$preserv,2);
	}
}
?>