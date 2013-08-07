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
require_once(EGW_INCLUDE_ROOT. '/spisession/fpdf/fpdf.php');
require_once(EGW_INCLUDE_ROOT. '/spireapi/inc/class.trans_so.inc.php');


class generate_pdf_session extends fpdf{

	var $colonnes;
	var $format;
	var $type;
	
	var $timelogs;
	
	var $config;
	
	function generate_pdf($ses_id,$list = false){
	/**
	* Constructeur 
	*/
		
		self::__construct($ses_id,$list = false);
	}
	
	function __construct($ses_id,$list = false){
	/**
	* Méthode appelée directement par le Constructeur . Charge les varibles globales. $emetteur est un tableau décrivant la socité émettrice
	* $client esu une tableau décrivant la société cliente
	*
	* @param $ses_id : identifiant d'une session ou tableau contenant les sessions
	* @param $list : impression en liste (o/n)
	*/
		// Initialisation la config
		$config = CreateObject('phpgwapi.config');
		$this->config = $config->read('spisession');
		$this->type = '';
		
		$so_ses = new so_sql('spisession','spisession_session');
		// Récupération du ou des sessions
		if(is_array($ses_id)){
			$this->sessions = $ses_id;
		}else{
			$this->sessions[0] = $so_ses->read($ses_id);
		}
		
		define('FPDF_FONTPATH',EGW_INCLUDE_ROOT.'/spisession/fpdf/font/');

		// Impression en liste => mode paysage
		if($list == 'list'){
			parent::FPDF('L', 'mm', 'A4');
		}else{
			parent::FPDF('P', 'mm', 'A4');
		}
	}
	
	function sizeOfText($texte,$largeur){
	/**
	* Retourne le nombre de lignes de $texte divisé par la largeur $largeur (en comptant les retours à la ligne)
	*
	* NOTE : avec un explode et  quelques lignes, on peut faire facilement mieux
	*
	* @param string $texte texte dont on doit compte le nombre de lignes
	* @param int $largeur 
	* @return int 
	*/
		$index = 0;
		$nb_lines = 0;
		$loop = TRUE;
		while($loop){
			$pos = strpos($texte, "\n");
			if(!$pos){
				$loop  = FALSE;
				$ligne = $texte;
			}else{
				$ligne = substr( $texte, $index, $pos);
				$texte = substr( $texte, $pos+1 );
			}
			$length = floor($this->GetStringWidth($ligne));
			$res = 1 + floor($length / $largeur);
			$nb_lines += $res;
		}
		return $nb_lines;
	}

	function truncate($string, $limit=28, $break="-", $pad="...") { 
	/** 
	 * Découpe les $limit premier caractere d'une chaine $string
	 */
		if(strlen($string) <= $limit) return $string; 
		
		
		$string = substr($string, 0, $limit) . $pad; 

		return $string; 
	}
	
