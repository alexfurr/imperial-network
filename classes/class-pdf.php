<?php

if ( isset($_GET['createPDF'] ) )
{
	

	// Handle CSV Export
	add_action( 'init', array('imperialPDF', 'downloadPDF') );	


	
}





class imperialPDF
{




	static function downloadPDF()
	{
		
		
		$createPDF = $_GET['createPDF'];
		
		
		$fileName = '';
		
		switch ($createPDF)
		{
			
			case "allFormSubmissions":
				$username = $_GET['username'];
				$academicYear = $_GET['academicYear'];

				$fileNameArray = imperialPDF::create_all_form_submissions($username, $academicYear);		
				$fileName = $fileNameArray['fileName'];
				$fileRef = $fileNameArray['fileRef'];	

			break;	


			case "formSubmission":
				$submissionID = $_GET['submissionID'];
				$username = $_GET['username'];

				$fileNameArray = imperialPDF::create_form_submission($username, $submissionID);		
				$fileName = $fileNameArray['fileName'];
				$fileRef = $fileNameArray['fileRef'];
			break;
		}
		
		if($fileName=="")
		{
			die();
		}
		
			
		header("Content-type:application/pdf");
		// It will be called downloaded.pdf
		header("Content-Disposition:attachment;filename='".$fileName."'");

		// The PDF source is in original.pdf			
		readfile($fileRef);

		// Make sure nothing else is sent, our file is done
		die();
		
	}

	
	
	
	// All User submissions for an acaedmic year PDF
	static function create_all_form_submissions($username, $academicYear)	
	{
		

		// Get Username from email
		$userMeta = imperialQueries::getUserInfo($username);
		$submitterName = $userMeta['first_name'].' '.$userMeta['last_name'];		
		$niceAcademicYear = imperialNetworkUtils::getNiceAcademicYear($academicYear, "-");		
	
	

		$pdfTitle= $submitterName.' submissions - '.$niceAcademicYear;
		$pdfFileName = $pdfTitle.'.pdf';


		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		
		$headerStr = 'Created by MedLearn';
		$headerTitle = $pdfTitle; 

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('MedLearn');
		$pdf->SetTitle($pdfTitle);

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $pdfTitle, $headerStr, array(0,64,255), array(0,64,128));
		$pdf->setFooterData(array(0,64,0), array(0,64,128));

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('dejavusans', '', 8, '', true);
		

		// Add a page
		
		
		// Get an array of all submission IDs for this user and academic year
		$userSubmissions = form2_queries::getUserSubmissionsByAcademicYear($username, $academicYear);
		
		foreach ($userSubmissions as $submissionMeta)
		{
			
			
			$html='';
			
			$submissionID = $submissionMeta->submissionID;
			$formID = $submissionMeta->formID;
			
			// Get the form name
			$formInfo = form2_queries::getFormInfo( $formID, $academicYear );
			$formName = $formInfo['formName'];

			
			$pdf->AddPage();

			
			$html.='<h2>'.$formName.'</h2>';
			// Set some content to print
			$html.='Submission ID : '.$submissionID.'<br/>';					
			$html.=form2_draw::drawSubmissionFields($submissionID, true);
			
			
			// Print text using writeHTMLCell()
			$pdf->writeHTML	(
				$html,
				true,
				false,
				false,
				false,
				'' 
			);			
			

			
		}
		

			
		



		
		//--- output the PDF ---------------------------------------	
		$WPuploads = wp_upload_dir();
		$basePath = $WPuploads['basedir'];
		if ( ! file_exists( $basePath ) ) {
			mkdir( $basePath, 0777, true );
		}		
		
		$form2Dir = $basePath.'/form2/temp';
		if ( ! file_exists( $form2Dir ) ) {
			mkdir( $form2Dir, 0777, true );
		}			
		
		
		
		//temp set server limit and timeout
		ini_set("memory_limit", "1024M");
		ini_set("max_execution_time", "600");
		ini_set("allow_url_fopen", "1");
		
		//output PDF document.
		$pdf->Output( $form2Dir . '/' . $pdfFileName, 'F');
		
		$fullRef = $form2Dir . '/' . $pdfFileName;

		$fileNameArray['fileName'] = $pdfFileName;
		$fileNameArray['fileRef'] = $fullRef;
		
		return $fileNameArray;
	}	

	
	
	
	// Single Submission PDF
	static function create_form_submission ($username, $submissionID) 
	{
		
		$submissionData = form2_queries::getSubmissionData( $submissionID );
		$formID = $submissionData['formID'];
		$academicYear= $submissionData['academicYear'];
		$email = $submissionData['email'];
		// Get Username from email
		$username = form2_utils::getUsernameFromEmail($email);	
		$userMeta = imperialQueries::getUserInfo($username);
		$submitterName = $userMeta['first_name'].' '.$userMeta['last_name'];		

		// Get the Form Name
		$formInfo = form2_queries::getFormInfo( $formID );
		$formName = $formInfo['formName'];		
		$formName = preg_replace("/[^A-Za-z0-9 ]/", '', $formName);
		
		
		$pdfTitle= $formName.' - '.$submitterName;
		$pdfFileName = $pdfTitle.'.pdf';


		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		
		$headerStr = 'Created by MedLearn';
		$headerTitle = $pdfTitle; 

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('MedLearn');
		$pdf->SetTitle($pdfTitle);

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $pdfTitle, $headerStr, array(0,64,255), array(0,64,128));
		$pdf->setFooterData(array(0,64,0), array(0,64,128));

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('dejavusans', '', 8, '', true);
		

		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

		// Set some content to print
		$html = '<h1>'.$pdfTitle.'</h1>';
		$html.='Submission ID : '.$submissionID.'<br/>';
				
		$html.=form2_draw::drawSubmissionFields($submissionID, true);
		
		
		// Print text using writeHTMLCell()
		$pdf->writeHTML	(
			$html,
			true,
			false,
			false,
			false,
			'' 
		);

		
		//--- output the PDF ---------------------------------------	
		$WPuploads = wp_upload_dir();
		$basePath = $WPuploads['basedir'];
		if ( ! file_exists( $basePath ) ) {
			mkdir( $basePath, 0777, true );
		}
		
		
		$form2Dir = $basePath.'/form2/temp';
		if ( ! file_exists( $form2Dir ) ) {
			mkdir( $form2Dir, 0777, true );
		}		
		
		
		//temp set server limit and timeout
		ini_set("memory_limit", "1024M");
		ini_set("max_execution_time", "600");
		ini_set("allow_url_fopen", "1");
		
		//output PDF document.
		$pdf->Output( $form2Dir . '/' . $pdfFileName, 'F');
		
		$fullRef = $form2Dir . '/' . $pdfFileName;

		$fileNameArray['fileName'] = $pdfFileName;
		$fileNameArray['fileRef'] = $fullRef;
		
		return $fileNameArray;
	}	
	
	
}



?>