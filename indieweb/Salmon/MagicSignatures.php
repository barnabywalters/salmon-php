<?php 
/**
 * libmagicsignature
 * 
 * This is a very limited version of Magic Signature defined in  
 * http://salmon-protocol.googlecode.com/svn/trunk/draft-panzer-magicsig-00.html
 * It caters only the JSON format ones. 
 * License: GPL v.3
 * 
 * @author Nat Sakimura (http://www.sakimura.org/) 
 * @version 0.5
 * @create 2010-05-09
**/

namespace indieweb\Salmon;

// Utility functions
function base64url_encode($input,$nopad=1,$wrap=1)
{
    $data  = base64_encode($input);

    if($nopad) {
        $data = str_replace("=","",$data);
    }
    $data = strtr($data, '+/=', '-_,');
    if ($wrap) {
        $datalb = ""; 
        while (strlen($data) > 64) { 
            $datalb .= substr($data, 0, 64) . "\n"; 
            $data = substr($data,64); 
        } 
        $datalb .= $data; 
        return $datalb; 
    } else {
        return $data;
    }
}

function base64url_decode($input)
{
    return base64_decode(strtr($input, '-_,', '+/='));
}

class MagicSignatures
{
	/** 
	 * Creating Magic Envelope Signature
	 * @param  String $file     Data to be signed.  
	 * @param  String $datatype MIME type of $file
	 * @param  String $pemfile  Filename of the PEM file that has signing key
	 * @param  String $pass     The password for $pemfile 
	 * @return String Magic Signature in JSON format
	 */
	public function sign($file, $datatype, $pemfile, $pass){
			$data = base64url_encode($file);
			$m = $data . base64url_encode($datatype) . ".base64url.RSA-SHA256";
			// echo "\n========= M ==========\n" . $m . "\n";
			$hash = hash("sha256",$m);
			// Get Private Key
			$fp=fopen($pemfile,"r"); 
			$priv_key=fread($fp,8192); 
			fclose($fp); 
	
			$res = openssl_get_privatekey($priv_key,$pass); 
			openssl_private_encrypt($hash,$bsig,$res); 
	
			$arr=array("data"=>$data,"data_type"=>$datatype,
			"encoding"=>"base64url",
			"alg"=>"RSA-SHA256",
			"sigs"=>
			array(  "value"=>base64url_encode($bsig),
					"keyhash"=>$hash)
			);
			return json_encode($arr);
	}
	
	/** 
	 * Verifying the magic signature
	 * @param  String $data    JSON formatted  Magic Signautre data
	 * @param  String $pemfile The filename of the PEM with public key of the signer
	 * @return true if the signature is valid. false if not. 
	 */
	
	public function verify($data, $pemfile){ 
			$fp=fopen ($pemfile,"r"); 
			$pub_key=fread($fp,8192); 
			fclose($fp); 
			openssl_get_publickey($pub_key); 
			$arr=json_decode($data,true);
			// print_r($arr);
			$sigs=$arr["sigs"];
			$value=$sigs["value"];
			openssl_public_decrypt(base64url_decode($value),$nhash,$pub_key); 
	
			// Compute Hash from data. 
			$m = $arr["data"] . base64url_encode($arr["data_type"]) . ".base64url.RSA-SHA256";
	
			$chash = hash("sha256",$m);
			if($debug=1){
					echo "\n" . $m . "\n";
					echo "\nvalue  :" . $sigs["value"];
					echo "\nkeyhash:" . $sigs["keyhash"];
					echo "\nnewhash:" . $nhash;
					echo "\nchash  :" . $chash;
					echo "\n\n";
			}
	
			// Hash Must Match
			if ($chash==$nhash && $nhash==$sigs["keyhash"]){
					return true;
			} else {
					return false;
			}
	}
}

// EOF