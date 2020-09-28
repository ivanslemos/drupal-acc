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
    class UserResource
    {
        public static function getResourceOwner($resourceownerdetailsurl, $access_token){
            $ch = curl_init($resourceownerdetailsurl);
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch, CURLOPT_ENCODING, "" );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
            curl_setopt( $ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$access_token
            ));
            $t_vers = curl_version();
            curl_setopt( $ch, CURLOPT_USERAGENT, 'curl/' . $t_vers['version']);
            $content = curl_exec($ch);
            if(curl_error($ch)){
                exit( curl_error($ch) );
            }
            if(!is_array(json_decode($content, true))) {
                exit("Invalid response received.");
            }
            $content = json_decode($content,true);
            if(isset($content["error_description"])){
                if(is_array($content["error_description"]))
                    print_r($content["error_description"]);
                else
                    echo $content["error_description"];
                exit;
            } else if(isset($content["error"])){
                if(is_array($content["error"]))
                    print_r($content["error"]);
                else
                    echo $content["error"];
                exit;
            }
            return $content;
        }
    }
?>