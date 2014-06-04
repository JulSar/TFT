<?php

/*
curl -v --data-urlencode data@4STORE_sparql11-test-suite/sparql11-test-suite/syntax-fed/manifest.ttl \
-d 'graph=http://dev.grid-observatory.org/sparql11-test-suite/' \
-d 'mime-type=application/x-turtle' \
http://dev.grid-observatory.org:8000/data/ --trace-ascii /dev/stdout
*/
class FourStoreTestSuite  extends TestSuite {

  function install(){  
		global $modeDebug,$modeVerbose;

		$success = true;		
		$nb = 0;
		$this->endpoint->ResetErrors();
		
		foreach ($listFileTTL as $value) {		
			$path = "4STORE_sparql11-test-suite/".$value[0];
			$dirname = dirname($path);
			if (!is_dir($dirname))
			{
				if(!mkdir($dirname, 0755, true))
				{
					die('Erreur dans la création du répertoire.');
				}
			}
			
			$fp = fopen($path, 'w');
					fwrite($fp,FourStoreTools::fixTTL(file_get_contents($value[0]),$value[0]));
					fflush($fp);
					fclose($fp);
					
			echo ".";
		}		
		
        
		$len = strlen($this->endpoint->getEndpointUpdate());
		$urlSaveData = substr($this->endpoint->getEndpointUpdate(), 0, $len - ($len  - strrpos ( $this->endpoint->getEndpointUpdate(), "update/")))."data/"; // ?
		foreach ($listFileTTL as $value) {
			$curl = new Curl($modeDebug);
			
			$graph = "";
			if (preg_match("/manifest[^\.]*\.ttl$/i", $value[1])) {				
				$graph = $this->graph;
			} else {
				$graph = str_replace($this->folder,$this->graph,$value[0]);
			}
			
			$postdata = array("mime-type" => "application/x-turtle", "graph" => $graph);
			$headerdata = array("Content-Type: application/x-www-form-urlencoded");
			$curl->send_post_content($urlSaveData, $headerdata,$postdata,  file_get_contents(getcwd()."/4STORE_sparql11-test-suite/".$value[0]));
			          
			$code = $curl->get_http_response_code();
			if($code<200 && $code >= 300)
			{
				echo "\n".$path."\n";
				echo "ERROR ".$code." : cannot import files TTL in 4store!!";
				$success = false;		
				//exit();
			}				
			
			echo ".";
			$nb++;
		}
		
		echo "\n";
		echo $nb." new graphs\n";
		
		return $success ;
	}
	
	function fixTTL($contentTTL,$path){
		global $modeDebug,$modeVerbose;
		
		
		$resultContent = $contentTTL;
		
		$URI = str_replace($this->folder,$this->graph,$path);
		$URI = str_replace($this->folder,$this->graph,$path);
		$patternDetectNotUri = '/<>/im';
		$replacementNotUri = '<'.$URI.'>';
		$resultContent = preg_replace($patternDetectNotUri, $replacementNotUri, $resultContent);

		$len = strlen($URI);
		$prefix = substr($URI, 0, $len - ($len  - strrpos ( $URI , "/"))); 
		//Problem with sesame : check URI
		$patternDetectNotUri = '/<([^:<>]+)>/im';
		$replacementNotUri = '<'.$prefix .'/$1>';
		$resultContent = preg_replace($patternDetectNotUri, $replacementNotUri, $resultContent);		
		return $resultContent;
	}
	
   	function importData($endpoint,$content,$graph = "DEFAULT"){	
		global $modeDebug,$modeVerbose,$TESTENDPOINT;			
		$len = strlen($TESTENDPOINT->getEndpointUpdate());
		$url = substr($TESTENDPOINT->getEndpointUpdate(), 0, strrpos( $TESTENDPOINT->getEndpointUpdate(), "update/"))."data/";
		
		if($graph == "DEFAULT"){
			$postdata = array();
		}else{
			$postdata = array("graph" => $graph);
		}
		$headerdata = array("Content-Type: application/x-www-form-urlencoded");
		$curl = new Curl($modeDebug);
		$contentFinal = $this->fixTTL($content,$graph);
		
		 $curl->send_post_content(
			 $url, 
			 $headerdata,
			 $postdata,
			 $contentFinal);
		
		$code = $curl->get_http_response_code();	
		
		if($code<200 || $code >= 300)
		{
			echo "ERROR ".$code." : cannot import files TTL in 4store!!";			
			exit();
		}	
	}
} 
