<?php
/**
 * @file
 * Contains Attribute for miniOrange OAuth Client Module.
 */

 /**
 * Showing Settings form.
 */
namespace Drupal\oauth_login_oauth2\Form;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Render;
use Drupal\oauth_login_oauth2\Utilities;

class MiniorangeRFD extends FormBase {

  public function getFormId() {
    return 'miniorange_oauth_client_rfd';
  }


    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "oauth_login_oauth2/oauth_login_oauth2.admin",
                    "oauth_login_oauth2/oauth_login_oauth2.style_settings",
                    "oauth_login_oauth2/oauth_login_oauth2.module",
                )
            ),
        );

        $user_email = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_email');

        $form['markup_1'] = array(
            '#markup' =>'<div class="mo_oauth_table_layout_1"><div class="mo_oauth_table_layout mo_oauth_container">'
        );

        $form['markup_request_for_demo'] = array(
            '#type' => 'fieldset',
            '#title' => t('REQUEST FOR DEMO'),
            '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
            '#markup' => '<br><hr><br>',
        );

        $form['markup_request_for_demo']['markup_2'] = array(
            '#markup' => '<div class="mo_oauth_highlight_background_note_export"><p><strong>Want to test any of the Premium module before purchasing? </strong></p>
                          <p>Just send us a request, We will setup a demo site for you on our cloud with the premium module and provide you with the administrator credentials.
                          You can configure it with your OAuth Server and test all the premium features as per your requirement.</p>  
                          </div><br>',
        );

        $form['markup_request_for_demo']['customer_email'] = array(
            '#type' => 'email',
            '#title' => t('Email'),
            '#required' => TRUE,
            '#default_value' => t(strval($user_email)),
            '#attributes' => array('style' => 'width:65%;', 'placeholder' => 'Enter your email'),
            '#description' => t('<b>Note:</b> Use valid Email ID. ( We discourage the use of disposable emails )'),
        );

        $form['markup_request_for_demo']['demo_plan'] = array(
            '#type' => 'select',
            '#title' => t('Demo Plan'),
            '#attributes' => array('style' => 'width:65%;'),
            '#options' => [
                'Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Standard Module' => t('Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Standard Module'),
                'Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Premium Module' => t('Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Premium Module'),
                'Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Enterprise Module' => t('Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Enterprise Module'),
                'Not Sure' => t('Not Sure'),
            ],
        );

        $form['markup_request_for_demo']['description_doubt'] = array(
            '#type' => 'textarea',
            '#title' => t('Description'),
            '#attributes' => array('style' => 'width:65%', 'placeholder' => 'Describe your requirement'),
            '#required' => TRUE,
        );

        $form['markup_request_for_demo']['submit_button'] = array(
            '#type' => 'submit',
            '#value' => t('Submit'),
            '#prefix' => '<br>',
            '#suffix' => '</div>',
        );

        Utilities::newFeatureRequestForm($form, $form_state);
        $form['mo_markup_div_end1']=array('#markup'=>'</div>');

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $email = trim($form['markup_request_for_demo']['customer_email']['#value']);
        $demo_plan = $form['markup_request_for_demo']['demo_plan']['#value'];
        $description_doubt = trim($form['markup_request_for_demo']['description_doubt']['#value']);
        $query = $demo_plan.' -> '.$description_doubt;
        Utilities::send_demo_query($email, $query,$description_doubt);
    }
}