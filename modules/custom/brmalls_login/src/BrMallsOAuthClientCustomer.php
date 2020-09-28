<?php 

/**
 * @file
 * Contains brMalls Customer class.
 */

/**
 * @file
 * This class represents configuration for customer.
 */
namespace Drupal\brmalls_login;

 
class BrMallsOAuthClientCustomer {
    public static $username;
    public static $password;
    public static $email;
    public static $group;
    public static $customerKey;
    public static $transactionId;
    public static $accessToken;
 
    public static $key;
    public static $secret;
    public static $type;
    public static $base64User;
    public static $host;

    public static $userid;


    
    /**
     * Constructor.
     */
    public static function setConfig() {
        self::$key = "B5ElDRm2TPQWNa3LCPop_i0EK2wa";
        self::$secret = "UHmjCiVKssdFFVZivKSS909XGTQa";
        self::$type = "AD";
        self::$host = "https://api-gtw.integracao.brmalls.com.br";
    }

    /**
     * getAccessToken
     * @return String || False
     *   return string Token or false value in case the error 
     */
    public static function getAccessToken(){
            try{
                
   
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL =>  self::$host."/token?grant_type=client_credentials" ,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                    "X-Authorization:  Basic base",
                    "Authorization: Basic ".base64_encode(self::$key.':'.self::$secret)
                ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
    
                $obj = json_decode($response);
                
                if(isset($obj->access_token) && !empty($obj->access_token)){
                    // ok - we find us the token . now, we can pass to controller
                    return $obj->access_token;
                }else{
                    return false;
                }


            }catch(Exception $e){
                return false;
            }


    }

    /**
     * Public function authenticate
     */
    public static function authenticate($username, $pass){
        try{ 
            self::setConfig();
            if(self::$accessToken = self::getAccessToken()){
                
                self::$username = $username;
                self::$password = $pass;

                self::$base64User = base64_encode(self::$username.':'.self::$type.':'.self::$password );
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => self::$host."/identity-server/1.3.0/is/authenticate",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                    "X-Authorization: Basic ". self::$base64User,
                    "Accept-language: pt-BR",
                    "Authorization: Bearer ". self::$accessToken
                ),
                ));
        
                $response = curl_exec($curl);
                curl_close($curl);
    
                $obj = json_decode($response);

                
                
                if(isset($obj->token) or !empty($obj->token)){
                    // ok - we find the user . now, we get the data of groups 
                    
                    if($usergroup = self::getGroupInfo(self::$username)){                                             
                        return $usergroup;
                    }else{
                        return json_decode('{"success": false}');
                    };

                }else{
                    return json_decode('{"success": false}');
                }
        
            }else{
                return json_decode('{"success": false}');
            }
        }catch(Exception $e){
            return false;
        }

    }

    /**
     * getGroupInfo 
     */
    public static function getGroupInfo($username){       

        try{

            // check if username exist 
            if(!isset($username) or empty($username)){
                return false;
            }

            

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => self::$host . "/identity-server/1.3.0/ad-api/api/v2/user/".$username."/groups",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "X-Authorization: Basic " . self::$base64User ,
                "Accept-language: pt-BR",
                "Authorization: Bearer " . self::$accessToken
            ),
            ));
            
            $response = curl_exec($curl);

           

            curl_close($curl);

            $obj = json_decode($response);

                        
            if(isset($obj->success) && !empty($obj->success)){
                // ok - we find the user . now, we get the data of groups 
                return $obj;
            }else{
                return false;
            }

        }catch(Exception $e){
            return false;
        }

    }

    public static function getGroupShopping($userid){

        self::$userid = $userid;
        $vid = 'field_shopping';
        $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);

       

        foreach ($terms as $term) {
            $term_data[] = array(
            'id' => $term->tid,
            'name' => $term->name
            );
        }
    }

}