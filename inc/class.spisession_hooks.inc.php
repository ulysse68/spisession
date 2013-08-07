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

require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.acl_so.inc.php');
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.acl_spisession.inc.php');	
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.spisession_so.inc.php');


class spisession_hooks  extends acl_so{

	static function search_link($location)
	{
	/**
	* Méthode initialisant les variables globales des tickets, et les paramètres d'affichage de l'utilisateur
	*
	* NOTE : $location ne sert à rien
	* 
	* @param int $location paramètres locaux à charger
	* @return array
	*/
		$appname = 'spisession';
		/* Récupération des droits d'accès ACL */
		$acl = CreateObject($appname.'.acl_'.$appname);
		
		return array(
			'query' => 'spisession.spisession_bo.link_query',
			'title' => 'spisession.spisession_bo.link_title',
			'titles' => 'spisession.spisession_bo.link_titles',
			'view'  => array(
				'menuaction' => 'spisession.spisession_ui.edit',
			),
			'view_id' => 'id',
			'view_popup'  => '930x700',
			'add' => array(
				'menuaction' => 'spisession.spisession_ui.edit',
			),
			'add_app'    => 'link_app',
			'add_id'     => 'link_id',
			'add_popup'  => '930x700',
		);
	}

	static function all_hooks($args){
	/**
	* Méthode initialisant les variables globales des tickets et chargeant les préférences paramétrées.
	* Permet aussi d'afficher le menu et de créer des liens dirigés vers son contenu
	*
	* \version 
	*
	* @param array $args tableau contenant l'index location définissant l'endroit où l'utilisateur se trouve : spisession menu,spisession,admin,... (on en déduit ainsi les paramètres à afficher)
	*/
		$appname = 'spisession';
		$location = is_array($args) ? $args['location'] : $args;
		
		/* Récupération de la config */
		$config = CreateObject('phpgwapi.config');
		$obj_config = $config->read('spisession');
		
		acl_so::get_spisession_level();
		
		/*********************/

		/**** Cours ****/
		if ($GLOBALS['egw_info']['user']['apps']['spisession'] && $location != 'admin' && $location != 'preferences'){
			$file = array();
			
			if(spisession_so::is_manager()){
				$file[]=array(
					'text' => '<a class="textSidebox" href="'.$GLOBALS['egw']->link('/index.php',array('menuaction' => 'spisession.course_ui.edit')).
					'" onclick="window.open(this.href,\'_blank\',\'dependent=yes,width=990,height=600,scrollbars=yes,status=yes\');
					return false;">'.lang('New course').'</a>',
					'no_lang' => true,
					'link' => false,
				);
			}
			
			
			$file['Course Catalog']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.course_ui.index&view=all');

			if ($location == 'Course Catalog'){
				display_section($appname,$file);
			}else{
				display_sidebox($appname,lang('Course Catalog'),$file);
			}
		}
		
		/**** Sessions ****/
		if ($GLOBALS['egw_info']['user']['apps']['spisession'] && $location != 'admin' && $location != 'preferences'){
			$file = array();
			
			if(spisession_so::is_manager()){
				$file[]=array(
					'text' => '<a class="textSidebox" href="'.$GLOBALS['egw']->link('/index.php',array('menuaction' => 'spisession.spisession_ui.edit')).
					'" onclick="window.open(this.href,\'_blank\',\'dependent=yes,width=990,height=600,scrollbars=yes,status=yes\');
					return false;">'.lang('New session').'</a>',
					'no_lang' => true,
					'link' => false,
				);
			}
			
			$file['My sessions']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.spisession_ui.index&view=own');
			$file['Pending sessions']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.spisession_ui.index&view=pending');
			$file['Archived sessions']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.spisession_ui.index&view=archived');
			$file['All sessions']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.spisession_ui.index&view=all');

			$file['Advanced search']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.spisession_ui.index&adv_search='.true);

			if ($location == 'sessions'){
				display_section($appname,$file);
			}else{
				display_sidebox($appname,lang('Sessions'),$file);
			}
		}


		/**** Dates de sessions ****/
		if ($GLOBALS['egw_info']['user']['apps']['spisession'] && $location != 'admin'){
			$file = array();
			
			if ($GLOBALS['egw_info']['user']['SpiSessionLevel'] > 50){
				$file[]=array(
						'text' => '<a class="textSidebox" href="'.$GLOBALS['egw']->link('/index.php',array('menuaction' => 'spisession.date_ui.edit')).
						'" onclick="window.open(this.href,\'_blank\',\'dependent=yes,width=990,height=600,scrollbars=yes,status=yes\');
						return false;">'.lang('New session date').'</a>',
						'no_lang' => true,
						'link' => false,
					);
			};

			$file['Session dates']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.date_ui.index');

			if ($location == 'registration'){
				display_section($appname,$file);
			}else{
				display_sidebox($appname,lang('Session date'),$file);
			}
		}
		
		/**** Stats ****/
		if ($GLOBALS['egw_info']['user']['apps']['spisession'] && $location != 'admin' && $location != 'statistics'){
			$file = array();
			$file['Registrations']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.registration_ui.index');
			
			if ($location == 'statistics'){
				display_section($appname,$file);
			}else{
				display_sidebox($appname,lang('Statistics'),$file);
			}
		}

		/**** Référentiels ****/
		if ($GLOBALS['egw_info']['user']['SpiSessionLevel'] >= 50 && $location != 'admin' && $location != 'referentiel'){
			$file = array();
			
			$file['Subjects']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.field_ui.index');
			$file['Course components']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.component_ui.index');
			$file['Course graduation']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.graduation_ui.index');

			$file['Course status']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.crs_status_ui.index');
			$file['Session status']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.ses_status_ui.index');
			$file['Session date status']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.date_status_ui.index');
			$file['Registration status']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.reg_status_ui.index');
			
			$file['Role']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.role_ui.index');
			
			
			if ($location == 'repository'){
				display_section($appname,$file);
			}else{
				display_sidebox($appname,lang('Repository'),$file);
			}
		}

		/**** Admin ****/
		if ($GLOBALS['egw_info']['user']['apps']['admin'] && $location != 'preferences' && $location != 'admin'){
			$file = array();
			
			$file['General']=$GLOBALS['egw']->link('/index.php','menuaction=spisession.admin_ui.index');

			if ($location == 'admin'){
				display_section($appname,$file);
			}else{
				display_sidebox($appname,lang('Admin'),$file);
			}
		}

		/**** About ****/
		if ($location != 'admin' && $location != 'preferences' && $location != 'spisession'){
			$file = array();
			$file[lang('About').' '.lang('spisession')]=$GLOBALS['egw']->link('/index.php','menuaction=spisession.spisession_ui.about');
			// $file[lang('User Manual')]=$GLOBALS['egw']->link('/spisession/about/Manuel_spisession_fr.pdf');
			$file[lang('User Manual')]=$GLOBALS['egw']->link('http://www.spirea.fr/fileadmin/Documentation/spisession/about/Manuel_spisession_fr.pdf');
			$file[lang('User Manual')]='http://www.spirea.fr/fileadmin/Documentations/spisession/SPI_EGW_USER_APP_SPISESSION_DOC_EN.pdf';
			// $file[lang('License').' spisession']=$GLOBALS['egw']->link('/spisession/about/Licence_spisession_fr.pdf');
			display_sidebox($appname,lang('About').' '.lang('spisession'),$file);
		}
		
	}
	