	function Header(){
	/**
	* Construit l'entete de la page PDF
	*/
		if($this->type == 'list'){
		// Vue liste
			$this->SetLineWidth(.3);		
			$this->fill = false;
			$this->SetFont('Arial','B',9);
			$this->SetFillColor(192,192,192);
			$this->SetXY(5,5);

			// Entete + Date de creation
			$this->Cell(0,6,utf8_decode(lang('Session\'s list')),0,0,'C');
			$this->Cell(0,6,utf8_decode(date('d/m/Y')),0,1,'R');

			// Entete des tableaux
			$this->SetX(5);
			$this->Cell(46,8,utf8_decode(lang('Course')),1,0,'C',1);
			$this->Cell(25,8,utf8_decode(lang('Status')),1,0,'C',1);
			$this->Cell(23,8,utf8_decode(lang('Start date')),1,0,'C',1);
			$this->Cell(20,8,utf8_decode(lang('End date')),1,0,'C',1);
			$this->Cell(45,8,utf8_decode(lang('Responsible')),1,0,'C',1);
			$this->Cell(39,8,utf8_decode(lang('Location')),1,0,'C',1);
			$this->Cell(15,8,utf8_decode(lang('Nb dates')),1,0,'C',1);
			$this->Cell(27,8,utf8_decode(lang('Start registration')),1,0,'C',1);
			$this->Cell(27,8,utf8_decode(lang('End registration')),1,0,'C',1);
			$this->Cell(20,8,utf8_decode(lang('Nb students')),1,1,'C',1);
		}else{
			$so_crs = new so_sql('spisession','spisession_course');
			$so_field = new so_sql('spisession','spisession_ref_field');

			$course = $so_crs->read($this->sessions[$this->current]['ses_crs']);
			$field = $so_field->read($course['crs_field']);

			// Police 
			$this->SetFont('Arial','',12);
			$this->SetLineWidth(.3);
			$this->SetY(5);
			
			// Couleur pour texte et ligne
			$this->SetDrawColor(192,192,192);
			$this->SetTextColor(192,192,192);

			$check_ses_lang = file_exists('spisession/templates/default/pdfimages/header_'.$this->sessions[$this->current]['ses_lang'].'.png');
			$check_default_lang = file_exists('spisession/templates/default/pdfimages/header_'.$GLOBALS['egw_info']['user']['preferences']['common']['lang'].'.png');

			if($check_ses_lang){
				// Image d'entete
				$this->Image('spisession/templates/default/pdfimages/header_'.$this->sessions[$this->current]['ses_lang'].'.png',$this->GetX(),$this->GetY(),44,20);
			}elseif ($check_default_lang) {
				// Image d'entete
				$this->Image('spisession/templates/default/pdfimages/header_'.$GLOBALS['egw_info']['user']['preferences']['common']['lang'].'.png',$this->GetX(),$this->GetY(),44,20);
			}

			// Image de fond
			$this->Image('spisession/templates/default/pdfimages/background_en.png',15,63.5,180,170);
			
			// On se deplace apres l'image d'entete
			$this->SetXY($this->GetX(),$this->GetY()+20);
			
			// Titre
			$startY = $this->GetY();
			$this->Line($this->GetX(),$this->GetY(),$this->GetX()+190,$this->GetY());
			$this->MultiCell(150,6,utf8_decode($course['crs_title'].' - '.$field['field_label']),0,'L');

			$newY = $this->GetY();
			$this->SetY($startY);
			$this->Cell(0,6,utf8_decode(date('d/m/Y')),0,1,'R');
			$this->Line($this->GetX(),$newY,$this->GetX()+190,$newY);


			// Couleur pour texte et ligne
			$this->SetDrawColor(0,0,0);
			$this->SetTextColor(0,0,0);
		}
	}
	
	function AddCover(){
	/**
	 * Page de couverture
	 */
		$so_crs = new so_sql('spisession','spisession_course');
		$so_grad = new so_sql('spisession','spisession_ref_graduation');
		$so_field = new so_sql('spisession','spisession_ref_field');
		$so_site = new so_sql('spireapi','spireapi_site');

		$course = $so_crs->read($this->sessions[$this->current]['ses_crs']);
		$grad = $so_grad->read($course['crs_grad']);
		$field = $so_field->read($course['crs_field']);

		$this->SetY(80);

		// Titre cours
		$this->SetFont('Arial','B',28);
		$this->MultiCell(0,15,utf8_decode($course['crs_code'].' : '.$course['crs_title']),0,'L');

		// Niveau et theme
		$this->SetFont('Arial','BI',25);
		$this->MultiCell(0,15,utf8_decode($field['field_label'].', '.$grad['grad_label']),0,'L');

		// Description courte
		$this->SetFont('Arial','I',16);
		$this->MultiCell(0,15,utf8_decode($course['crs_short_desc']),0,'L');

		// Lieu et date de session
		$this->Ln(10);
		$this->SetFont('Arial','BI',16);
		$start_date = lang(date('F',$this->sessions[$this->current]['ses_start_date'])).' '.date('d Y',$this->sessions[$this->current]['ses_start_date']);
		$end_date = lang(date('F',$this->sessions[$this->current]['ses_end_date'])).' '.date('d Y',$this->sessions[$this->current]['ses_end_date']);
		$site = $so_site->read($this->sessions[$this->current]['ses_location']);
		$this->MultiCell(0,15,utf8_decode($site['site_city'].', '.$GLOBALS['egw']->country->get_full_name($site['site_country']).', '.$start_date.' - '.$end_date),0,'L');

	}

