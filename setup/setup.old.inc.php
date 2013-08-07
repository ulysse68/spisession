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

$setup_info['spisession']['name'] = 'spisession';
$setup_info['spisession']['title'] = 'Integrated Sessions Management Modules for eGroupware (trainings, meetings, etc.)';
$setup_info['spisession']['version'] = '1.002';
$setup_info['spisession']['app_order'] = 0;
$setup_info['spisession']['tables'] = array('spisession_ref_ses_status','spisession_ref_ses_status_transition','spisession_ref_reg_status','spisession_ref_reg_status_transition','spisession_ref_crs_status','spisession_ref_crs_status_transition','spisession_ref_date_status','spisession_ref_date_status_transition','spisession_ref_role','spisession_ref_field','spisession_ref_graduation','spisession_ref_component','spisession_course','spisession_crs_component','spisession_session','spisession_session_date','spisession_registration');
$setup_info['spisession']['enable'] = 1;

$setup_info['spisession']['author'][] = array(
	'name'  => 'Spirea',
	'email' => 'contact@spirea.fr',
	'url'	=> 'http://www.spirea.fr',
);

$setup_info['spisession']['maintainer'][] = array(
	'name'  => 'Spirea',
	'email' => 'contact@spirea.fr',
	'url'   => 'http://www.spirea.fr'
);

$setup_info['spisession']['license'] = 'Copyright 2012 - Spirea';
$setup_info['spisession']['description'] = 'Integrated Sessions Management Modules for eGroupware (trainings, meetings, etc.)';

$setup_info['spisession']['depends'][] = array(
	'appname' => 'phpgwapi',
	'versions' => array('1.8')
);
$setup_info['spisession']['depends'][] = array(
	'appname' => 'etemplate',
	'versions' => array('1.8')
);

/* The hooks this app includes, needed for hooks registration */
/* note spirea : doit être nickel : pas de ligne vide, vérifier les applications et chemins */
$setup_info['spisession']['hooks']['preferences'] = 'spisession_hooks::all_hooks';  // affiche les liens dans le menu des préférences
$setup_info['spisession']['hooks']['settings'] = 'spisession_hooks::settings';  // affiche les liens dans le menu des préférences
$setup_info['spisession']['hooks']['admin'] = 'spisession_hooks::all_hooks'; // affiche les liens dans le menu d'administration
$setup_info['spisession']['hooks']['spisession menu'] = 'spisession_hooks::all_hooks'; // affiche les liens dans le menu spiclient menu
$setup_info['spisession']['hooks']['sidebox_menu'] = 'spisession_hooks::all_hooks'; // affiche le menu sur la gauche de l'appli
$setup_info['spisession']['hooks']['search_link'] = 'spisession_hooks::search_link'; // note : il y avait une faute de frappe !
$setup_info['spisession']['hooks']['home'] = 'spisession_hooks::home'; //Permet d'afficher un hook sur la page d'accueil











































