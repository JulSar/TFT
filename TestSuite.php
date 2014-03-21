<?php
require_once 'lib/sparql/Endpoint.php';
require_once 'Tools.php';

class TestSuite {

	public $endpoint = "";
	public $graph = "";
	public $folder = "";
	
	function __construct($endpoint,$graph,$folder)
	{
		$this->endpoint = $endpoint;
		$this->graph = $graph;
		$this->folder = $folder;
	}
     
   function clear(){   
		global $modeDebug,$modeVerbose;//,$this->endpoint,$listFileTTL,$this->graph,$folderTests;	
		$nb = 0;
				
		$this->endpoint->ResetErrors();
		$q = 'DROP SILENT GRAPH <'.$this->graph.'>';		
		$res = $this->endpoint->queryUpdate($q);
		$err = $this->endpoint->getErrors();
		 if ($err) {
			print_r($err);
			exit();
		 }		
		 $nb++;
   }
   
   function listFileTTL(){ 
		$Directory = new RecursiveDirectoryIterator($this->folder);
		$Iterator = new RecursiveIteratorIterator($Directory);
		return new RegexIterator($Iterator, '/^.+\/([^\/]+\.ttl)$/i', RecursiveRegexIterator::GET_MATCH);
		//$listFileString = new RegexIterator($Iterator, '/^.+\/([^\/]+(\.rdf|\.rq||\.ru|\.srx|\.srj|\.csv|\.tsv))$/i', RecursiveRegexIterator::GET_MATCH);
   }
   
   function install(){   
		global $modeDebug,$modeVerbose;
		$success = true;		
		$nb = 0;		
		$listFileTTL = $this->listFileTTL();		
		foreach ($listFileTTL as $value) {
			$path = str_replace($this->folder,$this->graph,$value[0]);
			if (preg_match("/manifest[^\.]*\.ttl$/i", $value[1])) {			
				Tools::loadData($this->endpoint,$this->graph,$path);
			}
			$nb ++;
		}
		
		echo "\n";
		echo $nb." new graphs\n";
		return $success ;
	}
	
	   function importDataTest($endpoint,$graph){
			Tools::loadData($endpoint,$graph,$graph);
	   }
}   
