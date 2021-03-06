<?php
use JasperPHP\Report;
use JasperPHP\Report2XLS;
use JasperPHP\ado\TTransaction;
use JasperPHP\ado\TLogger;
use JasperPHP\ado\TLoggerHTML;

//use PHPexcel as PHPexcel; // experimental
/**
* classe TJasper
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
* @version  2018.10.15
* @access   restrict
* 
* 2015.03.11 -- create
* 2018.10.15 -- revision and internationalize, add TLogger classes
**/
class TJasper
{
	private $report;
	private $type;

	/**
	* method __construct()
         * 
	* @param $jrxml = a jrxml file name
	* @param $param = a array with params to use into jrxml report
	*/
	public function __construct($jrxml, array $param)
	{
		$xmlFile=  $jrxml;
		$this->type = (array_key_exists('type',$param))?$param['type']:'pdf';
		//error_reporting(0);
		switch ($this->type)
		{
			case 'pdf': 
				$this->report = new JasperPHP\Report($xmlFile,$param);
				JasperPHP\Pdf::prepare($this->report);
				break;
			case 'xls':
				JasperPHP\Excel::prepare();
				$this->report = new JasperPHP\Report2XLS($xmlFile,$param);
				
				break;
		}
	}
	public function outpage($type='pdf'){
		$this->report->generate();
		$this->report->out();
		switch ($this->type)
		{
			case 'pdf':
				$pdf  = JasperPHP\Pdf::get();
				$pdf->Output('report.pdf',"I");
				break;
			case 'xls':
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="01simple.xls"');
				header('Cache-Control: max-age=0');
				// If you're serving to IE 9, then the following may be needed
				header('Cache-Control: max-age=1');
				// If you're serving to IE over SSL, then the following may be needed
				header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
				header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
				header ('Pragma: public'); // HTTP/1.0
				$objWriter = PHPExcel_IOFactory::createWriter($this->report->wb, 'Excel5');
				$objWriter->save('php://output');
			break;
		}
		
	}
	public function setVariable($name,$value){
		$this->report->arrayVariable[$name]['initialValue'] = $value ;
	}
}
require('autoloader.php');
require('../../tecnickcom/tcpdf/tcpdf.php'); // point to tcpdf class previosly instaled , 
                                            // on composer instalation is not necessaty 

$report_name = isset($_GET['report'])?$_GET['report']:'testReport.jrxml';  // sql into testReport.txt report do not select any table.
TTransaction::open('dev');
TTransaction::setLogger(new TLoggerHTML('log.html'));
$jasper = new TJasper($report_name,$_GET);
$jasper->outpage();