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
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.registration_bo.inc.php');


class registration_ui extends registration_bo{
	
	var $public_functions = array(
		'index'	=> true,
	);
	
	/**
	 * Constructeur 
	 *
	 */
	function registration_ui(){
		parent::registration_bo();
		
				
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
		if(isset($_GET['msg'])){
			$msg = $_GET['msg'];
		}

		if (!is_array($content['nm']))
		{
			$default_cols='reg_id,status_label,status_description,status_active,status_order';
			$content['nm'] = array(                           // I = value set by the app, 0 = value on return / output
				'get_rows'       	=>	'spisession.registration_bo.get_rows_registrations',	// I  method/callback to request the data for the rows eg. 'notes.bo.get_rows'
				'bottom_too'     	=> false,		// I  show the nextmatch-line (arrows, filters, search, ...) again after the rows
				'never_hide'     	=> true,		// I  never hide the nextmatch-line if less then maxmatch entrie
				'no_cat'         	=> true,
				'filter_no_lang' 	=> false,		// I  set no_lang for filter (=dont translate the options)
				'filter2_no_lang'	=> false,		// I  set no_lang for filter2 (=dont translate the options)
				'lettersearch'   	=> false,
				'no_filter2'		=> false,
				'options-cat_id' 	=> false,
				'start'          	=>	0,			// IO position in list
				'cat_id'         	=>	'',			// IO category, if not 'no_cat' => True
				'search'         	=>	'',// IO search pattern
				'order'          	=>	'reg_id',	// IO name of the column to sort after (optional for the sortheaders)
				'sort'           	=>	'DESC',		// IO direction of the sort: 'ASC' or 'DESC'
				'col_filter'     	=>	array(),	// IO array of column-name value pairs (optional for the filterheaders)
				'filter_label'   	=>	'',	// I  label for filter    (optional)
				'filter'         	=>	'',	// =All	// IO filter, if not 'no_filter' => True
				'filter2_label'   	=>	lang('Group by'),	// I  label for filter    (optional)
				'filter2'         	=>	'',	// =All	// IO filter, if not 'no_filter' => True
				'default_cols'   	=> $default_cols,
				'filter_onchange' 	=> "this.form.submit();",
				'filter2_onchange' 	=> "this.form.submit();",
				'no_csv_export'		=> false,
				'csv_fields'		=> true,
				 'manual'         => false, // pas de manuel...
				//'manual'         => $do_email ? ' ' : false,	// space for the manual icon
			);
		}
		
		// Listes
		$sel_options = array(
			'filter' => array(''=>lang('All status')) + $this->spisession_bo->get_reg_status(),
			'filter2' => array('reg_contact'=>lang('Contact'), 'reg_id'=>lang('Registration'), 'reg_role'=>lang('Role'),'ses_crs'=>lang('Course')) ,
			'reg_status' => $this->spisession_bo->get_reg_status(),
			'reg_role' => array(''=>lang('All roles')) + $this->spisession_bo->get_role(),
			'ses_crs' => array(''=>lang('All courses')) +$this->spisession_bo->get_crs('','all'),
		);

	
		
		$content['nm']['filter2'] = $content['filter2'] ? $content['filter2'] : 'reg_id';
		$content['msg'] = $msg;

		// Simple utilisateur
		if ($GLOBALS['egw_info']['user']['SpiSessionLevel'] == 1){
			$content['nm']['hide_contact'] = true;
		}
		
		$tpl = new etemplate('spisession.registration.index');
		$content['nm']['header_left'] = 'spisession.registration.index.left';
		$content['nm']['header_right'] = 'spisession.registration.index.right';
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Registrations');
		$tpl->read('spisession.registration.index');
		$tpl->exec('spisession.registration_ui.index', $content, $sel_options, $readonlys, array('nm' => $content['nm']));
	}

}
?>