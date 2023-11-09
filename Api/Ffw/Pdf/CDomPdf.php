<?php

namespace Ffw\Pdf;
use Ffw\Crypt\CCrypt8;
use Api\Mt;
use Dompdf\FontMetrics;
use Dompdf\Dompdf;
use Dompdf\Options;

class CDomPdf {

	static function htmlToPDF($path, $documentName="", $stream=false) {

		$ch = curl_init($path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if (isset($_COOKIE[session_name()]))
			curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . $_COOKIE[session_name()] . '; path=/');

		session_write_close();
		$html = curl_exec($ch);

		$info = curl_getinfo($ch);
		$ct = $info['content_type'];
		
		if (curl_errno($ch)) {
			die ('curl error: ' . curl_error($ch));
		}

		curl_close($ch);
		
		$pdfContents = "";
		if ($ct == "application/pdf ") {	//Already PDF
			$pdfContents = $html;
		} 
		else if (substr($ct, 0, 9) == "text/html") {
			require_once Mt::$appDir . '/Api/Ffw/Pdf/dompdf/autoload.inc.php';
			
			if(session_status() === PHP_SESSION_NONE) @session_start();
			$options = new Options();
			$options->set('isRemoteEnabled', TRUE);
			$options->set("isPhpEnabled", true);
			$dompdf = new Dompdf($options);
			$contxt = stream_context_create([
				'ssl' => [
					'verify_peer' => FALSE,
					'verify_peer_name' => FALSE,
					'allow_self_signed' => TRUE
				]
			]);
			$dompdf->setHttpContext($contxt);
			$dompdf->loadHtml($html);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();

			$canvas = $dompdf->get_canvas();
			$fontMetrics = $dompdf->getFontMetrics();
			$font = $fontMetrics->getFont($dompdf->getOptions()->getDefaultFont()) . ".ttf";
		    if ($documentName !="")
		        $canvas->page_text(16, 800, "{$documentName}.   Page: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
			//$dompdf->stream($documentName,["Attachment"=>0]);
			$pdfContents = $dompdf->output();
		}
		else {
		}

		if ($pdfContents != "") {
			return $pdfContents;
		}

	}

}