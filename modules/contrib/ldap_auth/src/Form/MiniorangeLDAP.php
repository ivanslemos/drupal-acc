<?php

/**
 * @file
 * Contains \Drupal\miniorange_ldap\Form\MiniorangeConfigOAuthClient.
 */

namespace Drupal\ldap_auth\Form;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ldap_auth\MiniorangeLDAPSupport;
use Drupal\Core\Form\FormBase;
use Drupal\ldap_auth\handler;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\ldap_auth\Utilities;
use Drupal\Component\Utility\Html;

class MiniorangeLDAP extends FormBase{
  public function getFormId() {
    return 'miniorange_ldap_config_client';
  }
  public function buildForm(array $form, FormStateInterface $form_state){
      global $base_url;
    \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_disabled', FALSE)->save();
    $attachments['#attached']['library'][] = 'ldap_auth/ldap_auth.admin';
    $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "ldap_auth/ldap_auth.admin",
            )
        ),
    );
    $status='';
    $status= \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_config_status');
    if($status=='')
      $status = 'two';

    $form['#prefix'] = '<div class="mo_ldap_table_layout_1"><div class="mo_ldap_table_layout container">';
    

    if($status=='review_config'){
        $form['miniorange_ldap_enable_ldap_markup'] = array(
        '#markup' => "<h1><b>Login With LDAP</b></h1><hr><br>",
        );
        $form['miniorange_ldap_enable_ldap'] = array(
            '#type' => 'checkbox',
            '#title' => t('Enable Login with LDAP '),
            '#default_value' => \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_ldap'),
        );
        $form['set_of_radiobuttons']['miniorange_ldap_authentication'] = array(
            '#type' => 'radios',
            '#disabled' => true,
            '#title' => t('Authentication restrictions: <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#description' => t('Only particular personalities will be able to login by selecting the above option.'),
            '#tree' => TRUE,
            '#default_value' => is_null(\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_authentication'))?2:\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_authentication'),
            '#options' => array(0 => t('Drupal and LDAP Users'), 1 => t('Administrators of Drupal'), 2 => t('LDAP Users')),
        );
        $form['miniorange_ldap_enable_auto_reg'] = array(
            '#type' => 'checkbox',
            '#disabled' => 'true',
            '#title' => t('Enable Auto Registering users if they do not exist in Drupal <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_auto_reg'),
        );
        $form['ldap_server'] = array(
            '#markup' => "<br><br>
                <strong>Note: </strong>You need to find out the values of the below given field from your LDAP administrator</strong><br><br>
                <h1><b>LDAP Connection Information</b></h1><hr><br>",
        );
        $form['miniorange_ldap_server'] = array(
            '#type' => 'textfield',
            '#title' => t('LDAP Server'),
            '#id' => 'mo_ldap_server',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server'),
            '#description' => "Specify the host name for the LDAP server eg: ldap://myldapserver.domain:389 , ldap://89.38.192.1:389. When using SSL, the host may have to take the form ldaps://host:636",
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'ldap://<server-address or IP>:<port>'),
        );
        $form['miniorange_ldap_contact_server_button'] = array(
            '#type' => 'submit',
            '#value' => t('Contact LDAP Server'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::test_ldap_connection'),
        );
        $form['miniorange_ldap_enable_tls'] = array(
            '#prefix' => '<br>',
            '#type' => 'checkbox',
            '#disabled' => true,
            '#id' => 'check',
            '#title' => t('Enable TLS (Check this only if your server use TLS Connection)'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_tls'),
        );
        global $base_url;
        $form['miniorange_ldap_server_account_username'] = array(
            '#type' => 'textfield',
            '#title' => t('Service Account Username:'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username'),
            '#description' => "This service account username will be used to establish the connection. Specify the Service Account Username of the LDAP server in the either way as follows Username@domainname or domainname\Username. or Distinguished Name(DN) format",
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'CN=service,DC=domain,DC=com'),
        );
        $form['miniorange_ldap_server_account_password'] = array(
            '#type' => 'password',
            '#title' => t('Service Account Password:'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password'),
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'Enter password for Service Account'),
        );
        $form['miniorange_ldap_test_connection_button'] = array(
            '#type' => 'submit',
            '#prefix' => '<br>',
            '#value' => t('Test Connection'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::test_connection_ldap'),
        );
        $form['troubleshooting_1'] = array(
            '#markup' => "
                <div style='background-color: white; padding: 10px ;margin-left: 20px; width: 70%' id='enable_ldap'>
                    <input type='button' style='background-color: #008CBA;border: none;color: white;padding: 8px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 12px;font-size: 16px;' value='Troubleshooting' onclick='msg()'>
                </div><br><br>",
        );
        $form['miniorange_ldap_search_base'] = array(
            '#type' => 'textfield',
            '#title' => t('Search Base(s):'),
            '#description' => 'This is the LDAP Tree under which we will search for the users for authentication. If we are not able to find a user in LDAP it means they are not present in this search base or any of its sub trees. They may be present in some other .
                Provide the distinguished name of the Search Base object. <b>eg. cn=Users,dc=domain,dc=com.
                Multiple Search Bases are supported in the Premium version of the plugin.</b>',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_search_base'),
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'DC=domain, DC=com'),
        );
        $form['miniorange_ldap_username_attribute'] = array(
          '#type' => 'textfield',
          '#title' => t('Username Attribute:'),
          '#description' => 'This field is important for two reasons.
            1. While searching for users, this is the attribute that is going to be matched to see if the user exists.
            2. If you want your users to login with their username or firstname.lastname or email - you need to specify those options in this field. e.g. <b>LDAP_ATTRIBUTE</b>. Replace <b><LDAP_ATTRIBUTE></b> with the attribute where your username is stored. Some common attributes are</p>
            <table>
                <tr>
                    <td>common name</td>
                    <td>cn</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>email</td>
                    <td>mail</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>logon name</td>
                    <td>samaccountName</td>
                    <td>or</td>
                    <td>userPrincipalName</td>
                </tr>
            </table>
            <p>You can even allow logging in with multiple attributes, separated with  <b>semicolon</b> . e.g. you can allow logging in with username or email. <strong>e.g. cn;mail</strong><br>',
          '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_username_attribute'),
          '#attributes' => array('style' => 'width:73%;','placeholder' => 'eg. cn , mail or sammaccountname, etc'),
        );
        $form['save_user_mapping'] = array(
            '#markup' => "
                <div style='background-color: white; padding: 10px ;margin-left: 20px; width: 70%' id='enable_ldap'>
                    <p>Please make clear that the attributes that we are showing are examples and the actual ones could be different. These should be confirmed with the LDAP Admin.<p>
                    <input type='button' style='background-color: #4CAF50;border: none;color: white;padding: 8px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 12px;font-size: 16px;' value='Save User Mapping' onclick='msg()'>
                </div>",
        );
        $form['troubleshooting_2'] = array(
            '#markup' => "
                <div style='background-color: white; padding: 10px ;margin-left: 20px; width: 70%' id='enable_ldap'>
                    <input type='button' style='background-color: #008CBA;border: none;color: white;padding: 8px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 12px;font-size: 16px;' value='Troubleshooting' onclick='msg()'>
                </div>",
        );
        $form['back_step_3'] = array(
            '#type' => 'submit',
            '#prefix' => "<table><tr><td>",
                '#suffix' => "</td>",
            '#value' => t('Reset Configurations'),
            '#submit' => array('::miniorange_ldap_back_2'),
            '#attributes' => array('style' => 'border-radius:4px;float:left;opacity:0.7;background: red;color: #ffffff;text-shadow: 0 -1px 1px red, 1px 0 1px red, 0 1px 1px red, -1px 0 1px red;
                box-shadow: 0 2px 0 red;border-color: red red red; width: 50%;display:block;margin-left:90px;margin-right:auto;margin-bottom:10px;'),
        );
        $form['save_config_edit'] = array(
            '#type' => 'submit',
            '#prefix' => "<td>",
            '#suffix' => "</td></tr></table>",     
            '#value' => t('Save Changes'),
            '#submit' => array('::miniorange_ldap_review_changes'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;float: right;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                    box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 50%;display:block;margin-right:130px;'),       
        );
        }
    if($status=='one'){
        $form['miniorange_ldap_enable_ldap_markup'] = array(
            '#markup' => "<h1><b>Login With LDAP Options</b></h1><hr><br>",
        );
        $form['miniorange_ldap_enable_ldap'] = array(
            '#type' => 'checkbox',
            '#description' => t('Enabling LDAP login will protect your login page by your configured LDAP. Please check this only after you have successfully tested your configuration as the default Drupal login will stop working'),
            '#title' => t('Enable Login with LDAP '),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_ldap'),
        );
        $form['set_of_radiobuttons']['miniorange_ldap_authentication'] = array(
            '#type' => 'radios',
            '#disabled' => 'true',
            '#title' => t('Authentication restrictions: <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#description' => t('Only particular personalities will be able to login by selecting the above option.'),
            '#tree' => TRUE,
            '#default_value' => is_null(\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_authentication'))?2:\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_authentication'),
            '#options' => array(0 => t('Drupal and LDAP Users'), 1 => t('Administrators of Drupal'), 2 => t('LDAP Users')),
        );
        $form['miniorange_ldap_enable_auto_reg'] = array(
            '#type' => 'checkbox',
            '#title' => t('Enable Auto Registering users if they do not exist in Drupal <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#disabled' => 'true',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_auto_reg'),
        );
        $form['back_step_3'] = array(
            '#type' => 'submit',
            '#id' => 'button_config',
            '#prefix' => "<br><table><tr><td>",
            '#suffix' => "</td>",
            '#value' => t('BACK'),
            '#submit' => array('::miniorange_ldap_back_5'),
            '#attributes' => array('style' => 'border-radius:4px;opacity:0.7;background: red;color: #ffffff;text-shadow: 0 -1px 1px red, 1px 0 1px red, 0 1px 1px red, -1px 0 1px red;
                box-shadow: 0 2px 0 red;border-color: red red red; width: 40%;display:block;margin-right:auto;margin-bottom:10px;'),
        );
        $form['next_step_1'] = array(
            '#type' => 'submit',
            '#prefix' => "<td>",
            '#suffix' => "</td></tr></table>",
            '#id' => 'button_config',
            '#value' => t('Save & Review Configurations'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;float: right;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 50%;display:block;margin-right:auto;'),
            '#submit' => array('::miniorange_ldap_next_1'),
        );
    }
    else if($status=='two'){
        $form['mo_ldap_local_configuration_form_action'] = array(
            '#markup' => "<input type='hidden' name='option' id='mo_ldap_local_configuration_form_action' value='mo_ldap_local_save_config'></input>",
        );
        $form['ldap_server'] = array(
            '#markup' => "<strong><i>Note: </strong>You need to find out the values of the below given field from your LDAP administrator</i></strong><br>
            <br><h1><b>LDAP Connection Information</b></h1><hr><br>",
        );
        $form['miniorange_ldap_server'] = array(
            '#type' => 'textfield',
            '#title' => t('LDAP Server'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server'),
            '#description' => t("Specify the host name for the LDAP server eg: ldap://myldapserver.domain:389 , ldap://89.38.192.1:389. When using SSL, the host may have to take the form ldaps://host:636"),
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'ldap://<server-address or IP>:<port>'),
        );
        $form['miniorange_ldap_contact_server_button'] = array(
            '#type' => 'submit',
            '#value' => t('Contact LDAP Server'),
            '#id' => 'button_config',
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::test_ldap_connection'),
        );
        $form['miniorange_ldap_enable_tls'] = array(
            '#prefix' => '<br>',
            '#type' => 'checkbox',
            '#id' => 'check',
            '#disabled' => 'true',
            '#title' => t('Enable TLS (Check this only if your server use TLS Connection) <b><a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[PREMIUM]</a></b>'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_tls'),
        );
        $form['miniorange_ldap_server_account_username'] = array(
          '#type' => 'textfield',
          '#title' => t('Service Account Username:'),
          '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username'),
          '#description' => t("This service account username will be used to establish the connection. Specify the Service Account Username of the LDAP server in the either way as follows Username@domainname or domainname\Username. or Distinguished Name(DN) format"),
          '#attributes' => array('style' => 'width:73%;','placeholder' => 'CN=service,DC=domain,DC=com'),
        );
        $form['miniorange_ldap_server_account_password'] = array(
            '#type' => 'password',
            '#title' => t('Service Account Password:'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password'),
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'Enter password for Service Account'),
        );
        $form['miniorange_ldap_test_connection_button'] = array(
            '#type' => 'submit',
            '#prefix' => '<br>',
            '#value' => t('Test Connection'),
            '#attributes' => array('style' => 'border-radius:4px;background: #006799;color: #ffffff;text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
            box-shadow: 0 2px 0 #006799;border-color: #006799 #006799 #006799; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::test_connection_ldap'),
        );
        $form['next_step_2'] = array(
            '#type' => 'submit',
            '#value' => t('NEXT'),
            '#attributes' => array('style' => 'border-radius:4px;float:right;opacity:0.7;background: green;color: #ffffff;text-shadow: 0 -1px 1px green, 1px 0 1px green, 0 1px 1px green, -1px 0 1px green;
            box-shadow: 0 2px 0 green;border-color: green green green; width: 20%;display:block;margin-bottom:10px;'),
            '#submit' => array('::miniorange_ldap_next_2'),
        );
    }
    else if($status=='three'){
        $form['miniorange_ldap_search_base'] = array(
            '#type' => 'textfield',
            '#title' => t('Search Base(s):'),
            '#description' => t('This is the LDAP Tree under which we will search for the users for authentication. If we are not able to find a user in LDAP it means they are not present in this search base or any of its sub trees. They may be present in some other .
                Provide the distinguished name of the Search Base object. <b>eg. cn=Users,dc=domain,dc=com.
                Multiple Search Bases are supported in the <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">Premium</a> version of the plugin.</b>'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_search_base'),
            '#attributes' => array('placeholder' => 'DC=domain, DC=com'),
        );
        $form['miniorange_ldap_username_attribute'] = array(
            '#type' => 'textfield',
            '#title' => t('Username Attribute:'),
            '#description' => t('This field is important for two reasons.
                <b>1.</b> While searching for users, this is the attribute that is going to be matched to see if the user exists.
                <b>2.</b> If you want your users to login with their username or firstname.lastname or email - you need to specify those options in this field. e.g. <b>LDAP_ATTRIBUTE</b>. Replace <b><LDAP_ATTRIBUTE></b> with the attribute where your username is stored. Some common attributes are</p>
                <table>
                    <tr>
                        <td>common name</td>
                        <td>cn</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>email</td>
                        <td>mail</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>logon name</td>
                        <td>samaccountName</td>
                        <td>or</td>
                        <td>userPrincipalName</td>
                    </tr>
                </table>
                <p>You can even allow logging in with multiple attributes, separated with  <b>semicolon(;)</b> . e.g. you can allow logging in with username or email. <strong>e.g. cn;mail</strong></p><br>'),
            '#default_value' => \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_username_attribute'),
            '#attributes' => array('placeholder' => 'eg. cn , mail or sammaccountname, etc'),
        );
        $form['back_step_3'] = array(
            '#type' => 'submit',
            '#id' => 'button_config',
            '#prefix' => "<table><tr><td>",
            '#suffix' => "</td>",
                '#value' => t('BACK'),
                '#submit' => array('::miniorange_ldap_back_3'),
                '#attributes' => array('style' => 'border-radius:4px;opacity:0.7;background: red;color: #ffffff;text-shadow: 0 -1px 1px red, 1px 0 1px red, 0 1px 1px red, -1px 0 1px red;
                box-shadow: 0 2px 0 red;border-color: red red red; width: 30%;display:block;margin-bottom:10px;'),
        );
        $form['next_step_3'] = array(
            '#type' => 'submit',
            '#id' => 'button_config',
            '#value' => t('NEXT'),
            '#prefix' => "<td>",
            '#suffix' => "</td></tr></table>",
            '#attributes' => array('style' => 'border-radius:4px;background: green;float: right;color: #ffffff;text-shadow: 0 -1px 1px green, 1px 0 1px green, 0 1px 1px green, -1px 0 1px green;
                box-shadow: 0 2px 0 green;border-color: green green green; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::miniorange_ldap_next3'),
        );
    }
    // Do Not Delete
    /* else if($status=='four'){
        $form['test_username'] = array(
            '#markup' => "
                <div style='background-color: white; padding: 10px ;margin-left: 20px; width: 70%' id='enable_ldap'>
                    <h1><b>Test Authentication</b></h1><br>
                    <p><i>Drupal username is mapped to the LDAP attribute defined in the Search Filter attribute in LDAP. Ensure that you have an administrator user in LDAP with the same attribute value.</i></p>
                </div>",
        );
        $form['miniorange_ldap_test_username'] = array(
          '#type' => 'textfield',
          '#title' => t('Username:'),
          '#prefix' => "<div class='subdiv_ui'>",
          '#suffix' => "</div>",
          '#id' => 'firstname',
          '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_test_username'),
          '#attributes' => array('placeholder' => 'Enter username for Service Account'),
        );
        $form['miniorange_ldap_test_password'] = array(
          '#type' => 'password',
          '#title' => t('Password:'),
          '#prefix' => "<div class='subdiv_ui'>",
          '#suffix' => "</div>",
          '#id' => 'firstname',
          '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_test_password'),
          '#attributes' => array('placeholder' => 'Enter password for Service Account'),
        );
        $form['back_step_3'] = array(
            '#type' => 'submit',
            '#id' => 'button_config',
            '#prefix' => "<div class='subdiv_ui'><table><tr><td>",
            '#suffix' => "</td>",
            '#value' => t('BACK'),
            '#submit' => array('::miniorange_ldap_back_5'),
            '#attributes' => array('style' => 'border-radius:4px;float:left;opacity:0.7;background: red;color: #ffffff;text-shadow: 0 -1px 1px red, 1px 0 1px red, 0 1px 1px red, -1px 0 1px red;
                box-shadow: 0 2px 0 red;border-color: red red red; width: 30%;display:block;margin-left:auto;margin-right:auto;margin-bottom:10px;'),
        );
        $form['next_review_changes'] = array(
            '#type' => 'submit',
            '#id' => 'button_config',
            '#prefix' => "<td>",
            '#suffix' => "</td></tr></table></div>",
            '#value' => t('Review/Edit Configurations'),
            '#submit' => array('::miniorange_ldap_save_changes'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;float: right;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 30%;display:block;margin-right:auto;'),
        );
    } */

    Utilities::AddSupportButton($form, $form_state);

    return $form;
  }
  function miniorange_ldap_back_1($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'one')->save();
  }
  function miniorange_ldap_back_2($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_enable_ldap')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_authenticate_admin')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_authenticate_drupal_users')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_enable_auto_reg')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_server')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_enable_tls')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_server_account_username')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_server_account_password')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_search_base')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_username_attribute')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_test_username')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_test_password')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'two')->save();
   }
    function miniorange_ldap_back_3($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'two')->save();
    }
    function miniorange_ldap_back_5($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'three')->save();
    }
    function miniorange_ldap_back_4($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'four')->save();
    }
    /**
     * Test Connection
     */
    function test_connection_ldap(){
        $server_account_username ="";
        $server_account_password ="";
        if(isset($_POST['miniorange_ldap_server_account_username']) && !empty($_POST['miniorange_ldap_server_account_username'])){
            $server_account_username = trim(Html::escape($_POST['miniorange_ldap_server_account_username']));
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_username', $server_account_username)->save();
        }
        if(isset($_POST['miniorange_ldap_server_account_password']) && !empty($_POST['miniorange_ldap_server_account_password'])){
            $server_account_password = trim(Html::escape($_POST['miniorange_ldap_server_account_password']));
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', $server_account_password)->save();
        }
        user_cookie_save(array("mo_ldap_test" => true));
        $error = array();
        $error = handler::test_mo_config($server_account_username,$server_account_password);
        if( $error[1] == "error" ) {
            if($error[0] == "Invalid Password. Please check your password and try again."){
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', '')->save();
                \Drupal::messenger()->addMessage(t( $error[0] ), "error");
            }
            elseif($error[0] == "Username or Password can not be empty"){
                \Drupal::messenger()->addMessage(t( $error[0] ), "error");
            }
            else
                \Drupal::messenger()->addMessage(t("There was an error processing your request." ), "error");
        } else if($error[1] == "Success") {
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', $server_account_password)->save();
            \Drupal::messenger()->addMessage(t('Test Connection is successful.'),'status');
        }
    }
    function miniorange_ldap_next_2($form, $form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'three')->save();
        if(!empty($form['miniorange_ldap_server']['#value'])){
            $mo_ldap_server = $form['miniorange_ldap_server']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server', $mo_ldap_server)->save();
        }
        if(!empty($form['miniorange_ldap_enable_tls']['#value'])){
            $enable_tls = $form['miniorange_ldap_enable_tls']['#value'];

            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_tls', $enable_tls)->save();
        }
        if(!empty($form['miniorange_ldap_server_account_username']['#value'])){
            $server_account_username = trim($form['miniorange_ldap_server_account_username']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_username', $server_account_username)->save();
        }
        if(!empty($form['miniorange_ldap_server_account_password']['#value'])){
            $server_account_password = trim($form['miniorange_ldap_server_account_password']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', $server_account_password)->save();
        }
    }


    function miniorange_ldap_next_3($form, $form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'four')->save();
        if(!empty($form['miniorange_ldap_search_base']['#value'])){
            $searchBase = trim($form['miniorange_ldap_search_base']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_search_base', $searchBase)->save();
        }
        if(!empty($form['miniorange_ldap_username_attribute']['#value'])){
            $usernameAttribute = trim($form['miniorange_ldap_username_attribute']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute', $usernameAttribute)->save();
        }
    }

    function miniorange_ldap_next3($form, $form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'one')->save();
        if(!empty($form['miniorange_ldap_search_base']['#value'])){
            $searchBase = trim($form['miniorange_ldap_search_base']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_search_base', $searchBase)->save();
        }
        if(!empty($form['miniorange_ldap_username_attribute']['#value'])){
            $usernameAttribute = trim($form['miniorange_ldap_username_attribute']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute', $usernameAttribute)->save();
        }
        if(!empty($form['miniorange_ldap_test_username']['#value'])){
            $testUsername = $form['miniorange_ldap_test_username']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_username', $testUsername)->save();
        }
        if(!empty($form['miniorange_ldap_test_password']['#value'])){
            $testPassword = $form['miniorange_ldap_test_password']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_password', $testPassword)->save();
        }
    }
    /**
     * Contact LDAP server
     */
    function test_ldap_connection($form,$form_state){
        global $base_url;
        $server_name ="";
        if(isset($_POST['miniorange_ldap_server']) && !empty($_POST['miniorange_ldap_server']))
            $server_name = Html::escape($_POST['miniorange_ldap_server']);
        if(empty($server_name)){
            \Drupal::messenger()->addMessage(t('LDAP Server name can not be empty'),'error');
            return;
        }
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server', $server_name)->save();
        $login_with_ldap = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_ldap');
        $ldapconn = getConnection();
        if($ldapconn){
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            $ldap_bind_dn = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username');
            $ldap_bind_password = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password');
            if(!empty(\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_tls')))
                ldap_start_tls($ldapconn);
            $bind = @ldap_bind($ldapconn, $ldap_bind_dn, $ldap_bind_password);
            $err = ldap_error($ldapconn);
            if(strtolower($err) != 'success'){
                \Drupal::messenger()->addMessage(t("There seems to be an error trying to contact your LDAP server. Please check your configurations or contact the administrator for the same."),"error");
                return;
            }
            else{
                \Drupal::messenger()->addMessage(t("Congratulations, you were succesfully able to connect to your LDAP Server"));
                return;
            }
        }
    }

    function miniorange_ldap_next_1($form, $form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'review_config')->save();
        $enable_ldap = $form['miniorange_ldap_enable_ldap']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_ldap', $enable_ldap)->save();
        if(!empty($form['miniorange_ldap_authenticate_admin']['#value'])){
            $authn_admin = $form['miniorange_ldap_authenticate_admin']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_authenticate_admin', $authn_admin)->save();
        }
        if(!empty($form['miniorange_ldap_authenticate_drupal']['#value'])){
            $authn_drupal_users = $form['miniorange_ldap_authenticate_drupal']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_authenticate_drupal_users', $authn_drupal_users)->save();
        }
        $auto_reg_users = $form['miniorange_ldap_enable_auto_reg']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_auto_reg', $auto_reg_users)->save();
        user_cookie_save(array("mo_ldap_test" => true));
        $error = handler::test_mo_config();
        if($error[1]=="error")
            \Drupal::messenger()->addMessage(t($error[0]),"error");
        else
            \Drupal::messenger()->addMessage(t($error[0]));
        return;
    }

    function miniorange_ldap_review_changes($form, $form_state){
        $enable_ldap = $form['miniorange_ldap_enable_ldap']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_ldap', $enable_ldap)->save();
        if(!empty($form['miniorange_ldap_authenticate_admin']['#value'])){
            $authn_admin = $form['miniorange_ldap_authenticate_admin']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_authenticate_admin', $authn_admin)->save();
        }
        if(!empty($form['miniorange_ldap_authenticate_drupal']['#value'])){
            $authn_drupal_users = $form['miniorange_ldap_authenticate_drupal']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_authenticate_drupal', $authn_drupal_users)->save();
        }
        $auto_reg_users = $form['miniorange_ldap_enable_auto_reg']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_auto_reg', $auto_reg_users)->save();
        if(!empty($form['miniorange_ldap_server']['#value'])){
            $mo_ldap_server = $form['miniorange_ldap_server']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server', $mo_ldap_server)->save();
        }
        if(!empty($form['miniorange_ldap_enable_tls']['#value'])){
            $enable_tls = $form['miniorange_ldap_enable_tls']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_tls', $enable_tls)->save();
        }
        if(!empty($form['miniorange_ldap_server_account_username']['#value'])){
            $server_account_username = trim($form['miniorange_ldap_server_account_username']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_username', $server_account_username)->save();
        }
        if(!empty($form['miniorange_ldap_server_account_password']['#value'])){
            $server_account_password = trim($form['miniorange_ldap_server_account_password']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', $server_account_password)->save();
        }
        if(!empty($form['miniorange_ldap_search_base']['#value'])){
            $searchBase = trim($form['miniorange_ldap_search_base']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_search_base', $searchBase)->save();
        }
        if(!empty($form['miniorange_ldap_username_attribute']['#value'])){
            $usernameAttribute = trim($form['miniorange_ldap_username_attribute']['#value']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute', $usernameAttribute)->save();
        }
        if(!empty($form['miniorange_ldap_test_username']['#value'])){
            $testUsername = $form['miniorange_ldap_test_username']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_username', $testUsername)->save();
        }
        if(!empty($form['miniorange_ldap_test_password']['#value'])){
            $testPassword = $form['miniorange_ldap_test_password']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_password', $testPassword)->save();
        }
    }

    function submitForm(array &$form, FormStateInterface $form_state){
    }

    function saved_support(array &$form, FormStateInterface $form_state) {
        $email = $form['miniorange_ldap_email_address']['#value'];
        $phone = $form['miniorange_ldap_phone_number']['#value'];
        $query = $form['miniorange_ldap_support_query']['#value'];
        Utilities::send_support_query($email, $phone, $query);
    }
}