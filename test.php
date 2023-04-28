<?php
    $filename = "/home/hostin16/public_html/test4.txt";
    $content="Hello World 3";
    
    print "-0030\n\n".$filename."-\n\n";
    //if (is_writable($filename)) {
        $fh = fopen($filename, "w");
        fwrite($fh, $content);
        fclose($fh);
        /*
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
        */
    //} else {
    //    print "-003111\n\n".$filename." Not Writable-\n\n";
        //$this->Error("The file $filename is not writable");
        //echo $filename." - The file $filename is not writable"."-\n\n";
    //}
    
    /*
    if(!file_exists($filename)) {
        
        $fh = fopen($filename, "w");
        fwrite($fh, $content);
        fclose($fh);

        print "-0055111\n\n".$filename." Writen-\n\n";
    }else{
        print "-0066111\n\n".$filename." Already There-\n\n";
    }
    */

?>