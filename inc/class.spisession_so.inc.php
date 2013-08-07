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

class spisession_so extends so_sql{
	
	var $spisession = 'spisession_session';
	var $spisession_ses_status = 'spisession_ref_ses_status';
	var $spisession_ses_status_transition = 'spisession_ref_ses_status_transition';
	var $spisession_crs = 'spisession_course';
	var $spisession_role = 'spisession_ref_role';
	var $spisession_reg = 'spisession_registration';
	var $spisession_reg_status = 'spisession_ref_reg_status';
	var $spireapi_site = 'spireapi_site';

	var $so_ses_status;
	var $so_ses_status_transition;
	var $so_crs;
	var $so_role;
	var $so_reg;
	var $so_reg_status;
	var $so_sites;
	
	var $account_id;
	var $obj_accounts;
	
	/**
	 * Constructeur 
	 *
	 */
	function spisession_so(){
		parent::so_sql('spisession',$this->spisession);
		
		$GLOBALS['egw_info']['user']['account_id']=$GLOBALS['egw_info']['user']['account_id'];
		$this->obj_accounts = CreateObject('phpgwapi.accounts',$GLOBALS['egw_info']['user']['account_id'],'u');

		$this->so_ses_status = new so_sql('spisession',$this->spisession_ses_status);
		$this->so_ses_status_transition = new so_sql('spisession',$this->spisession_ses_status_transition);
		$this->so_crs = new so_sql('spisession',$this->spisession_crs);
		$this->so_role = new so_sql('spisession',$this->spisession_role);
		$this->so_reg = new so_sql('spisession',$this->spisession_reg);
		$this->so_reg_status = new so_sql('spisession',$this->spisession_reg_status);
		
		$this->so_sites = new so_sql('spireapi',$this->spireapi_site);
		
		
		$this->date_ui = CreateObject('spisession.date_ui');
	}

	function add_update_session($info){
	/**
	 * Fonction permettant la mise à jour ou la creation d'une reference
	 *
	 * @param $info tableau contenant les valeurs
	 * @return string
	 */
		$msg='';
		if(is_array($info)){
			// Controle sur les dates
			if($info['ses_start_date'] > $info['ses_end_date']){
				return lang('Error while saving').' : '.lang('End date must be after the start date');
			}
			if($info['ses_start_reg'] > $info['ses_end_reg']){
				return lang('Error while saving').' : '.lang('End registration date must be after the start registration date');
			}

			// Max participants > Min Participants >= 0
			if($info['ses_min_participant'] < 0 || $info['ses_max_participant'] < 0){
				return lang('Error while saving').' : '.lang('Min and max participants must be superior or equal to 0');
			}
			if($info['ses_min_participant'] > $info['ses_max_participant']){
				return lang('Error while saving').' : '.lang('Max participant must be superior to min participant');
			}


			if(isset($info['ses_id'])){
				// Existant
				$this->history($info);
				$this->data = $info;
				$this->data['ses_modified'] = time();
				$this->data['ses_modifier'] = $GLOBALS['egw_info']['user']['account_id'];
				$this->update($this->data,true);
				
				$msg .= ' '.lang('Session updated');
			}else{
				// Nouveau
				$this->data = $info;
				$this->data['ses_id'] = '';
				$this->data['ses_created'] = time();
				$this->data['ses_creator'] = $GLOBALS['egw_info']['user']['account_id'];
				$this->save();
				
				$msg .= ' '.lang('Session created');
			}
		}
		return $msg;
	}

