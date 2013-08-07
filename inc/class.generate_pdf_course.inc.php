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


class generate_pdf_course extends fpdf{

	var $colonnes;
	var $format;
	var $type;
	
	var $timelogs;
	
	var $config;
	
	function generate_pdf($crs_id,$list = false){
	/**
	* Constructeur 
	*/
		
		self::__construct($crs_id,$list = false);
	}
	
	function __construct($crs_id,$list = false){
	/**
	* Méthode appelée directement par le Constructeur . Charge les varibles globales. $emetteur est un tableau décrivant la socité émettrice
	* $client esu une tableau décrivant la société cliente
	*
	* @param $crs_id : identifiant du cours ou tableau contenant la listes des cours
	* @param $list : impression en liste (o/n)
	*/
		// Initialisation la config
		$config = CreateObject('phpgwapi.config');
		$this->config = $config->read('spisession');
		$this->type = '';
		
		$so_crs = new so_sql('spisession','spisession_course');
		if(is_array($crs_id)){
			$this->courses = $crs_id;
		}else{
			$this->courses[0] = $so_crs->read($crs_id);
		}
		
		define('FPDF_FONTPATH',EGW_INCLUDE_ROOT.'/spisession/fpdf/font/');

		// Liste = paysage
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
	* Construit le bas de la page PDF
	*/
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

		// Image entete
		$this->Image('spisession/templates/default/pdfimages/header_en.png',$this->GetX(),$this->GetY(),44,20);

		// Image de fond
		$this->Image('spisession/templates/default/pdfimages/background_en.png',15,63.5,180,170);
		
		// On se place juste apres l'image d'entete
		$this->SetXY($this->GetX(),$this->GetY()+20);
		
		// Titre 
		$this->Line($this->GetX(),$this->GetY(),$this->GetX()+190,$this->GetY());
		$this->Cell(150,6,utf8_decode(lang('Course presentation')),0,0,'L');
		$this->Cell(0,6,utf8_decode(date('d/m/Y')),0,1,'R');
		$this->Line($this->GetX(),$this->GetY(),$this->GetX()+190,$this->GetY());


		// Couleur pour texte et ligne
		$this->SetDrawColor(0,0,0);
		$this->SetTextColor(0,0,0);
	}