	static function home(){
	/**
	 * Crée l'écran d'accueil avec les paramètres par défaut
	 * Cette partie n'est pas codée - pas besoin d'affiche home...
	 */
		if($GLOBALS['egw_info']['user']['preferences']['spisession']['mainscreen_show_spisession'])
		{
			$content =& ExecMethod('spisession.spisession_ui.home');
			$title="Spisession";
			$portalbox =& CreateObject('phpgwapi.listbox',array(
				'title'	=> $title,
				'primary'	=> $GLOBALS['egw_info']['theme']['navbar_bg'],
				'secondary'	=> $GLOBALS['egw_info']['theme']['navbar_bg'],
				'tertiary'	=> $GLOBALS['egw_info']['theme']['navbar_bg'],
				'width'	=> '100%',
				'outerborderwidth'	=> '0',
				'header_background_image'	=> $GLOBALS['egw']->common->image('phpgwapi/templates/default','bg_filler')
			));
			$GLOBALS['egw_info']['flags']['app_header'] = $save_app_header;
			unset($save_app_header);

			$GLOBALS['portal_order'][] = $app_id = $GLOBALS['egw']->applications->name2id('spisession');
			foreach(array('up','down','close','question','edit') as $key)
			{
				$portalbox->set_controls($key,Array('url' => '/set_box.php', 'app' => $app_id));
			}
			$portalbox->data = Array();
			echo '<!-- BEGIN spisession info -->'."\n".$portalbox->draw($content)."\n".'<!-- END spisession info -->'."\n";
		}
		else
		{
			echo '<!-- BEGIN spisession info -->'."\nPARTIE A CODER..\n".'<!-- END spisession info -->'."\n";
		}
	}
	
	function get_spisession_level(){
	/**
	 * Constructeur 
	 *
	 */
		$config = CreateObject('phpgwapi.config');
		$this->obj_config = $config->read('spisession');
				
		$managers = array();
				// Récupération des groupes de l'utilisateur
		$groupeUser = array_keys($GLOBALS['egw']->accounts->memberships($GLOBALS['egw_info']['user']['account_id']));

		if($GLOBALS['egw_info']['user']['apps']['admin']){
			// Admin
			$GLOBALS['egw_info']['user']['SpiSessionLevel'] = 99;
		}elseif(in_array($this->obj_config['ManagementGroup'],$groupeUser)){
			// Groupe de gestion manager
			$GLOBALS['egw_info']['user']['SpiSessionLevel'] = 59;
		}elseif(in_array($GLOBALS['egw_info']['user']['account_id'],$managers)){
			// Manager
			$GLOBALS['egw_info']['user']['SpiSessionLevel'] = 19;
		}else{
			// Utilisateur
			$GLOBALS['egw_info']['user']['SpiSessionLevel'] = 1;
		}
		return $GLOBALS['egw_info']['user']['SpiSessionLevel'];
	}
}
?>
