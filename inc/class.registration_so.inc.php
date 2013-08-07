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
require_once(EGW_INCLUDE_ROOT. '/spisession/inc/class.spisession_bo.inc.php');	

class registration_so{
	
	var $spisession_registration = 'spisession_registration';
	var $spisession_bo;
	
	var $registration_so;
	
	var $reg_so;
	var $ses_so_transition_status;
	
	/**
	 * Constructeur 
	 *
	 */
	function registration_so(){
		$this->reg_so = new so_sql('spisession',$this->spisession_registration);
		
		$this->spisession_bo = new spisession_bo();
	}
	
	function construct_search($search){
	/**
	 * Crée une recherche. Le tableau de retour contiendra toutes les colonnes de la table en cours, en leur faisant correspondre la valeur $search 
	 *
	 * La requête ainsi crée est prête à être utilisée comme filtre
	 *
	 * @param int $search tableau des critères de recherche
	 * @return array
	 */
		$tab_search=array();
		foreach((array)$this->reg_so->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}
		return $tab_search;
	}
}
?>