	function AddContent(){
	/**
	 * Construit le contenu de la page
	 */
		$so_crs_status = new so_sql('spisession','spisession_ref_crs_status');
		$so_grad = new so_sql('spisession','spisession_ref_graduation');
		$so_crs_comp = new so_sql('spisession','spisession_crs_component');
		$so_comp = new so_sql('spisession','spisession_ref_component');


		// Positionnement
		$this->SetLineWidth(.2);
		$this->SetY(35);
		
		// Code : Titre
		$this->SetFont('Arial','B',14);
		$this->Ln(2);
		$this->MultiCell(0,8,utf8_decode($this->courses[$this->current]['crs_code'].' : '.$this->courses[$this->current]['crs_title']),0,'L');

		// Description courte
		$this->SetFont('Arial','I',11);
		$this->Ln(2);
		$this->MultiCell(0,6,utf8_decode($this->courses[$this->current]['crs_short_desc']),0,'L');

		// Responsable
		$this->SetFont('Arial','B',11);
		$this->Ln(2);
		$this->Cell(35,8,utf8_decode(lang('Responsible').' :'),0,0,'L');

		$this->SetFont('Arial','',11);
		// $contact = $GLOBALS['egw']->contacts->search(array('egw_accounts.account_id' => $this->courses[$this->current]['crs_responsible']),false);
		$contact = $GLOBALS['egw']->accounts->read($this->courses[$this->current]['crs_responsible']);

		$this->Cell(60,8,utf8_decode($contact['account_lastname'].' '.$contact['account_firstname']),0,0,'L');

		// Statut
		$this->SetFont('Arial','B',11);
		$this->Cell(47,8,utf8_decode(lang('Status').' :'),0,0,'L');

		$this->SetFont('Arial','',11);
		$status = $so_crs_status->read($this->courses[$this->current]['crs_status']);
		$this->Cell(48,8,utf8_decode($status['status_label']),0,1,'L');

		// Nombre de date par defaut
		if(!empty($this->courses[$this->current]['crs_duration'])){
			$this->SetFont('Arial','B',11);
			$this->Cell(35,8,utf8_decode(lang('Default nb of dates').' :'),0,0,'L');

			$this->SetFont('Arial','',11);
			$this->Cell(60,8,utf8_decode($this->courses[$this->current]['crs_duration']),0,0,'L');
		}else{
			$this->Cell(35,8,'',0,0,'L');
			$this->Cell(60,8,'',0,0,'L');
		}

		// Nb heures
		if(!empty($this->courses[$this->current]['crs_nb_hours'])){
			$this->SetFont('Arial','B',11);
			$this->Cell(47,8,utf8_decode(lang('Nb hours').' :'),0,0,'L');

			$this->SetFont('Arial','',11);
			$this->Cell(48,8,utf8_decode($this->courses[$this->current]['crs_nb_hours']),0,1,'L');
		}else{
			$this->Cell(47,8,'',0,0,'L');
			$this->Cell(48,8,'',0,1,'L');
		}

		// niveau
		if(!empty($this->courses[$this->current]['crs_grad'])){
			$this->SetFont('Arial','B',11);
			$this->Cell(35,8,utf8_decode(lang('Level').' :'),0,0,'L');

			$this->SetFont('Arial','',11);
			$grad = $so_grad->read($this->courses[$this->current]['crs_grad']);
			$this->Cell(60,8,utf8_decode($grad['grad_label']),0,0,'L');
		}else{
			$this->Cell(35,8,'',0,0,'L');
			$this->Cell(60,8,'',0,0,'L');
		}
		

		// unites
		if(!empty($this->courses[$this->current]['crs_units']) && $this->courses[$this->current]['crs_units'] > 0){
			$this->SetFont('Arial','B',11);
			$this->Cell(47,8,utf8_decode(lang('Units').' :'),0,0,'L');

			$this->SetFont('Arial','',11);
			$this->Cell(48,8,utf8_decode($this->courses[$this->current]['crs_units']),0,1,'L');
		}else{
			$this->Cell(47,8,'',0,0,'L');
			$this->Cell(48,8,'',0,1,'L');
		}

		// Description longue - n'est pas imprimée pour formatage HTML complexe
		// $this->Ln(2);
		// $this->SetFont('Arial','B',11);
		// $this->Cell(47,8,utf8_decode(lang('Description').' :'),0,1,'L');

		// $this->SetFont('Arial','',11);
		// $this->MultiCell(0,6,utf8_decode(str_replace('<br />', "", $this->courses[$this->current]['crs_desc'])),0,'L');

		// Composante
		$this->Ln(4);
		
		$components = $so_crs_comp->search(array('crs_id' => $this->courses[$this->current]['crs_id']),false);
		if(is_array($components)){
			$this->SetFont('Arial','B',11);
			$this->Cell(170,8,utf8_decode('Component'),1,0,'L');
			$this->Cell(20,8,utf8_decode('Required'),1,1,'C');

			$this->SetFont('Arial','',11);
			foreach((array)$components as $component){
				$comp = $so_comp->read($component['comp_id']);
				$this->Cell(170,8,utf8_decode($comp['comp_label']),1,0,'L');
				$this->Cell(20,8,utf8_decode($component['crs_comp_required'] ? 'x' : ''),1,1,'C');
			}
		}else{
			$this->Cell(0,8,utf8_decode(lang('No component selected for this course')),0,1,'L');
		}


		// Description
		$this->SetFont('Arial','B',11);
		$this->Cell(0,8,utf8_decode(lang('Description').' :'),0,1,'L');
		$this->SetFont('Arial','',11);
		$this->Ln(2);
		$this->MultiCell(0,6,utf8_decode(translation::convertHTMLToText($this->courses[$this->current]['crs_desc'],'utf8',false,true)),0,'L');
	}
	
	function AddContentList(){
	/**
	 * Contruction de la vue liste
	 */
		$so_ses = new so_sql('spisession','spisession_session');
		$so_date = new so_sql('spisession','spisession_session_date');
		$so_course = new so_sql('spisession','spisession_course');
		$so_ses_status = new so_sql('spisession','spisession_ref_ses_status');
		$so_reg = new so_sql('spisession','spisession_registration');

		// Données sessions
		$session = $so_ses->read($this->sessions[$this->current]);
		$course = $so_course->read($session['ses_crs']);
		$ses_status = $so_ses_status->read($session['ses_status']);
		
		$responsible = $GLOBALS['egw']->contacts->search(array('egw_accounts.account_id' => $session['ses_responsible']),false);

		$registrations = $so_reg->search(array('reg_ses' => $session['ses_id'],'reg_status' => $this->config['validated_reg_status'],'reg_role' => $this->config['student_role']),false);
		$dates = $so_date->search(array('ses_date_ses' => $session['ses_id']),false);

		$this->SetFillColor(210,210,210);
		$this->SetFont('Arial','',9);
		$this->SetX(5);

		// Ajout des données dans la table
		$startY = $this->GetY();
		$this->MultiCell(46,6,utf8_decode($course['crs_title']),1,'L',$this->fill);
		$height = $this->GetY() - $startY;
		$this->SetXY(51,$startY);
		$this->Cell(35,$height,utf8_decode($ses_status['status_label']),1,0,'C',$this->fill);
		$this->Cell(23,$height,utf8_decode(date('d/m/Y',$session['ses_start_date'])),1,0,'C',$this->fill);
		$this->Cell(20,$height,utf8_decode(date('d/m/Y',$session['ses_end_date'])),1,0,'C',$this->fill);
		$this->Cell(45,$height,utf8_decode($responsible[0]['n_fn']),1,0,'C',$this->fill);
		$this->Cell(29,$height,utf8_decode('[Site]'),1,0,'C',$this->fill);
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

		foreach((array)$this->courses as $id => $course){
			$this->current = $id;

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