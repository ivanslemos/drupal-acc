<?php

namespace Drupal\brmalls_login\Form;

use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\user\UserAuthInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

use Drupal\brmalls_login\BrMallsOAuthClientCustomer;

/**
 * Provides a user login form.
 *
 * @internal
 */
class UserLoginForm extends FormBase {
 

  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The user storage.
   *
   * @var \Drupal\brmalls_login\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The user authentication object.
   *
   * @var \Drupal\brmalls_login\UserAuthInterface
   */
  protected $userAuth;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new UserLoginForm.
   *
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   * @param \Drupal\brmalls_login\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\brmalls_login\UserAuthInterface $user_auth
   *   The user authentication object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(  FloodInterface $flood, UserStorageInterface $user_storage, UserAuthInterface $user_auth, RendererInterface $renderer ) {
 
    $this->flood = $flood;
    $this->userStorage = $user_storage;
    $this->userAuth = $user_auth;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flood'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('user.auth'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('system.site');

    // Display login form:
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#size' => 60,
      '#maxlength' => UserInterface::USERNAME_MAX_LENGTH,
      '#description' => $this->t('Enter your @s username of BR Malls.', ['@s' => $config->get('name')]),
      '#required' => TRUE,
      '#attributes' => [
        'autocorrect' => 'none',
        'autocapitalize' => 'none',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
      ],
    ];

    $form['pass'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#size' => 60,
      '#description' => $this->t('Enter the password that accompanies your username.'),
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Log in')];

    $form['#validate'][] = '::validateName';
    $form['#validate'][] = '::validateAuthentication';
    $form['#validate'][] = '::validateFinal';

    $this->renderer->addCacheableDependency($form, $config);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $account = $this->userStorage->load($form_state->get('uid'));

    // A destination was set, probably on an exception controller,
    if (!$this->getRequest()->request->has('destination')) {
      $form_state->setRedirect(
        'entity.user.canonical',
        ['user' => $account->id()]
      );
    }
    else {
      $this->getRequest()->query->set('destination', $this->getRequest()->request->get('destination'));
    }

    brmalls_login_finalize($account);
  }



  /**
   * Sets an error if supplied username has been blocked.
   */
  public function validateName(array &$form, FormStateInterface $form_state) {
    if (!$form_state->isValueEmpty('name') && user_is_blocked($form_state->getValue('name'))) {
      // Blocked in user administration.
      $form_state->setErrorByName('name', $this->t('The username %name has not been activated or is blocked.', ['%name' => $form_state->getValue('name')]));
    }
  }


