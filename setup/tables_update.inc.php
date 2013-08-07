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


function spisession_upgrade1_000()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('spisession_ref_ses_status','status_group',array(
		'type' => 'int',
		'precision' => '4'
	));

	$GLOBALS['egw_setup']->oProc->AddColumn('spisession_ref_reg_status','status_group',array(
		'type' => 'int',
		'precision' => '4'
	));

	$GLOBALS['egw_setup']->oProc->AddColumn('spisession_ref_crs_status','status_group',array(
		'type' => 'int',
		'precision' => '4'
	));

	$GLOBALS['egw_setup']->oProc->AddColumn('spisession_ref_date_status','status_group',array(
		'type' => 'int',
		'precision' => '4'
	));

	return $GLOBALS['setup_info']['spisession']['currentver'] = '1.001';
}

function spisession_upgrade1_001()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('spisession_registration','reg_account',array(
		'type' => 'int',
		'precision' => '4'
	));

	return $GLOBALS['setup_info']['spisession']['currentver'] = '1.002';
}


function spisession_upgrade1_002()
{
	$GLOBALS['egw_setup']->oProc->AlterColumn('spisession_registration','reg_contact',array(
		'type' => 'varchar',
		'precision' => '255'
	));

	return $GLOBALS['setup_info']['spisession']['currentver'] = '1.003';
}

