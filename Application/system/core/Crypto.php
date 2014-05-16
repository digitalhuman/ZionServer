<?php

/**
 * Description of Crypto
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

class Crypto {
    
    public function __construct() {
    }
    
    /**
     * Encrypt data with public key
     * @param type $data
     * @return boolean
     */
    public function encrypt($data = ""){
        if(openssl_pkey_get_public(file_get_contents(PEM_FILENAME)) !== FALSE){
            
            //Get the public key
            $publicKey = openssl_pkey_get_public(file_get_contents(PEM_FILENAME));
            
            //Decrypt
            if(openssl_public_encrypt($data, $encrypted, $publicKey) !== FALSE){
                return base64_encode($encrypted);
            }else{
                log_message("Erro while encrypting message");
                return false;
            }
        }else{
            log_message("Error while getting public key");
        }
        return false;
    }
    
    /**
     * Decrypt data with private key
     * @param type $data
     * @return boolean
     */
    public function decrypt($data = ""){
        //This needs to be like this.
        if(openssl_get_privatekey(file_get_contents(APPPATH."/ssl_server.key"), PEM_PASSPHRASE) !== FALSE){
            
            //Get the key to work with
            $privateKey = openssl_get_privatekey(file_get_contents(APPPATH."/ssl_server.key"), PEM_PASSPHRASE);
            
            //Encrypt with our private key
            if(openssl_private_decrypt(base64_decode($data), $decrypted, $privateKey) !== FALSE){
                return $decrypted;
            }else{
                echo "Error while decrypting data\n";
                log_message("Error while decrypting data");
                return false;
            }
            
        }else{
            log_message("Error while getting PrivateKey");
            echo "Error while getting PrivateKeyn\n";
        }
        return false;
    }
    
    /**
     * Generate a private key file, and a certificate with public key
     * @return boolean
     */
    public function generate_keys_and_certificate(){
        //Generate new key
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "x509_extensions" => "v3_ca"
        );
        $privateKey = openssl_pkey_new($config);

        //Get public key
        $details = openssl_pkey_get_details($privateKey);
        $publicKey = $details['key'];

        //Get private key
        openssl_pkey_export($privateKey, $privkey);

        $dn = array(
            "countryName" => "UK",
            "stateOrProvinceName" => "Sweden",
            "localityName" => "Sweden",
            "organizationName" => "Ignite the Future",
            "organizationalUnitName" => "ZionServer",
            "commonName" => "ZionServer",
            "emailAddress" => "support@ignitethefuture.nl"
        );
        $csr = openssl_csr_new($dn, $privateKey);
        if(($sscert = openssl_csr_sign($csr, null, $privateKey, 365)) !== false){
            if(openssl_csr_export($csr, $csrout) !== false){
                if(openssl_x509_export($sscert, $certificate) !== false){

                }else{
                    log_message("Error while exporting Certificate");                        
                    openssl_x509_free($sscert);
                }                    
            }else{
                log_message("Error while exporting signed request.");
                $certificate = 'null';
                openssl_x509_free($sscert);
            }
        }else{
            log_message("Error while creating certificate for this user.");
            $certificate = 'null';
        }

        $result = array(
            "certificate" => $certificate,
            "private_key" => $privateKey,
            "public_key"  => $publicKey
        );

        openssl_pkey_free($privateKey);
        openssl_pkey_free($privateKey);

        return $result;

    }
    
}
