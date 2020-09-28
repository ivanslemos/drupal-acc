<?php
    namespace Drupal\oauth_login_oauth2;
    use Symfony\Component\HttpFoundation\Response;
    use Drupal\Component\Utility;
    use Drupal\user\Entity\User;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Drupal\Component\Render\FormattableMarkup;
    use Drupal\Core\Extension;
    use Drupal\Component\Utility\Html;
    use Drupal\oauth_login_oauth2\handler;
    class AccessToken 
    {
        /**
         * This function gets the access token from the server
         */
        public static function getAccessToken($tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body) {
            $ch = curl_init($tokenendpoint);
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch, CURLOPT_ENCODING, "" );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
            curl_setopt( $ch, CURLOPT_POST, true);

            if($send_headers && !$send_body) {

                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Basic ' . base64_encode( $clientid . ":" . $clientsecret ),
                    'Accept: application/json'
                ));
                curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&code='.$code);

            }else if(!$send_headers && $send_body){

                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Accept: application/json'
                ));
                curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&client_id='.$clientid.'&client_secret='.$clientsecret.'&code='.$code);
            }else {

                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Basic ' . base64_encode( $clientid . ":" . $clientsecret ),
                    'Accept: application/json'
                ));
                curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&client_id='.$clientid.'&client_secret='.$clientsecret.'&code='.$code);
            }


            $content = curl_exec($ch);
            if(curl_error($ch)){
                echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
                exit( curl_error($ch) );
            }
            if(!is_array(json_decode($content, true))){
                echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
                exit("Invalid response received.");
            }
            $content = json_decode($content,true);

            if (isset($content["error"])) {
                if (is_array($content["error"])) {
                    $content["error"] = $content["error"]["message"];
                }
                exit($content["error"]);
            }
            else if(isset($content["error_description"])){
                exit($content["error_description"]);
            }
            else if(isset($content["access_token"])) {
                $access_token = $content["access_token"];
            } else {
                exit('Invalid response received from OAuth Provider. Contact your administrator for more details.');
            }
            return $access_token;
        }        
    }

?>