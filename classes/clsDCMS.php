<?php
	class DistributedCMS{
		var $RemoteServer="creativeweblogic.net/";
		var $BaseCacheDirectory="cache/";
		var $BaseDomainCacheDirectory="../cache/";
		var $current_dir="";
		var $current_back_dir="";
		var $LocalServer;
		var $RequestUnCachedFiles=true;
		var $RemoteServerIP="50.116.93.232";
		var $ForbiddenExtensions=array();
		var $useragent="curl";
		var $cookieFile = "cookies.txt";
		var $guid="";
		
		function __construct(){
			
			
			$current_dir=pathinfo(__DIR__);
			//print_r($current_dir);
			$this->current_back_dir=$current_dir["dirname"].'/';
			//print "=1=".$this->current_back_dir."="."-\n\n";
			$this->current_dir=$current_dir['dirname'].'/'.$current_dir['basename']."/";
			
			$this->LocalServer=urlencode($_SERVER['HTTP_HOST']);
			$this->BaseDomainCacheDirectory=$this->CacheDirectory().$this->slash_wrap($this->LocalServer)."/";
			//print $this->BaseDomainCacheDirectory;
			if (!file_exists($this->BaseDomainCacheDirectory)) {
				if(!mkdir($this->BaseDomainCacheDirectory)){
					echo "error"."-\n\n";
				}
			}
			
			
		}

		
		function make_guid ($length=32) 
		{ 
			if (function_exists('com_create_guid') === true)
			{
					return trim(com_create_guid(), '{}');
			}else{
				$key="";    
				$minlength=$length;
				$maxlength=$length;
				$charset = "abcdefghijklmnopqrstuvwxyz"; 
				$charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
				$charset .= "0123456789"; 
				if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength); 
				else                         $length = mt_rand ($minlength, $maxlength); 
				for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))]; 
				return $key;
			}	
		}

		
		function set_cookie(){
			
			if(!isset($_SESSION['guid'])){
				$this->guid=$this->make_guid();
				$_SESSION['guid']=$this->guid;
			}else{
				$this->guid=$_SESSION['guid'];
			}
			//print_r($_SESSION);
			$this->cookieFile = "cache/cookies/".$this->guid."-cookies.txt";
			if(!file_exists($this->cookieFile)) {
				$fh = fopen($this->cookieFile, "w");
				fwrite($fh, "");
				fclose($fh);
			}
		}
		
		function slash_wrap($DisplayPage){
			return urlencode(base64_encode($DisplayPage));
		}
		
		function CacheDirectory(){
			//return $this->BaseCacheDirectory;
			$dir=$this->current_back_dir.$this->BaseCacheDirectory;
			//print "=2=".$dir."="."-\n\n";
			return $this->current_back_dir.$this->BaseCacheDirectory;
		}
		function CheckIfHTMLFile($DisplayPage){
			$BSlashEncoded=urlencode("/");
			if(substr($DisplayPage,strlen($DisplayPage)-strlen($BSlashEncoded))==$BSlashEncoded){
				return true;
			}else{
				return false;
			}
		}
		
		function LocalFileName($DisplayPage){
			//print $this->current_dir;
			if($this->CheckIfHTMLFile($DisplayPage)){
				//$filename = $this->BaseDomainCacheDirectory.str_replace("/","",base64_encode($DisplayPage));
				$filename = $this->BaseDomainCacheDirectory.$this->slash_wrap($DisplayPage);
			}else{
				//$filename = $this->CacheDirectory().str_replace("/","",base64_encode($DisplayPage));
				$filename = $this->CacheDirectory().$this->slash_wrap($DisplayPage);
			}
			
			//return str_replace("/","",$filename);
			//return urlencode($filename);
			return $filename;
		}
		
		function url_get_contents($url){//,$DisplayPage) {
			//print $url;
			$this->set_cookie();
			$encoded="";
			if(count($_GET)>0){
				foreach($_GET as $name => $value) {
					$encoded .= urlencode($name).'='.urlencode($value).'&';
			  	}
			}
			if(count($_POST)>0){
				foreach($_POST as $name => $value) {
					$encoded .= urlencode($name).'='.urlencode($value).'&';
				  }
			}
			  
			$encoded = substr($encoded, 0, strlen($encoded)-1);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile); // Cookie aware
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile); // Cookie aware
			/*
			if ($headers==true){
				curl_setopt($ch, CURLOPT_HEADER,1);
			}
			if ($headers=='headers only') {
				curl_setopt($ch, CURLOPT_NOBODY ,1);
			}
			if ($follow_redirects==true) {
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
			}
			if ($debug==true) {
				$result['contents']=curl_exec($ch);
				$result['info']=curl_getinfo($ch);
			}
			
			else */$result=curl_exec($ch);
			curl_close($ch);
			//print $url."-".$result;
			//print $url;
			/*
			$filename = $this->CacheDirectory.base64_encode($DisplayPage);
			$cp = curl_init($url);
			$fp = fopen($filename, "w");
			
			curl_setopt($cp, CURLOPT_FILE, $fp);
			curl_setopt($cp, CURLOPT_HEADER, 0);
			curl_setopt($cp, CURLOPT_USERAGENT,"ie");
			
			curl_exec($cp);
			curl_close($cp);
			fclose($fp);
			$SCount=0;
			do{
				$SCount++;
				sleep(1);
			}while(filesize($filename)==0||$SCount<10);
			$this->DisplayCacheFile($DisplayPage);
			*/
			return $result;
		}
		
		function WriteCacheFile($DisplayPage,$content){
			//$DisplayPage="xxx";
			$filename = $this->LocalFileName($DisplayPage);
			//print $filename."-\n\n";
			if (is_writable($filename)) {
				if (!$handle = fopen($filename, 'x')) {
					 $this->Error("Cannot open file ($filename)");
					 exit;
				}
				if (fwrite($handle, $content) === FALSE) {
					$this->Error("Cannot write to file ($filename)");
					exit;
				}
				fclose($handle);
			} else {
				//$this->Error("The file $filename is not writable");
				//echo $filename." - The file $filename is not writable"."-\n\n";
			}
		}
		
		function CheckIfCacheExists($DisplayPage){
			$filename = $this->LocalFileName($DisplayPage);
			//print "-3-".$filename."--";
			if(file_exists($filename)){
				return true;
			}else{
				return false;
			}
		}
		
		function DisplayCacheFile($DisplayPage){
			$filename = $this->LocalFileName($DisplayPage);
			//print $filename;
			$handle = fopen($filename, "r");
			$contents = fread($handle, filesize($filename));
			//if(substr($DisplayPage,strlen($DisplayPage)-1)=="/"){
			fclose($handle);
			//print "-1-".$contents."-"."-\n\n";
			if(strlen($contents)==0){
				unlink($filename);
				//$ContType="Content-Type: ".exec("file -bi '$filename'");
				$ContType=mime_content_type($filename);
				header($ContType);
				//print $contents."-\n\n"."-\n\n";
			}else{
				//print "-".$contents."-\n\n"."-\n\n";
				print $this->DisplayRealtime($DisplayPage);
			}
			
			//print $ContType;
		}
		
		function Error($error){
			print $error;
		}
		
		function IsValidFile($DisplayPage){
			if(substr($DisplayPage,strlen($DisplayPage)-1)=="/"){
				return true;
			}else{
				if(strlen($DisplayPage)>3){
					if(in_array(substr($DisplayPage,strlen($DisplayPage)-3),$this->ForbiddenExtensions)){
						return false;	
					}else{
						return true;
					}	
				}else{
					return false;	
				}
			}
		}
		
		function RequestUpdate($DisplayPage){
			//print $DisplayPage;
			if($this->IsValidFile($DisplayPage)){
				$DisplayPage=urlencode($DisplayPage);
				$urldetails=$this->RemoteServer."?x=1&dcmshost=".$this->LocalServer."&dcmsuri=".$DisplayPage;	
				$retdata=$this->url_get_contents($urldetails);
				$this->WriteCacheFile($DisplayPage,$retdata);
			}
		}
		
		
		function DisplayRealtime($DisplayPage){
			$urldetails=$this->RemoteServer."?x=1&dcmshost=".$this->LocalServer."&dcmsuri=".$DisplayPage;	
			//$this->url_get_contents($urldetails,$DisplayPage);
			//print $urldetails."-\n\n";
			$retdata=$this->url_get_contents($urldetails);
			$this->WriteCacheFile($DisplayPage,$retdata);
			return $retdata;
		
		}
		
		function DisplayHTML($DisplayPage){
			
			//$DisplayPage=urlencode($DisplayPage);
			
			if($this->IsValidFile($DisplayPage)){
				//$DisplayPage=urlencode($DisplayPage);
				$DisplayPage=$DisplayPage;
				
				if(!$this->CheckIfCacheExists($DisplayPage)){
					
					//print "-No File-l".$DisplayPage."l-\n\n"."-\n\n";
					if($this->RequestUnCachedFiles){
						print $this->DisplayRealtime($DisplayPage);
					}else{
						//echo"404"."-\n\n";	
					}
					//print "New Data\n\n"."-\n\n";
				}else{
					$this->DisplayCacheFile($DisplayPage);
					//print "Retrieved From Cache\n\n";
				}
			}
		}
		
		function CommandInterface($DisplayPage){
			//if(eregi("update=",$DisplayPage)){
			if(strpos("update=",$DisplayPage)){
				if($_SERVER['REMOTE_ADDR']==$this->RemoteServerIP){
					$this->RequestUpdate($_GET['update']);
				}else{
					//echo "Invalid Requestor\n\n";	
				}
			}else{
				$this->DisplayHTML($DisplayPage);
			}
		}
	}



?>