  private function createUserDrupalFromBrMalls($user_brmalls, $user_form, $pass_form, $groupsIS){

    
    if(($user_brmalls != 0)){

      //Createde array de grups return IS      
      $groups = $groupsIS->data->group;
      $arrayGroups = [];
      $arrayTerms = [];

      //pego os Grupos que vem da API para o user ID passado
      foreach ($groups as $key => $values){
        $values = explode(',', $values);
        foreach ($values as $key => $value){
          $value = explode('=', $value);
          if(strstr($value[1], 'GS_CMS_')){
            $arrayGroups[]=$value[1];
          }
        }            
      }
      
      //get terms do vocabulario shopping cadastrado no sistema
      $query = \Drupal::entityQuery('taxonomy_term');
      $query->condition('vid', "shopping");
      $tids = $query->execute();
      $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
      $groupUserDrupal = [];
      foreach ($terms as $term) {         
          if(in_array($term->name->value, $arrayGroups)){
            $groupUserDrupal[] = $term->tid->value;
          }             
      }
      

      //verifica se o grupo que retorna com o usuário existe no vocabulário shopping do drupal
      if(!empty($groupUserDrupal)){


         // Create user object.    
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $user = \Drupal\user\Entity\User::create();

        //Mandatory settings
        $user->setPassword($pass_form);
        $user->enforceIsNew();
        $user->setEmail($user_form.'@brmalls.com.br');
        $user->setUsername($user_form);//This username must be unique and accept only a-Z,0-9, - _ @ .
        $user->set("status", TRUE);
        $user->addRole('brmalls'); //E.g: authenticated
        // $user->set('field_shopping', $arrayGroups);
        //$user->set("field_shopping", $saveGroup);

        //Save user
        $res = $user->save();

        $uid = $user->id();
        

        //Set config the group user
        $account = user_load($uid);
        $account->set('field_rules_shopping', $groupUserDrupal);
        $account->save();
        return $uid;       
      }else{
        return false;
      }      
    } 
    
  }
  /**
   * Checks supplied username/password against local users table.
   *
   * If successful, $form_state->get('uid') is set to the matching user ID.
   */
  public function validateAuthentication(array &$form, FormStateInterface $form_state) {
   
    $password = trim($form_state->getValue('pass'));
    $flood_config = $this->config('user.flood');
    if (!$form_state->isValueEmpty('name') && strlen($password) > 0) {
      // Do not allow any login from the current user's IP if the limit has been
      // reached. Default is 50 failed attempts allowed in one hour. This is
      // independent of the per-user limit to catch attempts from one IP to log
      // in to many different user accounts.  We have a reasonably high limit
      // since there may be only one apparent IP for all users at an institution.
      if (!$this->flood->isAllowed('user.failed_login_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
        $form_state->set('flood_control_triggered', 'ip');
        return;
      }

      $accounts = $this->userStorage->loadByProperties(['name' => $form_state->getValue('name'), 'status' => 1]);      
      $account = reset($accounts);
      if ($account) {
        if ($flood_config->get('uid_only')) {
          // Register flood events based on the uid only, so they apply for any
          // IP address. This is the most secure option.
          $identifier = $account->id();
        }
        else {
          // The default identifier is a combination of uid and IP address. This
          // is less secure but more resistant to denial-of-service attacks that
          // could lock out all users with public user names.
          $identifier = $account->id() . '-' . $this->getRequest()->getClientIP();
        }
        $form_state->set('flood_control_user_identifier', $identifier);
        // Don't allow login if the limit for this user has been reached.
        // Default is to allow 5 failed attempts every 6 hours.
        if (!$this->flood->isAllowed('user.failed_login_user', $flood_config->get('user_limit'), $flood_config->get('user_window'), $identifier)) {
          $form_state->set('flood_control_triggered', 'user');
          return;
        }
      }

      //---------------------------------------------------------------------------------
      // ---------------------------   start login BRMALLS  -----------------------------
      //---------------------------------------------------------------------------------
      $user_brmalls =  BrMallsOAuthClientCustomer::authenticate($form_state->getValue('name'),$password );
      $ids_user_brmalls = null;
      // get user from name in db
      if(isset($user_brmalls->success) and $user_brmalls->success){


          // START context BRMALLS
          $ids_user_brmalls = \Drupal::entityQuery('user')->condition('name', $form_state->getValue('name'))->execute(); 
          
          //var_dump($ids_user_brmalls); die('122');
          
          $uid = array_shift($ids_user_brmalls);          
          if(!empty($uid)){            
            $form_state->set('uid', $uid);
          }else{
            //NOW WE CREATED THE USER IN DRUPAL
            $groupsIS = BrMallsOAuthClientCustomer::getGroupInfo($form_state->getValue('name'));
            $user_form = $form_state->getValue('name');
            $user_id = $this->createUserDrupalFromBrMalls($ids_user_brmalls, $user_form, $password, $groupsIS); 
            
            
            
            if($user_id != FALSE){
              $form_state->set('uid', $user_id);
            }else{
              drupal_set_message('Você não possui shoppig(s) para administrar', 'error');
            }   
          }
          // END context BRMALLS
      }else{        
           // IF NOT EXIST IN CONTEXT BRMALLS.. LET THE USER GOT TO THE DRUPAL AUTHENTICATE NORMALITY 
          // We are not limited by flood control, so try to authenticate.
          // Store $uid in form state as a flag for self::validateFinal().
          // $uid = $this->userAuth->authenticate($form_state->getValue('name'), $password);
          // $form_state->set('uid', $uid);
          drupal_set_message('Você não possui uma credencial de acesso', 'error');

      }
      //---------------------------------------------------------------------------------
      // ---------------------------   end login BRMALLS  -------------------------------
      //---------------------------------------------------------------------------------



    }
  }

  /**
   * Checks if user was not authenticated, or if too many logins were attempted.
   *
   * This validation function should always be the last one.
   */
  public function validateFinal(array &$form, FormStateInterface $form_state) {
    $flood_config = $this->config('user.flood');
    if (!$form_state->get('uid')) {
      // Always register an IP-based failed login event.
      $this->flood->register('user.failed_login_ip', $flood_config->get('ip_window'));
      // Register a per-user failed login event.
      if ($flood_control_user_identifier = $form_state->get('flood_control_user_identifier')) {
        $this->flood->register('user.failed_login_user', $flood_config->get('user_window'), $flood_control_user_identifier);
      }

      if ($flood_control_triggered = $form_state->get('flood_control_triggered')) {
        if ($flood_control_triggered == 'user') {
          $form_state->setErrorByName('name', $this->formatPlural($flood_config->get('user_limit'), 'There has been more than one failed login attempt for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', 'There have been more than @count failed login attempts for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', [':url' => Url::fromRoute('user.pass')->toString()]));
        }
        else {
          // We did not find a uid, so the limit is IP-based.
          $form_state->setErrorByName('name', $this->t('Too many failed login attempts from your IP address. This IP address is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', [':url' => Url::fromRoute('user.pass')->toString()]));
        }
      }
      else {
        // Use $form_state->getUserInput() in the error message to guarantee
        // that we send exactly what the user typed in. The value from
        // $form_state->getValue() may have been modified by validation
        // handlers that ran earlier than this one.
        $user_input = $form_state->getUserInput();
        $query = isset($user_input['name']) ? ['name' => $user_input['name']] : [];
        $form_state->setErrorByName('name', $this->t('Unrecognized username or password. <a href=":password">Forgot your password?</a>', [':password' => Url::fromRoute('user.pass', [], ['query' => $query])->toString()]));
        $accounts = $this->userStorage->loadByProperties(['name' => $form_state->getValue('name')]);
        if (!empty($accounts)) {
          $this->logger('user')->notice('Login attempt failed for %user.', ['%user' => $form_state->getValue('name')]);
        }
        else {
          // If the username entered is not a valid user,
          // only store the IP address.
          $this->logger('user')->notice('Login attempt failed from %ip.', ['%ip' => $this->getRequest()->getClientIp()]);
        }
      }
    }
    elseif ($flood_control_user_identifier = $form_state->get('flood_control_user_identifier')) {
      // Clear past failures for this user so as not to block a user who might
      // log in and out more than once in an hour.
      $this->flood->clear('user.failed_login_user', $flood_control_user_identifier);
    }
  }

}
