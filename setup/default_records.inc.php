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

$sql_component="INSERT INTO `spisession_ref_component` 
(`comp_id`, `comp_label`, `comp_description`, `comp_order`, `comp_active`, `comp_creator`) VALUES
(1, 'Lecture', 'Lecture', 1, 1, -1),
(2, 'Exam', 'Exam', 2, 1, -1),
(3, 'Recitation', 'Recitation or presentation', 3, 1, -1),
(4, 'Group brainstorming', 'Group brainstorming session', 4, 1, -1),
(5, 'Independent Study', 'Independent Study', 5, 1, -1),
(6, 'Internship', 'Internship', 6, 1, -1),
(7, 'Laboratory', 'Laboratory', 7, 1, -1),
(8, 'Other', 'Other component', 8, 1, -1);
	";
$sql_ref_crs_status="INSERT INTO `spisession_ref_crs_status` 
(`status_id`, `status_label`, `status_order`, `status_color`, `status_active`, `status_creator`) VALUES
(1, 'New', 2, '#990000', 1, 5),
(2, 'Validated', 1, '#CC6600', 1, 5),
(3, 'Archived', 3, '#339999', 1, 5);";

$sql_ref_crs_status_transition="INSERT INTO `spisession_ref_crs_status_transition` 
(`status_source`, `status_target`) VALUES
(1, 2),
(2, 3);";

$sql_ref_date_status="INSERT INTO `spisession_ref_date_status` 
(`status_id`, `status_label`, `status_order`, `status_color`, `status_active`, `status_creator`) VALUES
(1, 'Optional', 1, '#3399FF', 1, -1),
(2, 'Confirmed', 1, '#3399FF', 1, -1),
(3, 'Cancelled', 2, '#6666CC', 1, -1);";

$sql_ref_date_status_transition="INSERT INTO `spisession_ref_date_status_transition` 
(`status_source`, `status_target`) VALUES
(1, 2),
(1, 3),
(2, 3);";

$sql_ref_graduation="INSERT INTO `spisession_ref_graduation` 
(`grad_id`, `grad_label`, `grad_description`, `grad_order`, `grad_active`, `grad_creator`) VALUES
(1, 'All levels', 'All levels', 1, 1, -1),
(2, 'Beginner', 'Beginner', 2, 1, -1),
(3, 'Intermediate', '', 3, 1, -1),
(4, 'Advanced', '', 4, 1, -1),
(5, 'Expert', '', 5, 1, -1),
(6, 'Postdoctorate', 'Postdoctorate', 6, 1, -1),
(7, 'Postgraduate', '', 7, 1, -1),
(8, 'All with conditions', '', 8, 1, -1);";

$sql_ref_reg_status="INSERT INTO `spisession_ref_reg_status` 
(`status_id`, `status_label`, `status_order`, `status_color`, `status_active`, `status_creator`) VALUES
(1, 'Registration asked', 1, '#0099CC', 1, -1),
(2, 'Registration confirmed', 2, '#00FF33', 1, -1),
(3, 'Registration under process', 3, '#00FF33', 1, -1),
(4, 'Refused', 4, '#CC3300', 1, -1),
(5, 'Pending registration', 5, '#999999', 1, -1);";

$sql_ref_reg_status_transition="INSERT INTO `spisession_ref_reg_status_transition` (`status_source`, `status_target`) VALUES
(1, 2),
(1, 4),
(2, 4),
(5, 1),
(5, 4);";

$sql_ref_role="INSERT INTO `spisession_ref_role` 
(`role_id`, `role_label`, `role_description`, `role_order`, `role_active`, `role_creator`) VALUES
(1, 'Teacher', 'Teacher', 10, 1, -1),
(2, 'Instructor', 'Instructor', 20, 1, -1),
(3, 'Participant', 'Participant / Trainee', 30, 1, -1),
(4, 'Organizator', 'Organizator', 40, 1, -1),
(5, 'Speaker', 'Speaker', 50, 1, -1);";


$sql_ref_ses_status="INSERT INTO `spisession_ref_ses_status` 
(`status_id`, `status_label`, `status_order`, `status_color`, `status_active`, `status_creator`) VALUES
(1, 'New', 1, '#339999', 1, -1),
(2, 'Valid', 2, '#CC6600', 1, -1),
(3, 'Archived', 3, '#339999', 1, -1);";

$sql__ref_ses_status_transition="INSERT INTO `spisession_ref_ses_status_transition` (`status_source`, `status_target`) VALUES
(1, 2),
(2, 3);";


$oProc->query ($sql_component);
$oProc->query ($sql_ref_crs_status);
$oProc->query ($sql_ref_crs_status_transition);
$oProc->query ($sql_ref_date_status);
$oProc->query ($sql_ref_date_status_transition);
$oProc->query ($sql_ref_graduation);
$oProc->query ($sql_ref_reg_status);
$oProc->query ($sql_ref_reg_status_transition);
$oProc->query ($sql_ref_role);
$oProc->query ($sql_ref_ses_status);
$oProc->query ($sql__ref_ses_status_transition);

$oProc->query ("INSERT INTO {$GLOBALS['egw_setup']->config_table} (config_app, config_name, config_value) VALUES 
('spisession', 'archived_crs_status', '3'),
('spisession', 'archived_crs_status', '3'),
('spisession', 'archived_ses_status', '3'),
('spisession', 'canceled_date_status', '3'),
('spisession', 'default_crs_status', '1'),
('spisession', 'default_date_status', '1'),
('spisession', 'default_reg_status', '1'),
('spisession', 'default_ses_lang', 'fr'),
('spisession', 'default_ses_status', '1'),
('spisession', 'ManagementGroup', '-2'),
('spisession', 'pending_crs_status', '1,2'),
('spisession', 'pending_reg_status', '5'),
('spisession', 'pending_ses_status', '1,2'),
('spisession', 'rejected_reg_status', '4'),
('spisession', 'student_role', '3'),
('spisession', 'unvalidated_reg_status', '1'),
('spisession', 'validated_crs_status', '2'),
('spisession', 'validated_date_status', '2'),
('spisession', 'validated_reg_status', '2');");


$admingroup = $GLOBALS['egw_setup']->add_account('Admins','Admin','Group',False,False);
$GLOBALS['egw_setup']->add_acl('spisession','run',$admingroup);

?>