	function history($content){
	/**
	 * Fonction permettant l'historisation des valeurs (lors de la mise a jour d'une reference)
	 *
	 * @param $content : info concernant la référence (contient les infos avec les nouvelles valeurs)
	 */
		// Valeur actuel du contrat
		$id = $content['ses_id'];
		$old = $this->read($id);

		// Nouvelles valeurs
		$history = array_diff_assoc($content,$old);
		$infoHistory = $history['history'];

		$FieldIgnore = array('link_to','user_timezone_read','history','msg','registration','ses_desc','date|registration|description|cost|link|history','button','ses_modified','ses_modifier','date','hideadd','mode','crs_link','hide_spiclient');
		$FieldDate = array('ses_start_date','ses_end_date','ses_start_reg','ses_end_reg');
		$FieldExternal = array(
			'ses_status' => array('table' => $this->so_ses_status,'field' => 'status_label'),
			'ses_crs' => array('table' => $this->so_crs,'field' => 'crs_title'),
		);
		$FieldUser = array('ses_responsible');
		$FieldText = array('');
		
		$historylog = CreateObject('phpgwapi.historylog','spisession');


		// Historisation des field
		foreach((array)$history as $field => $value){
			if(!in_array($field,$FieldIgnore)){				
				// test afin de savoir si on est sur une valeur qui etait null (mais qui apparait avec la valeur 0) cas des listes
				if(!($value == null && $old[$field] == '0')){
					if(in_array($field, $FieldDate)){
						$historylog->add(lang($field),$id,date('d/m/Y',$value),date('d/m/Y',$old[$field]));
					}else{
						if(array_key_exists($field,$FieldExternal)){
							$new_value = $FieldExternal[$field]['table']->read($value);
							$old_value = $FieldExternal[$field]['table']->read($old[$field]);
							$historylog->add(lang($field),$id,$new_value[$FieldExternal[$field]['field']],$old_value[$FieldExternal[$field]['field']]);
						}else{
							if(in_array($field,$FieldUser)){
								$new_contact = $GLOBALS['egw']->accounts->read($value);
								$old_contact = $GLOBALS['egw']->accounts->read($old[$field]);
								
								$new_name = $new_contact['account_firstname'].' '.$new_contact['account_lastname'];
								$old_name = $old_contact['account_firstname'].' '.$old_contact['account_lastname'];
								$historylog->add(lang($field),$id,$new_name,$old_name);
							}else{
								if(in_array($field,$FieldText)){
									$value = $this->truncate($value);
									$old[$field] = $this->truncate($old[$field]);
								}
								$historylog->add(lang($field),$id,$value,$old[$field]);
							}
						}
					}
				}
			}
		}
	}

	
	function is_manager(){
	/**
	 * Vérifie si l'utilisateur est manager ou non
	 *
	 * @return boolean
	 */
		$groupeUser = array_keys($GLOBALS['egw']->accounts->memberships($GLOBALS['egw_info']['user']['account_id']));
		
		$config = CreateObject('phpgwapi.config');
		$obj_config = $config->read('spisession');
		
		if($GLOBALS['egw_info']['user']['apps']['admin'] || in_array($obj_config['ManagementGroup'],$groupeUser)){
			return true;
		}else{
			return false;
		}
	}
	
	function construct_search($search){
	/**
	 * Crée une recherche. Le tableau de retour contiendra toutes les colonnes de la table en cours, en leur faisant correspondre la valeur $search 
	 *
	 * La requête ainsi crée est prète à être utilisée comme filtre
	 *
	 * @param int $search tableau des critères de recherche
	 * @return array
	 */
		$tab_search=array();
		foreach((array)$this->db_data_cols as $id=>$value){
			$tab_search[$id]=$search;
		}

		return $tab_search;
	}
	
	function set_readonlys(){
	/**
	 * Genere la liste des informations a mettre en readonly
	 */
		foreach((array)$this->db_data_cols as $key => $value){
			$retour[$key] = true;
		}
		return $retour;
	}

