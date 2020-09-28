<?php

/**
 * @file
 * Contains \Drupal\miniorange_oauth_client\Form\MiniorangeGeneralSettings.
 */

namespace Drupal\oauth_login_oauth2\Form;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oauth_login_oauth2\mo_saml_visualTour;
use Drupal\oauth_login_oauth2\Utilities;
use Drupal\Core\Form\FormBase;
use Drupal\oauth_login_oauth2\MiniorangeOAuthClientSupport;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MiniorangeMapping extends FormBase{
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'miniorange_mapping';
  }
  public function buildForm(array $form, FormStateInterface $form_state){
    global $base_url;
    $moTour = mo_saml_visualTour::genArray();
    $form['tourArray'] = array(
        '#type' => 'hidden',
        '#value' => $moTour,
    );
    $form['markup_library'] = array(
      '#attached' => array(
          'library' => array(
              "oauth_login_oauth2/oauth_login_oauth2.admin",
              "oauth_login_oauth2/oauth_login_oauth2.style_settings",
              "oauth_login_oauth2/oauth_login_oauth2.Vtour",
          )
      ),
    );
    $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');
    $form['markup_top'] = array(
        '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
    );

    $form['markup_top_vt_start'] = array(
        '#type' => 'fieldset',
        '#title' => t('ATTRIBUTE MAPPING'),
        '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
        '#markup' => '<a id="Restart_moTour" class="mo_oauth_btn mo_oauth_btn-primary-color mo_oauth_btn-large mo_oauth_btn_restart_tour">Take a Tour</a><br><br><hr><br>',
      );

    $email_attr = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_email_attr_val');
    $name_attr =\Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_name_attr_val');
    
    $form['markup_top_vt_start']['miniorange_oauth_client_email_attr'] = array(
      '#type' => 'textfield',
      '#title' => t('Email Attribute: '),
      '#default_value' => $email_attr,
      '#description' => 'This field is mandatory for login',
      '#required' => TRUE,
      '#prefix' => '<b>Note: </b>Please copy the attribute name with <b>email</b> and <b>username</b> from the <b>test configuration</b> window for successful SSO.<br><br>',
      '#attributes' => array('id'=>'mo_oauth_vt_attrn','style' => 'width:73%;','placeholder' => 'Enter Email Attribute'),
    );
    $form['markup_top_vt_start']['miniorange_oauth_client_name_attr'] = array(
      '#type' => 'textfield',
      '#title' => t('Username Attribute: '),
      '#description' => "<b>Note:</b> If this text field is empty, then by default email id will be the user's username",
      '#default_value' => $name_attr,
      '#attributes' => array('id'=>'mo_oauth_vt_attre','style' => 'width:73%;','placeholder' => 'Enter Username Attribute'),
    );
    $form['markup_top_vt_start']['miniorange_oauth_client_attr_setup_button_2'] = array(
      '#type' => 'submit',
      '#value' => t('Save Configuration'),
      '#submit' => array('::miniorange_oauth_client_attr_setup_submit'),
      '#button_type' => 'primary',
      '#attributes' => array('style' => '	margin: auto; display:block; '),
    );
    
    $form['markup_custom_attribute'] = array(
        '#type' => 'fieldset',
        '#title' => t('CUSTOM ATTRIBUTE MAPPING <a href="' . $base_url . '/admin/config/people/oauth_login_oauth2/licensing"> [STANDARD, PREMIUM, ENTERPRISE]</a>'),
        '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
    );

    $form['markup_custom_attribute']['markup_cam'] = array(
      '#markup' => '<br><hr><br><div class="mo_saml_highlight_background_note_1">Add the Drupal field attributes in the Attribute Name textfield and add the OAuth Server attributes that you need to map with the drupal attributes in the OAuth Server Attribute Name textfield. 
                      Drupal Field Attributes will be of type text. Add the machine name of the attribute in the Drupal Attribute textfield. 
                    <b>For example: </b>If the attribute name in the drupal is name then its machine name will be field_name.</div><br>',
    );

     $form['markup_custom_attribute']['miniorange_oauth_attr_name'] = array(
       '#type' => 'textfield',
       '#prefix' => '<div><table><tr><td>',
       '#suffix' => '</td>',
       '#id' => 'text_field',
       '#title' => t('OAuth Server Attribute Name 1'),
       '#attributes' => array('style' => 'width:73%;background-color: hsla(0,0%,0%,0.08) !important;','placeholder' => 'Enter OAuth Server Attribute Name'),
       '#required' => FALSE,
       '#disabled' => TRUE,
     );
    $form['markup_custom_attribute']['miniorange_oauth_server_name'] = array(
         '#type' => 'textfield',
         '#id' => 'text_field1',
         '#prefix' => '<td>',
       '#suffix' => '</td>',
         '#title' => t('Attribute Name 1'),
         '#attributes' => array('style' => 'width:73%;background-color: hsla(0,0%,0%,0.08) !important;','placeholder' => 'Enter Attribute Name'),
         '#required' => FALSE,
         '#disabled' => TRUE,
       );
       $form['markup_custom_attribute']['miniorange_oauth_add_name'] = array(
        '#prefix' => '<td>',
        '#suffix' => '</td>',
        '#type' => 'button',
        '#disabled' => 'true',
        '#attributes' => array('style' => 'background-color: lightgreen;color:white'),
        '#value' => '+',
       );
       $form['markup_custom_attribute']['miniorange_oauth_sub_name'] = array(
        '#prefix' => '<td>',
        '#suffix' => '</td></tr></table></div>',
        '#type' => 'button',
        '#disabled' => 'true',
        '#attributes' => array('style' => 'background-color: red;color:white'),
        '#value' => '-',
       );

    $form['markup_custom_role_mapping'] = array(
        '#type' => 'fieldset',
        '#title' => t('CUSTOM ROLE MAPPING <a href="' . $base_url . '/admin/config/people/oauth_login_oauth2/licensing"> [PREMIUM, ENTERPRISE]</a>'),
        '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
    );   

    $form['markup_custom_role_mapping']['miniorange_disable_attribute'] = array(
        '#type' => 'checkbox',
        '#title' => t('Do not update existing user&#39;s role.'),
        '#disabled' => TRUE,
        '#prefix' => '<br><hr><br>',
    );
    $form['markup_custom_role_mapping']['miniorange_oauth_disable_role_update'] = array(
      '#type' => 'checkbox',
      '#title' => t('Check this option if you do not want to update user role if roles not mapped. '),
    '#disabled' => TRUE,
    );
    $form['markup_custom_role_mapping']['miniorange_oauth_disable_autocreate_users'] = array(
      '#type' => 'checkbox',
      '#title' => t('Check this option if you want to disable <b>auto creation</b> of users if user does not exist. '),
      '#disabled' => TRUE,
    );
	$mrole= user_role_names($membersonly = TRUE);
      $drole = array_values($mrole);

      $form['markup_custom_role_mapping']['miniorange_oauth_default_mapping'] = array(
          '#type' => 'select',
          '#id' => 'miniorange_oauth_client_app',
          '#title' => t('Select default group for the new users'),
          '#options' => $mrole,
          '#attributes' => array('style' => 'width:73%;'),
          '#disabled' => TRUE,
      );

      foreach($mrole as $roles) {
          $rolelabel = str_replace(' ','',$roles);
          $form['markup_custom_role_mapping']['miniorange_oauth_role_' . $rolelabel] = array(
              '#type' => 'textfield',
              '#title' => t($roles),
              '#attributes' => array('style' => 'width:73%;background-color: hsla(0,0%,0%,0.08) !important;','placeholder' => 'Semi-colon(;) separated Group/Role value for ' . $roles),
              '#disabled' => TRUE,
          );
      }

    $form['markup_custom_role_mapping']['markup_role_signin'] = array(
        '#markup' => '<div class="custom-login-logout mo_oauth_custom_login_logout"><br><strong>Custom Login/Logout (Optional)</strong><hr></div>'
    );
	
    $form['markup_custom_role_mapping']['miniorange_oauth_client_login_url'] = array(
        '#type' => 'textfield',
        '#id' => 'text_field2',
        '#required' => FALSE,
        '#disabled' => TRUE,
        '#attributes' => array('style' => 'width:73%;background-color: hsla(0,0%,0%,0.08) !important;','placeholder' => 'Enter Login URL'),
    );
    $form['markup_custom_role_mapping']['miniorange_oauth_client_logout_url'] = array(
        '#type' => 'textfield',
        '#id' => 'text_field3',
        '#required' => FALSE,
        '#disabled' => TRUE,
        '#attributes' => array('style' => 'width:73%;background-color: hsla(0,0%,0%,0.08) !important;','placeholder' => 'Enter Logout URL'),
    );
    $form['markup_custom_role_mapping']['markup_role_break'] = array(
        '#markup' => '<br>',
    );
    $form['markup_custom_role_mapping']['miniorange_oauth_client_attr_setup_button'] = array(
        '#type' => 'submit',
        '#value' => t('Save Configuration'),
        '#disabled' => TRUE,
        '#attributes' => array('style' => '	margin: auto; display:block; '),
    );

    $form['mo_header_style_end'] = array('#markup' => '</div>');
    Utilities::show_attr_list_from_idp($form, $form_state);
    $form['miniorange_idp_guide_link_end'] = array(
        '#markup' => '</div>',
    );
    Utilities::AddSupportButton($form, $form_state);
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  function miniorange_oauth_client_attr_setup_submit($form, $form_state)
  {
      $email_attr = trim($form['markup_top_vt_start']['miniorange_oauth_client_email_attr']['#value']);
      $name_attr = trim($form['markup_top_vt_start']['miniorange_oauth_client_name_attr']['#value']);

      \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_email_attr_val', $email_attr)->save();
      \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_name_attr_val', $name_attr)->save();
      $app_values = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_appval');
      $app_values['miniorange_oauth_client_email_attr'] = $email_attr;
      $app_values['miniorange_oauth_client_name_attr'] = $name_attr;
      \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_appval',$app_values)->save();
      \Drupal::messenger()->addMessage(t('Attribute Mapping saved successfully. Please logout and go to your Drupal site’s login page, you will automatically find a <b>Login with Your OAuth Provider</b> link there.'), 'status');
  }

    /**
     * Send support query.
     */
    public function saved_support(array &$form, FormStateInterface $form_state) {
        $email = trim($form['miniorange_oauth_client_email_address']['#value']);
        $phone = trim($form['miniorange_oauth_client_phone_number']['#value']);
        $query = trim($form['miniorange_oauth_client_support_query']['#value']);
        Utilities::send_support_query($email, $phone, $query);
    }

    function clear_attr_list(&$form,$form_state){
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->clear('miniorange_oauth_client_attr_list_from_server')->save();
        Utilities::show_attr_list_from_idp($form, $form_state);
    }

    public function rfd(array &$form, FormStateInterface $form_state) {

        global $base_url;
        $response = new RedirectResponse($base_url."/admin/config/people/oauth_login_oauth2/request_for_demo");
        $response->send();
    }
}