	function AddContent(){
	/**
	 * Construit le contenu de la page
	 */
		$so_ses_date = new so_sql('spisession','spisession_session_date');
		$so_reg = new so_sql('spisession','spisession_registration');
		$so_role = new so_sql('spisession','spisession_ref_role');
		$so_site = new so_sql('spireapi','spireapi_site');

		// Police + Positionnement
		$this->SetLineWidth(.3);
		$this->Ln(10);
		// $this->SetY(35);

		// Informations
		$this->SetFont('Arial','BU',12);
		$this->Cell(60,6,utf8_decode(lang('General informations').' :'),0,1,'L');
		
		$this->SetFont('Arial','B',11);
		$this->Ln(2);
		$this->Cell(60,6,utf8_decode(lang('Dates').' :'),0,0,'L');

		$this->SetFont('Arial','',11);
		$start_date = lang(date('F',$this->sessions[$this->current]['ses_start_date'])).' '.date('d Y',$this->sessions[$this->current]['ses_start_date']);
		$end_date = lang(date('F',$this->sessions[$this->current]['ses_end_date'])).' '.date('d Y',$this->sessions[$this->current]['ses_end_date']);
		$this->Cell(0,6,utf8_decode($start_date.' - '.$end_date),0,1,'L');
		
		$this->SetFont('Arial','B',11);
		$this->Ln(2);
		$this->Cell(60,6,utf8_decode(lang('Location').' :'),0,0,'L');

		$this->SetFont('Arial','',11);
		$site = $so_site->read($this->sessions[$this->current]['ses_location']);
		$this->Cell(0,6,utf8_decode($site['site_label']),0,2,'L');
		$this->Cell(0,6,utf8_decode($site['site_street']),0,2,'L');
		$this->Cell(0,6,utf8_decode($site['site_street2']),0,2,'L');
		$this->Cell(0,6,utf8_decode($site['site_postalcode'].' '.$site['site_city']),0,1,'L');
		
		$temp = str_replace('<br />', "", $this->sessions[$this->current]['ses_cost']);
		if(!empty($temp)) {
			$this->SetFont('Arial','B',11);
			$this->Ln(2);
			$this->Cell(60,6,utf8_decode(lang('Costs').' :'),0,0,'L');

			$this->SetFont('Arial','',11);
			$this->MultiCell(0,6,utf8_decode(str_replace('<br />', "", $this->sessions[$this->current]['ses_cost'])),0,'L');
		}

		$this->Ln(2);

		// Dates de session
		$ses_dates = $so_ses_date->search(array('ses_date_ses' => $this->sessions[$this->current]['ses_id']),false,'ses_date_day');
		if(is_array($ses_dates)){
			$last_date = '';
			foreach((array)$ses_dates as $ses_date){
				if($ses_date['ses_date_day'] != $last_date){
					$this->SetFont('Arial','BU',12);
					$this->Cell(60,6,utf8_decode(lang(date('F',$ses_date['ses_date_day'])).' '.date('d Y',$ses_date['ses_date_day']).' :'),0,1,'L');

					$last_date = $ses_date['ses_date_day'];
				}

				$this->SetFont('Arial','B',11);
				
				$length = $this->GetStringWidth(utf8_decode(gmdate('H\hi',$ses_date['ses_date_start']).' - '.gmdate('H\hi',$ses_date['ses_date_end'])));
				$this->Cell($length,6,utf8_decode(gmdate('H\hi',$ses_date['ses_date_start']).' - '.gmdate('H\hi',$ses_date['ses_date_end'])),0,0,'L');

				if($ses_date['ses_date_break']){
					$this->SetFont('Arial','I',11);
				}else{
					$this->SetFont('Arial','BI',11);
				}
				$this->SetX($this->GetX()+2);
				$this->MultiCell(0,6,utf8_decode($ses_date['ses_date_title']),0,'L');

				if(!$ses_date['ses_date_break']){
					$this->SetFont('Arial','I',10);
					$this->SetX(45);
					$this->MultiCell(0,6,utf8_decode($ses_date['ses_date_desc']),0,'L');
				}
			}
		}else{
			$this->Cell(0,6,utf8_decode(lang('No session date')),0,1,'L');
		}
		
		// Inscriptions
		$this->Ln(3);
		$registrations = $so_reg->search(array('reg_ses' => $this->sessions[$this->current]['ses_id']),false,'reg_role');
		if(is_array($registrations)){
			$last_role = '';
			foreach((array)$registrations as $registration){
				if($registration['reg_status'] == $this->config['validated_reg_status']){
					if($registration['reg_role'] != $last_role){
						$this->SetFont('Arial','BU',12);
						$this->Ln(2);
						
						$role = $so_role->read($registration['reg_role']);
						$this->Cell(60,6,utf8_decode($role['role_label'].' :'),0,1,'L');

						$last_role = $registration['reg_role'];
					}

					$contact = $GLOBALS['egw']->contacts->read($registration['reg_contact']);

					$this->SetFont('Arial','',11);
					$this->Cell(50,6,utf8_decode($this->truncate($contact['n_family'],20)),0,0,'L');
					$this->Cell(50,6,utf8_decode($this->truncate($contact['n_given'],20)),0,0,'L');
					$this->Cell(0,6,utf8_decode($contact['contact_email']),0,1,'L');
				}
			}
		}else{
			$this->Cell(0,6,utf8_decode(lang('No one is registered for this session')),0,1,'L');	
		}
	}
	