	function notify($reg_data){
	/**
	 * Fonction de création du contenu du mail pour les notifications (appelé lors de l'inscription ou du changement de statut d'inscription)
	 *
	 * @param $reg_data : données de l'inscription
	 */
		
		// Récupération des valeurs des référentiels
		$ses = $this->read($reg_data['reg_ses']);
		$crs = $this->so_crs->read($ses['ses_crs']);
		$site = $this->so_sites->read($ses['ses_location']);
		$reg_status = $this->so_reg_status->read($reg_data['reg_status']);

		// Récupération des mots clés de la langue de la session
		$words = solangfile::load_app('spisession',$ses['ses_lang']);
		if(empty($words)) $words = solangfile::load_app('spisession','en');

		// Récupération des informations sur le contacts
		if(!empty($reg_data['reg_contact']))
			$contact = $GLOBALS['egw']->contacts->read($reg_data['reg_contact']);

		if(!empty($reg_data['reg_account']))
			$account = $GLOBALS['egw']->accounts->read($reg_data['reg_account']);

		// Sujet		
		$mail['subject'] = '[#'.$reg_data['reg_ses'].' - '.$crs['crs_title'].'] ';
		switch ($reg_data['reg_status']) {
			case $this->obj_config['validated_reg_status']:
				$mail['subject'] .= $words['your inscription is confirmed']['content'];
				break;
			case $this->obj_config['rejected_reg_status']:
				$mail['subject'] .= $words['your inscription is canceled/refused']['content'];
				break;
			case $this->obj_config['pending_reg_status']:
				$mail['subject'] .= $words['your inscription is pending']['content'];
				break;
		}

		// Contenu mail
		if(!empty($reg_data['reg_contact']))
			$mail['message'] = $words['hello']['content'].' '.$contact['n_fn'];

		if(!empty($reg_data['reg_account']))
			$mail['message'] = $words['hello']['content'].' '.$account['account_lastname'].' '.$account['account_firstname']."<br />";
		
		$mail['message'] .= $words['this is an automatic email to inform you']['content'].' : '."<br /><br />";
		
		$mail['message'] .= $words['course']['content'].' : '.$crs['crs_title']."<br />";
		$mail['message'] .= $words['site']['content'].' : '.$site['site_label']."<br />";
		$mail['message'] .= $words['start date']['content'].' : '.date('d/m/Y',$ses['ses_start_date'])."<br />";
		$mail['message'] .= $words['end date']['content'].' : '.date('d/m/Y',$ses['ses_end_date'])."<br /><br />";
		$mail['message'] .= $words['your registration status']['content'].' : '.$words[$reg_status['status_label']]['content']."<br /><br />";

		$mail['message'] .= $words['to consult dates and further information, please connect to']['content'].' : '."<br />".$GLOBALS['egw_info']['server']['webserver_url'].'/index.php?menuaction=spisession.spisession_ui.edit&id='.$ses['ses_id']."<br /><br />";

		$mail['message'] .= $words['regards']['content'].', '."<br /><br />".'--'.$words['message sent from spisession, copied to the responsible person for this session']['content'];
		
		// Emetteur
		$mail['sendby'] = $GLOBALS['egw_info']['user']['account_email'];
		
		// Notification au participant concerné
		if (!$this->obj_config['no_email_to_participants']){
			if(!empty($reg_data['reg_contact'])){
				if (strpos($contact['email'],'@') > 1) {
					$mail['sendto'][$contact['email']] = $contact['email'];
				}elseif (strpos($contact['email_home'],'@') > 1) {
					$mail['sendto'][$contact['email_home']] = $contact['email_home'];
				}
			}

			if(!empty($reg_data['reg_account'])){
				if (strpos($account['account_email'],'@') > 1) {
					$mail['sendto'][$account['account_email']] = $account['account_email'];
				}
			}
		}

		// Notification au responsable du dossier
		if(!$this->obj_config['no_email_to_responsibles']){
			$account = $GLOBALS['egw']->accounts->read($ses['ses_responsible']);
			$mail['sendcc'][$account['account_email']] = $account['account_email'];
		}

		// Destinataires (sous la forme xx@xx.xx, yy@yy.yy ...)
		$mail['sendto'] = implode(', ',$mail['sendto']);
		$mail['sendcc'] = implode(', ',$mail['sendcc']);

		// _debug_array('--- function notify : info envoi');
		// _debug_array($mail);

		$this->send_mail($mail);

		if(empty($mail['sendto'])){
			if(!empty($reg_data['reg_contact']))
				$msg = lang('Unable to send notification (no mail for this contact)');

			if(!empty($reg_data['reg_account']))
				$msg = lang('Unable to send notification (no mail for this account)');
		}else{
			$msg = lang('Notification sent to ').$mail['sendto'];
		}
		return $msg;
		
	}

	function send_mail($content){
	/**
	 * Fonction d'envoi de mail
	 *
	 * @param $content array : information sur le mail a envoyer (message / sendto / sendby / sendcc)
	 * @return string
	 */
		$content['message'] = htmlentities($content['message'], ENT_NOQUOTES, "UTF-8");
		$content['message'] = htmlspecialchars_decode($content['message']);

		$to = $content['sendto'];
		$subject = 	$content['subject'];
		$bound_text = 	"spirea";
		$bound = 	"--".$bound_text."\n";
		
		$bound_last = 	"--".$bound_text."--\n"; 
		$headers = 	"From: ".$content['sendby']."\n";
		
		if(!empty($content['sendcc'])){
			$headers .= "Cc: ".$content['sendcc']."\n";
		}

		if($content['notification']){
			$headers .='Disposition-Notification-To: '.$content['sendby']."\n";
			$headers .='Return-Receipt-To: '.$content['sendby']."\n";
		}

		$headers .= "MIME-Version: 1.0\n"
			."Content-Type: multipart/mixed; boundary=\"$bound_text\"\n";
		 
		$message .= 	"If you can see this MIME than your client doesn't accept MIME types!\n"
			.$bound;
		
		$message .= 	"Content-Type: text/html; charset=\"ISO-8859-1\"\n"
			."Content-Transfer-Encoding: 8bit\n\n"
			.$content['message']."\n"
			.$value
			.$bound;
		
		if(mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $message, $headers)){
			$msg = lang('Notification sent successfull');
		}else{
			$msg = lang('Notification failed')."\n (class.spisession_so.inc.php / send_mail) \nHere is the message : \n".$message ;
		}
		
		return $msg;
	}
}
?>