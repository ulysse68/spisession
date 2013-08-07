<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package spisession
 * @subpackage setup
 * @version $Id$
 */


$phpgw_baseline = array(
	'spisession_ref_ses_status' => array(
		'fd' => array(
			'status_id' => array('type' => 'auto','nullable' => False),
			'status_label' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'status_order' => array('type' => 'int','precision' => '4'),
			'status_color' => array('type' => 'varchar','precision' => '255'),
			'status_active' => array('type' => 'bool'),
			'status_creator' => array('type' => 'int','precision' => '4'),
			'status_created' => array('type' => 'int','precision' => '20'),
			'status_modifier' => array('type' => 'int','precision' => '4'),
			'status_modified' => array('type' => 'int','precision' => '20'),
			'status_group' => array('type' => 'int','precision' => '4')
		),
		'pk' => array('status_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_ses_status_transition' => array(
		'fd' => array(
			'status_source' => array('type' => 'int','precision' => '4','nullable' => False),
			'status_target' => array('type' => 'int','precision' => '4','nullable' => False)
		),
		'pk' => array(),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_reg_status' => array(
		'fd' => array(
			'status_id' => array('type' => 'auto','nullable' => False),
			'status_label' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'status_order' => array('type' => 'int','precision' => '4'),
			'status_color' => array('type' => 'varchar','precision' => '255'),
			'status_active' => array('type' => 'bool'),
			'status_creator' => array('type' => 'int','precision' => '4'),
			'status_created' => array('type' => 'int','precision' => '20'),
			'status_modifier' => array('type' => 'int','precision' => '4'),
			'status_modified' => array('type' => 'int','precision' => '20'),
			'status_group' => array('type' => 'int','precision' => '4')
		),
		'pk' => array('status_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_reg_status_transition' => array(
		'fd' => array(
			'status_source' => array('type' => 'int','precision' => '4','nullable' => False),
			'status_target' => array('type' => 'int','precision' => '4','nullable' => False)
		),
		'pk' => array(),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_crs_status' => array(
		'fd' => array(
			'status_id' => array('type' => 'auto','nullable' => False),
			'status_label' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'status_order' => array('type' => 'int','precision' => '4'),
			'status_color' => array('type' => 'varchar','precision' => '255'),
			'status_active' => array('type' => 'bool'),
			'status_creator' => array('type' => 'int','precision' => '4'),
			'status_created' => array('type' => 'int','precision' => '20'),
			'status_modifier' => array('type' => 'int','precision' => '4'),
			'status_modified' => array('type' => 'int','precision' => '20'),
			'status_group' => array('type' => 'int','precision' => '4')
		),
		'pk' => array('status_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_crs_status_transition' => array(
		'fd' => array(
			'status_source' => array('type' => 'int','precision' => '4','nullable' => False),
			'status_target' => array('type' => 'int','precision' => '4','nullable' => False)
		),
		'pk' => array(),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_date_status' => array(
		'fd' => array(
			'status_id' => array('type' => 'auto','nullable' => False),
			'status_label' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'status_order' => array('type' => 'int','precision' => '4'),
			'status_color' => array('type' => 'varchar','precision' => '255'),
			'status_active' => array('type' => 'bool'),
			'status_creator' => array('type' => 'int','precision' => '4'),
			'status_created' => array('type' => 'int','precision' => '20'),
			'status_modifier' => array('type' => 'int','precision' => '4'),
			'status_modified' => array('type' => 'int','precision' => '20'),
			'status_group' => array('type' => 'int','precision' => '4')
		),
		'pk' => array('status_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_date_status_transition' => array(
		'fd' => array(
			'status_source' => array('type' => 'int','precision' => '4','nullable' => False),
			'status_target' => array('type' => 'int','precision' => '4','nullable' => False)
		),
		'pk' => array(),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_role' => array(
		'fd' => array(
			'role_id' => array('type' => 'auto','nullable' => False),
			'role_label' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'role_description' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'role_order' => array('type' => 'int','precision' => '4'),
			'role_active' => array('type' => 'bool'),
			'role_creator' => array('type' => 'int','precision' => '4'),
			'role_created' => array('type' => 'int','precision' => '20'),
			'role_modifier' => array('type' => 'int','precision' => '4'),
			'role_modified' => array('type' => 'int','precision' => '20')
		),
		'pk' => array('role_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_field' => array(
		'fd' => array(
			'field_id' => array('type' => 'auto','nullable' => False),
			'field_label' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'field_description' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'field_order' => array('type' => 'int','precision' => '4'),
			'field_active' => array('type' => 'bool'),
			'field_creator' => array('type' => 'int','precision' => '4'),
			'field_created' => array('type' => 'int','precision' => '20'),
			'field_modifier' => array('type' => 'int','precision' => '4'),
			'field_modified' => array('type' => 'int','precision' => '20')
		),
		'pk' => array('field_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_graduation' => array(
		'fd' => array(
			'grad_id' => array('type' => 'auto','nullable' => False),
			'grad_label' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'grad_description' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'grad_order' => array('type' => 'int','precision' => '4'),
			'grad_active' => array('type' => 'bool'),
			'grad_creator' => array('type' => 'int','precision' => '4'),
			'grad_created' => array('type' => 'int','precision' => '20'),
			'grad_modifier' => array('type' => 'int','precision' => '4'),
			'grad_modified' => array('type' => 'int','precision' => '20')
		),
		'pk' => array('grad_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_ref_component' => array(
		'fd' => array(
			'comp_id' => array('type' => 'auto','nullable' => False),
			'comp_label' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'comp_description' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'comp_order' => array('type' => 'int','precision' => '4'),
			'comp_active' => array('type' => 'bool'),
			'comp_creator' => array('type' => 'int','precision' => '4'),
			'comp_created' => array('type' => 'int','precision' => '20'),
			'comp_modifier' => array('type' => 'int','precision' => '4'),
			'comp_modified' => array('type' => 'int','precision' => '20')
		),
		'pk' => array('comp_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_course' => array(
		'fd' => array(
			'crs_id' => array('type' => 'auto','nullable' => False),
			'crs_code' => array('type' => 'varchar','precision' => '64'),
			'crs_title' => array('type' => 'varchar','precision' => '255'),
			'crs_short_desc' => array('type' => 'text'),
			'crs_desc' => array('type' => 'text'),
			'crs_field' => array('type' => 'int','precision' => '4'),
			'crs_status' => array('type' => 'int','precision' => '4'),
			'crs_responsible' => array('type' => 'int','precision' => '4'),
			'crs_provider' => array('type' => 'int','precision' => '4'),
			'crs_duration' => array('type' => 'int','precision' => '4'),
			'crs_nb_hours' => array('type' => 'int','precision' => '4'),
			'crs_units' => array('type' => 'decimal','precision' => '10','scale' => '2'),
			'crs_grad' => array('type' => 'int','precision' => '4'),
			'crs_creator' => array('type' => 'int','precision' => '4'),
			'crs_created' => array('type' => 'int','precision' => '20'),
			'crs_modifier' => array('type' => 'int','precision' => '4'),
			'crs_modified' => array('type' => 'int','precision' => '20'),
			'crs_concerned' => array('type' => 'varchar','precision' => '255'),
			'crs_amount' => array('type' => 'decimal','precision' => '10','scale' => '2')
		),
		'pk' => array('crs_id'),
		'fk' => array('crs_field' => 'spisession_ref_field','crs_status' => 'spisession_ref_crs_status','crs_responsible' => 'egw_accounts','crs_provider' => 'spiclient','crs_grad' => 'spisession_ref_graduation'),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_crs_component' => array(
		'fd' => array(
			'comp_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'crs_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'crs_comp_required' => array('type' => 'bool')
		),
		'pk' => array('comp_id','crs_id'),
		'fk' => array('comp_id' => 'spisession_ref_component','crs_id' => 'spisession_course'),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_session' => array(
		'fd' => array(
			'ses_id' => array('type' => 'auto','precision' => '4','nullable' => False),
			'ses_crs' => array('type' => 'int','precision' => '4'),
			'ses_start_date' => array('type' => 'int','precision' => '20'),
			'ses_end_date' => array('type' => 'int','precision' => '20'),
			'ses_start_reg' => array('type' => 'int','precision' => '20'),
			'ses_end_reg' => array('type' => 'int','precision' => '20'),
			'ses_location' => array('type' => 'int','precision' => '4'),
			'ses_responsible' => array('type' => 'int','precision' => '4'),
			'ses_status' => array('type' => 'int','precision' => '4'),
			'ses_min_participant' => array('type' => 'int','precision' => '4'),
			'ses_max_participant' => array('type' => 'int','precision' => '4'),
			'ses_lang' => array('type' => 'varchar','precision' => '10'),
			'ses_desc' => array('type' => 'text'),
			'ses_creator' => array('type' => 'int','precision' => '4'),
			'ses_created' => array('type' => 'int','precision' => '20'),
			'ses_modifier' => array('type' => 'int','precision' => '4'),
			'ses_modified' => array('type' => 'int','precision' => '20'),
			'ses_cost' => array('type' => 'text'),
			'ses_client' => array('type' => 'int','precision' => '4'),
			'ses_provider' => array('type' => 'int','precision' => '4'),
			'ses_amount' => array('type' => 'decimal','precision' => '10','scale' => '2'),
			'ses_vat' => array('type' => 'int','precision' => '4'),
			'ses_conv_date' => array('type' => 'int','precision' => '20')
		),
		'pk' => array('ses_id'),
		'fk' => array('ses_crs' => 'spisession_course','ses_responsible' => 'egw_accounts','ses_status' => 'spisession_ref_ses_status','ses_client' => 'spiclient','ses_provider' => 'spiclient','ses_vat' => 'spireapi_vat'),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_session_date' => array(
		'fd' => array(
			'ses_date_id' => array('type' => 'auto','nullable' => False),
			'ses_date_ses' => array('type' => 'int','precision' => '4'),
			'ses_date_day' => array('type' => 'int','precision' => '20'),
			'ses_date_start' => array('type' => 'int','precision' => '4'),
			'ses_date_end' => array('type' => 'int','precision' => '4'),
			'ses_date_site' => array('type' => 'int','precision' => '4'),
			'ses_date_responsible' => array('type' => 'int','precision' => '4'),
			'ses_date_status' => array('type' => 'int','precision' => '4'),
			'ses_date_desc' => array('type' => 'text'),
			'ses_date_creator' => array('type' => 'int','precision' => '4'),
			'ses_date_created' => array('type' => 'int','precision' => '20'),
			'ses_date_modifier' => array('type' => 'int','precision' => '4'),
			'ses_date_modified' => array('type' => 'int','precision' => '20'),
			'ses_date_title' => array('type' => 'varchar','precision' => '255'),
			'ses_date_break' => array('type' => 'bool')
		),
		'pk' => array('ses_date_id'),
		'fk' => array('ses_date_ses' => 'spisession_session','ses_date_responsible' => 'egw_accounts','ses_date_status' => 'spisession_ref_date_status'),
		'ix' => array(),
		'uc' => array()
	),
	'spisession_registration' => array(
		'fd' => array(
			'reg_id' => array('type' => 'auto','nullable' => False),
			'reg_ses' => array('type' => 'int','precision' => '4'),
			'reg_contact' => array('type' => 'int','precision' => '4'),
			'reg_link' => array('type' => 'int','precision' => '4'),
			'reg_role' => array('type' => 'int','precision' => '4'),
			'reg_status' => array('type' => 'int','precision' => '4'),
			'reg_creator' => array('type' => 'int','precision' => '4'),
			'reg_created' => array('type' => 'int','precision' => '20'),
			'reg_modifier' => array('type' => 'int','precision' => '4'),
			'reg_modified' => array('type' => 'int','precision' => '20'),
			'reg_account' => array('type' => 'int','precision' => '4')
		),
		'pk' => array('reg_id'),
		'fk' => array('reg_ses' => 'spisession_session'),
		'ix' => array(),
		'uc' => array()
	)
);