	function AddContentList(){
	/**
	 * Contenu pour la vue liste
	 */
		$so_ses = new so_sql('spisession','spisession_session');
		$so_date = new so_sql('spisession','spisession_session_date');
		$so_course = new so_sql('spisession','spisession_course');
		$so_ses_status = new so_sql('spisession','spisession_ref_ses_status');
		$so_reg = new so_sql('spisession','spisession_registration');
		$so_site = new so_sql('spireapi','spireapi_site');

		// Données sessions
		$session = $so_ses->read($this->sessions[$this->current]);
		$course = $so_course->read($session['ses_crs']);
		$ses_status = $so_ses_status->read($session['ses_status']);
		
		// $responsible = $GLOBALS['egw']->contacts->search(array('egw_accounts.account_id' => $session['ses_responsible']),false);
		$responsible = $GLOBALS['egw']->accounts->read($session['ses_responsible']);
		$responsible = $GLOBALS['egw']->contacts->read($responsible['person_id']);
		
		$registrations = $so_reg->search(array('reg_ses' => $session['ses_id'],'reg_status' => $this->config['validated_reg_status'],'reg_role' => $this->config['student_role']),false);
		$dates = $so_date->search(array('ses_date_ses' => $session['ses_id']),false);
		$site = $so_site->read($session['ses_location']);

		$this->SetFillColor(210,210,210);
		$this->SetFont('Arial','',9);
		$this->SetX(5);

		// Ajout de la ligne dans le tableau
		$startY = $this->GetY();
		$this->MultiCell(46,6,utf8_decode($course['crs_title']),1,'L',$this->fill);
		$height = $this->GetY() - $startY;
		$this->SetXY(51,$startY);
		$this->Cell(25,$height,utf8_decode($ses_status['status_label']),1,0,'C',$this->fill);
		$this->Cell(23,$height,utf8_decode(date('d/m/Y',$session['ses_start_date'])),1,0,'C',$this->fill);
		$this->Cell(20,$height,utf8_decode(date('d/m/Y',$session['ses_end_date'])),1,0,'C',$this->fill);
		$this->Cell(45,$height,utf8_decode($responsible['n_fn']),1,0,'C',$this->fill);
		$this->Cell(39,$height,utf8_decode($this->truncate($site['site_label'],20)),1,0,'C',$this->fill);
		$this->Cell(15,$height,utf8_decode(count($dates)),1,0,'C',$this->fill);
		$this->Cell(27,$height,utf8_decode(date('d/m/Y',$session['ses_start_reg'])),1,0,'C',$this->fill);
		$this->Cell(27,$height,utf8_decode(date('d/m/Y',$session['ses_end_reg'])),1,0,'C',$this->fill);
		$this->Cell(20,$height,utf8_decode(count($registrations)),1,1,'C',$this->fill);

		$this->fill = $this->fill ? false : true;
	}

