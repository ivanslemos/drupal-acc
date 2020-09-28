<?php
namespace Drupal\oauth_login_oauth2;

class mo_saml_visualTour {

    public static function genArray($overAllTour = 'tabTour'){
        $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $exploded = explode('/', $link);
        $getPageName = end($exploded);
        $Tour_Token = \Drupal::config('oauth_login_oauth2.settings')->get('mo_saml_tourTaken_' . $getPageName);
        if($overAllTour == 'overAllTour'){
            $getPageName = 'overAllTour';
        }
        //\Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('mo_saml_tourTaken_' . $getPageName, TRUE)->save();
        $moTourArr = array (
            'pageID' => $getPageName,
            'tourData' => mo_saml_visualTour::getTourData($getPageName),
            'tourTaken' => $Tour_Token,
            'addID' => mo_saml_visualTour::addID(),
            'pageURL' => $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        );

        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('mo_saml_tourTaken_' . $getPageName, TRUE)->save();
        $moTour = json_encode($moTourArr);
        return $moTour;
    }

    public static function addID()
    {
        $idArray = array(
            array(
                'selector'  =>'li.tabs__tab:nth-of-type(1)',
                'newID'     =>'mo_vt_oauth_client_config',
            ),
            array(
                'selector'  =>'li.tabs__tab:nth-of-type(2)',
                'newID'     =>'mo_vt_oauth_client_mapping',
            ),
            array(
                'selector'  =>'li.tabs__tab:nth-of-type(3)',
                'newID'     =>'mo_vt_oauth_client_signin',
            ),
            array(
                'selector'  =>'li.tabs__tab:nth-of-type(4)',
                'newID'     =>'mo_vt_oauth_client_upgrade',
            ),
        );
        return $idArray;
    }
    public static function getTourData($pageID)
    {

        $tourData = array();
        $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $exploded = explode('/', $link);
        $getPageName = end($exploded);
        $Tour_Token = \Drupal::config('oauth_login_oauth2.settings')->get('mo_saml_tourTaken_' . $getPageName);

        if($Tour_Token == 0 || $Tour_Token == FALSE)
            $tab_index = 'idp_setup';
        else $tab_index = 'idp_tab';

            $tourData['config_clc'] = array(
                0 => array(
                    'targetE'       => 'miniorange_oauth_client_app',
                    'pointToSide'   => 'left',
                    'titleHTML'     => '<h1>Step 1: Select Application</h1>',
                    'contentHTML'   => 'Please select your OAuth server to configure. Select Custom OAuth if your server not listed.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                1 => array(
                    'targetE'       => 'mo_oauth_guide_vt',
                    'pointToSide'   => 'right',
                    'titleHTML'     => '<h1>Documentation</h1>',
                    'contentHTML'   => 'Click here to see the detailed documentation of OAuth Server. Click on Next to proceed ahead.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                2 => array(
                    'targetE'       => 'mo_vt_callback_url',
                    'pointToSide'   => 'left',
                    'titleHTML'     => '<h1>Step 2: Configure OAuth Server</h1>',
                    'contentHTML'   => 'Provide this <b>Callback/Redirect URL</b> to OAuth Server to configure with Drupal.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                3 => array(
                    'targetE'       => 'mo_vt1_add_data',
                    'pointToSide'   => 'left',
                    'titleHTML'     => '<h1>Configure Drupal as OAuth Client</h1>',
                    'contentHTML'   => 'Enter the application name so that the login link will appear on the user login page in this manner.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                4 => array(
                    'targetE'       => 'mo_vt_add_data',
                    'pointToSide'   => 'left',
                    'titleHTML'     => '<h1>Enter Client ID and Client Secret</h1>',
                    'contentHTML'   => 'Enter Client ID and Client Secret to configure with OAuth Server. You can get these details from OAuth Server.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                5 => array(
                    'targetE'       => 'mo_vt_add_data2',
                    'pointToSide'   => 'left',
                    'titleHTML'     => '<h1>Scope</h1>',
                    'contentHTML'   => 'Scope decides the range of data that comes from your OAuth Server.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                6 => array(
                    'targetE'       => 'mo_vt_add_data4',
                    'pointToSide'   => 'left',
                    'titleHTML'     => '<h1>Endpoints</h1>',
                    'contentHTML'   => 'The endpoints from your OAuth Server will be used during OAuth SSO login.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                7 => array(
                    'targetE'       => 'mo_vt_add_data3',
                    'pointToSide'   => 'left',
                    'titleHTML'     => '<h1>Enable login with OAuth</h1>',
                    'contentHTML'   => 'Enable the checkbox if you want to enable SSO login with OAuth.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                8 => array(
                    'targetE'       => 'button_config',
                    'pointToSide'   => 'left',
                    'titleHTML'     => '<h1>Save Settings</h1>',
                    'contentHTML'   => 'You can save your configurations by clicking on this button.',
                    'ifNext'        => true,
                    'buttonText'    => 'Next',
                    'cardSize'      => 'big',
                ),
                9 => array(
                    'targetE'       => 'edit-miniorange-saml-idp-support-side-button',
                    'pointToSide'   => 'right',
                    'titleHTML'     => '<h1>Support</h1>',
                    'contentHTML'   => 'Need any help? Just send us a support request if you are facing any issues in configuration.',
                    'ifNext'        => true,
                    'buttonText'    => 'End Tour',
                    'cardSize'      => 'big',
                    'ifskip'        =>  'hidden',
                ),
            );

        $tourData['mapping'] = array(
            0 =>    array(
                'targetE'       =>  'mo_oauth_vt_attrn',
                'pointToSide'   =>  'left',
                'titleHTML'     =>  '<h1>Email Attribute</h1>',
                'contentHTML'   =>  'Please enter attribute name which holds email address here. You can find this in test configuration',
                'ifNext'        =>  true,
                'buttonText'    =>  'Next',
                'img'           =>  array(),
                'cardSize'      =>  'big',
                'action'        =>  '',
            ),
            1 =>    array(
                'targetE'       =>  'mo_oauth_vt_attre',
                'pointToSide'   =>  'left',
                'titleHTML'     =>  '<h1>Username Attribute</h1>',
                'contentHTML'   =>  'Enter the Username Attribute which holds name. You can find this in test configuration.',
                'ifNext'        =>  true,
                'buttonText'    =>  'Next',
                'img'           =>  array(),
                'cardSize'      =>  'big',
                'action'        =>  '',
            ),
            2 => array(
                'targetE'       => 'edit-miniorange-saml-idp-support-side-button',
                'pointToSide'   => 'right',
                'titleHTML'     => '<h1>Support</h1>',
                'contentHTML'   => 'Need any help? Just send us a support request if you are facing any issues in configuration.',
                'ifNext'        => true,
                'buttonText'    => 'End Tour',
                'cardSize'      => 'big',
                'ifskip'        =>  'hidden',
            ),
        );
        $tourData['licensing'] = array(
            0 =>    array(
                'targetE'       =>  'edit-miniorange-saml-idp-support-side-button',
                'pointToSide'   =>  'right',
                'titleHTML'     =>  '<h1>Want a demo?</h1>',
                'contentHTML'   =>  'Want to test any paid modules before purchasing? Just send us a request.',
                'ifNext'        =>  true,
                'buttonText'    =>  'End Tour',
                'img'           =>  array(),
                'cardSize'      =>  'big',
                'action'        =>  '',
                'ifskip'        =>  'hidden',
            ),
        );
        return $tourData[$pageID] ;
    }
}
