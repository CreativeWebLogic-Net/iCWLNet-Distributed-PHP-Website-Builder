<?php
	class DistributedCMS{
		var $ConfigServer="https://git.creativeweblogic.net/Server-Config-File.html";
		var $RemoteServer="access.sitemanage.info/";
		var $BaseCacheDirectory="cache/";
		var $BaseDomainCacheDirectory="../cache/";
      	var $DomainCacheDirectory="";
		var $current_dir="";
		var $current_back_dir="";
		var $LocalServer;
		var $Current_File="";
		var $Current_Full_Cached_File="";
		var $RequestUnCachedFiles=true;
		var $RemoteServerIP="142.132.144.12";
		var $ForbiddenExtensions=array();
		var $useragent="curl";
		var $cookieFile = "cookies.txt";
		var $guid="";
		var $domain_folders=array("html","images","linked","cookies");
		var $domain_folder_index="";
		var $file_extensions=array();
		var $current_file_extension=array();
		var $Current_Full_Directories=array();
		var $Current_Full_Files=array();
		var $Current_Full_File_Index=0;
		var $error_count=0;
		
		function __construct(){
          	$this->create_domain_folders();
		}
		
		function create_domain_folders() 
		{ 
			$this->create_file_extensions();
          	          
          	if (!file_exists($this->DomainCacheDirectory)) {	
              	if(!mkdir($this->DomainCacheDirectory)){
                	$this->Error("\n 00111 error -|-".$this->DomainCacheDirectory."-|-\n\n",9);
                	//echo "error".$current_folder."-\n\n";
              	}else{
                  	//$this->Error("\n 00111234 success -|-".$this->DomainCacheDirectory."-|-\n\n",9);
              	}
            }else{
             	//$this->Error("\n 00111222 directory exists error -|-".$this->DomainCacheDirectory."-|-\n\n",6); 
            }
          
          	foreach($this->domain_folders as $key=>$val){
				if($this->BaseDomainCacheDirectory=="../cache/"){
					$this->create_cache_variables();
				}
				$current_folder=$this->BaseDomainCacheDirectory.$val;
              	//$this->Error("\n 00007 create_domain_folders |".$this->BaseDomainCacheDirectory."-Folder-".$current_folder."\n\n",6);
				if (!file_exists($current_folder)) {
					if(!mkdir($current_folder)){
						$this->Error("\n 001 error -|-".$current_folder."-|-\n\n",9);
						//echo "error".$current_folder."-\n\n";
					}else{
						$this->Current_Full_Directories[$key]=$current_folder;
						//$this->Error("=20011=".$current_folder."-20011-\n\n",1);
						//print "=20011=".$current_folder."-20011-\n\n";
					}
				}
			}
		}
		
		function create_file_extensions() 
		{ 
			$this->create_cache_directories();
          	$this->file_extensions[$this->domain_folders[0]]=array("html","htm","php","py","pl","ci","aspx","/");
			$this->file_extensions[$this->domain_folders[1]]=array("jpg","png","gif","svg","tiff","eps","psd","ico");
			$this->file_extensions[$this->domain_folders[2]]=array("css","js","xml","txt","csv");
			$this->file_extensions[$this->domain_folders[3]]=array("txt");
			$this->Current_Full_File_Index=0;
		}
		
		function create_cache_directories() 
		{ 
			$this->get_app_directory();
          	$this->create_server_constants();
          	$this->DomainCacheDirectory=$this->CacheDirectory().$this->LocalServer;
			$this->BaseDomainCacheDirectory=$this->CacheDirectory().$this->LocalServer."/";
          	$this->Error("\n 002 Cache Directories -|-".$this->DomainCacheDirectory."-|-".$this->BaseDomainCacheDirectory." | -002 \n\n",2);
			$this->create_server_constants();
		}
		
		function get_app_directory() 
		{ 
			$current_dir=pathinfo(__DIR__);
			$this->current_back_dir=$current_dir["dirname"].'/';
			$this->current_dir=$current_dir['dirname'].'/'.$current_dir['basename']."/";
		}
		
		function create_server_constants() 
		{ 
			$this->error_count=0;
			$this->Current_File=$_SERVER['REQUEST_URI'];//$_SERVER['REQUEST_URI']
			$this->LocalServer=$_SERVER['HTTP_HOST'];
			$this->Error("\n Base Directory |".$this->BaseDomainCacheDirectory."-URI-".$this->Current_File."\n\n");
			
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
			//$this->Error("=299=-\n\n".var_export($_SESSION)."=/200=-\n\n");
			if(!isset($_SESSION['guid'])){
				$this->guid=$this->make_guid();
				$_SESSION['guid']=$this->guid;
			}else{
				$this->guid=$_SESSION['guid'];
			}
			
			//print "=200=-\n\n";
			//print_r($this->Current_Full_Directories);
			//print "=/200=-\n\n";
			//$this->Error("=200=-\n\n".var_export($this->Current_Full_Directories)."=/200=-\n\n");
			//$this->cookieFile = "cache/cookies/".$this->guid."-cookies.txt";
			if(isset($this->Current_Full_Directories["cookies"])){
				$this->cookieFile =$this->Current_Full_Directories["cookies"].$this->guid."-cookies.txt";
				if(!file_exists($this->cookieFile)) {
					$fh = fopen($this->cookieFile, "w");
					fwrite($fh, "");
					fclose($fh);
				}
			}else{
				//$this->Error("=000003=-\n\n".var_export($this->Current_Full_Directories)."=000003=-\n\n");
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
			//print "-00123\n\n".$DisplayPage."-\n\n";
			$ret_val=false;
			$BSlashEncoded='/';
			$end_of_string=substr($DisplayPage,strlen($DisplayPage)-strlen($BSlashEncoded));
			
			if($end_of_string==$BSlashEncoded){
				$ret_val=true;
				$this->Error("-00123 true\n\n".$DisplayPage."-eos-".$end_of_string."-encoded-".$BSlashEncoded."-rv-".var_dump($ret_val)."\n\n");
				//print "-00123 true\n\n".$DisplayPage."-eos-".$end_of_string."-encoded-".$BSlashEncoded."-rv-".var_dump($ret_val)."\n\n";
			}else{
				$ret_val=false;
				$this->Error("-00123 false\n\n".$DisplayPage."-eos-".$end_of_string."-encoded-".$BSlashEncoded."-rv-".var_dump($ret_val)."\n\n");
				//print "-00123 false\n\n".$DisplayPage."-eos-".$end_of_string."-encoded-".$BSlashEncoded."-rv-".var_dump($ret_val)."\n\n";
			}
			return $ret_val;
		}
		
		function CheckFilesDuplicates($DisplayPage){
			//print "-00123\n\n".$DisplayPage."-\n\n";
			$ret_val=false;
			foreach($this->Current_Full_Files as $current_index=>$values){
				if($values["filename"]==$DisplayPage){
					$ret_val=true;
				}else{
					$ret_val=false;
				}
				
			}
			return $ret_val;
		}
		
		function Set_Full_Files($dimensions_array=array(),$files_index=0){
			if(!$this->CheckFilesDuplicates($dimensions_array["filename"])){
				if(count($dimensions_array)>0){
					$current_index=count($this->Current_Full_Files);
					$this->Current_Full_File_Index=$current_index;
					$this->Current_Full_Files[$current_index]["filename"]=$dimensions_array["filename"];
					$this->Current_Full_Files[$current_index]["encoded_filename"]=$dimensions_array["encoded_filename"];
					$this->Current_Full_Files[$current_index]["extension"]=$dimensions_array["extension"];
					$this->Current_Full_Files[$current_index]["extension_type"]=$dimensions_array["extension_type"];
					$this->Current_Full_Files[$current_index]["directory"]=$dimensions_array["directory"];
					$this->Current_Full_Files[$current_index]["complete_cache_location"]=$dimensions_array["directory"].$dimensions_array["encoded_filename"];
					return false;
				}else{
					$this->Current_Full_File_Index=$files_index;
					return $this->Current_Full_Files[$files_index];
				}
			}else{
				return false;
			}
			
		}
		
		function CheckFileDestination($DisplayPage){
			//print "-00123\n\n".$DisplayPage."-\n\n";
			$ret_val=false;
			
			foreach($this->file_extensions as $key=>$val){
				//$this->Error("-00123\n\n".$key."-".var_export($val,true)."|\n\n",0);
				//print "-00123\n\n".$key."-".var_export($val,true)."|\n\n";
				
				foreach($val as $ext_key=>$extension){
					
					//$extension="/";
					$end_of_string=substr($DisplayPage,strlen($DisplayPage)-strlen($extension));
					//print "-0010002\n\n".$end_of_string."-\n\n";
					
					if($end_of_string==$extension){
						$array_dims["filename"]=$DisplayPage;
						$array_dims["encoded_filename"]=$this->slash_wrap($DisplayPage);
						$array_dims["extension"]=$extension;
						$array_dims["extension_type"]=$key;
						$array_dims["directory"]=$this->BaseDomainCacheDirectory.$key.$extension;
                      	$array_dims["complete_cache_location"]=$this->BaseDomainCacheDirectory.$key.$array_dims["encoded_filename"];
						
						$this->Set_Full_Files($array_dims);
						
						$ret_val=true;
					}else{
						if(!$ret_val) $ret_val=false;
					}
					
					
				}
				
			}
			
			//$this->Error("-001234666\n\n".$key."-".var_export($this->Current_Full_Files,true)."|\n\n");
			//print "-001234\n\n".$key."-".var_export($this->Current_Full_Files,true)."|\n\n";
			//print "-0010002\n\n".var_export($this->Current_Full_Files)."-".$ret_val."\n\n";
			return $ret_val;
		}
		
		function LocalFileName($DisplayPage){
			//print "-0010101\n\n".$DisplayPage."-\n\n";
			if($this->CheckFileDestination($DisplayPage)){
				$filename =$this->Current_Full_Files[$this->Current_Full_File_Index]["encoded_filename"];
			}else{
				$filename ="404 Error";
              	header("Location: /404.shtml");
              	exit();
			}
			
			$this->Current_Full_Cached_File=$filename;
			//$this->Error("\n\n002123-".var_export($this->Current_Full_Files)."-\n\n");
			//print "\n\n002123-".var_export($this->Current_Full_Files)."-\n\n";
			return $filename;
		}
		
		function url_get_contents($url){//,$DisplayPage) {
			//print $url;
			$this->Error("\n 6679 The file | ".$url." | content legth \n",1);
			
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
			$this->Error("\n 667 The file | ".strlen($result)." | content legth \n",1);
			return $result;
		}
		
		function WriteCacheFile($DisplayPage,$content){
			//$DisplayPage="xxx";
			$filename ="";
          	$filename =$this->Current_Full_Files[$this->Current_Full_File_Index]["complete_cache_location"];
			if(strlen($content)>0){
              	if (!$fh = fopen($filename, "w")) {
                     $this->Error("\n 666003 The file | ".$filename." | Cannot open file",1);
                     exit;
                }else{
                	fwrite($fh, $content);
					fclose($fh);
                  	$this->Error("\n 006662 The file written | ".$filename." | content size | ".strlen($content)." | ",1);
                }
				//$filename = $this->LocalFileName($DisplayPage);
				//$fh = fopen($filename, "w");
				
			}else{
				$this->Error("\n 666 The file | ".$filename." | no content",1);
			}
			
			/*
			print "-0030\n\n".$filename."-\n\n";
			if (is_writable($filename)) {
				if (!$handle = fopen($filename, 'x')) {
					print "-0030\n\n".$filename."-not opened\n\n";
					 $this->Error("Cannot open file ($filename)");
					 exit;
				}else{
					print "-00302\n\n".$filename."-\n\n";
				}
				if (fwrite($handle, $content) === FALSE) {
					print "-0031\n\n".$filename."-not written\n\n";
					$this->Error("Cannot write to file ($filename)");
					exit;
				}else{
					print "-00311\n\n".$filename."-written\n\n";
				}
				fclose($handle);
			} else {
				print "-003111\n\n".$filename." Not Writable-\n\n";
				//$this->Error("The file $filename is not writable");
				//echo $filename." - The file $filename is not writable"."-\n\n";
			}
			*/
		}
		
		function CheckIfCacheExists($DisplayPage){
			$filename = $this->LocalFileName($DisplayPage);
			//print "-3-".$filename."--";
			$this->Error("-3-".$filename."--");
			/*
			if(file_exists($filename)){
				return true;
			}else{
				return false;
			}
			*/
			if($filename!=""){
				if(file_exists($filename)){
					if(filesize($filename)!=0){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function DisplayCacheFile($DisplayPage){
			$filename = $this->LocalFileName($DisplayPage);
			print "\n-DCacheFile=".$filename."\n";
			if($this->CheckIfCacheExists($DisplayPage)){
				
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
			}
			
			
			//print $ContType;
		}
		
		function Error($error_text,$error_type=-1){
			$line_number=__LINE__;
			$this->error_count++;
			$pval="\n | ".$this->error_count." | ".$line_number."-|-".$error_text."-|-".$error_type.=" | \n";
			if($error_type>6){
				print $pval;
			};
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
				//$DisplayPage=$DisplayPage;
				
				if(!$this->CheckIfCacheExists($DisplayPage)){
					
					//print "-No File-l".$DisplayPage."l-\n\n"."-\n\n";
					if($this->RequestUnCachedFiles){
						print $this->DisplayRealtime($DisplayPage);
					}else{
						//echo"404"."-\n\n";	
					}
					$this->Error("1234 New Data Page | ".$DisplayPage."\n\n"."-\n\n");
					//print "1234 New Data Page | ".$DisplayPage."\n\n"."-\n\n";
				}else{
					$this->DisplayCacheFile($DisplayPage);
					$this->Error("Retrieved From Cache\n\n");
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