	function Footer(){
	/**
	 * Construit le pied de la page PDF
	 */
		if($this->type == 'list'){
			$this->SetY(200);
			$this->Cell(0,6,utf8_decode(lang('Page').' '.$this->PageNo().'/{nb}'),0,1,'C');
		}else{
			// Couleur pour texte et ligne
			$this->SetDrawColor(192,192,192);
			$this->SetTextColor(192,192,192);

			$footer_address = trans_so::translate($this->sessions[$this->current]['ses_lang'], 'footer_ses_address', 'spisession');
			$this->SetY(275);
			$this->SetFont('Arial','',8);
			$this->Cell(0,6,utf8_decode($footer_address),0,1,'C');

			// Séparateur
			$this->Line($this->GetX(),$this->GetY(),$this->GetX()+190,$this->GetY());

			$check_ses_lang = file_exists('spisession/templates/default/pdfimages/footer_'.$this->sessions[$this->current]['ses_lang'].'.png');
			$check_default_lang = file_exists('spisession/templates/default/pdfimages/footer_'.$GLOBALS['egw_info']['user']['preferences']['common']['lang'].'.png');

			if($check_ses_lang){
				// Image pied de page
				$this->Image('spisession/templates/default/pdfimages/footer_'.$this->sessions[$this->current]['ses_lang'].'.png',15,285,35,5);
			}elseif ($check_default_lang) {
				// Image pied de page
				$this->Image('spisession/templates/default/pdfimages/footer_'.$GLOBALS['egw_info']['user']['preferences']['common']['lang'].'.png',15,285,35,5);
			}

			
			$footer = trans_so::translate($this->sessions[$this->current]['ses_lang'], 'footer_ses', 'spisession');
			$this->SetXY(52,285);
			$this->SetFont('Arial','',7);
			$this->Cell(0,6,utf8_decode($footer),0,1,'C');
		}
	}
	
	function generate($path = '', $header = ''){
	/**
	 * Fonction de génération du pdf
	 */
		$this->AliasNbPages();
		$this->header = $header;

		foreach((array)$this->sessions as $id => $session){
			$this->current = $id;
			
			$this->AddPage();
			$this->AddCover();
			$this->AddPage();
			$this->AddContent();
		}
		
		if($path != ''){
			$this->Output($path,'F');
		}else{
			$this->Output();
		}
	}
	
	function generate_list($path = ''){
	/**
	 * Fonction de génération du pdf
	 */
		$this->type = 'list';
		$this->AliasNbPages();

		$this->AddPage();

		foreach((array)$this->sessions as $id => $session){
			$this->current = $id;
			$this->AddContentList();
		}

		if($path != ''){
			$this->Output($path,'F');
		}else{
			$this->Output();
		}
	}
}

?>