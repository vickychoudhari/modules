<?php
namespace Drupal\miniorange_2fa\form;

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MoAuthUtilities;

class UserMfaSetup extends FormBase {
  private $entityId;
  private $savedFields;
  private $isAdmin;
  private $tfaRequired;

  public function __construct() {
    $url_parts        = MoAuthUtilities::mo_auth_get_url_parts();
    end($url_parts);
    $this->entityId = prev($url_parts);
    $this->savedFields = MoAuthUtilities::get_users_custom_attribute($this->entityId);
    $account = $this->currentUser();
    $this->isAdmin = $account->isAuthenticated() && $account->hasPermission('administer users');
    $user = User::load(intval($this->entityId));
    $this->tfaRequired = MoAuthUtilities::isTFARequired($user->getRoles(),$user->getEmail());
  }


  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return "mo_mfa_form";
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $variables_and_values = array(
          'mo_auth_enable_two_factor',
      );
      $mo_db_values = MoAuthUtilities::miniOrange_set_get_configurations( $variables_and_values, 'GET' );
     \Drupal::service('page_cache_kill_switch')->trigger();
      $account = $this->currentUser();
      // We can't let you see the settings if you are not admin and trying to edit 2FA for some other user
      if( !$mo_db_values['mo_auth_enable_two_factor'] || !MoAuthUtilities::isUserCanSee2FASettings() || ( !$this->isAdmin && $account->id() !== $this->entityId ) ) {
        return $form;
      }

      $form['mo_mfa_enable']=array(
        '#type'=>'checkbox',
        '#title'=>$this->t('Enable MFA for your account'),
        '#default_value'=>empty($this->savedFields) || !array_key_exists("enabled",(array)$this->savedFields[0])?0:$this->savedFields[0]->enabled,
        '#description'=>$this->t('Note: You will not be prompted for 2FA if you disable this'),
      );

      $form['mo_mfa_form_save'] = array(
        '#type'=>'submit',
        '#value'=>$this->t('Submit')
      );

      return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $formValues = $form_state->getValues();
    if(!($this->isAdmin) && $formValues["mo_mfa_enable"]===0 ){
       // TFA required means you are secured users and skipNotAllowed for you the
      if(MoAuthUtilities::isSkipNotAllowed($this->entityId)){
        $this->messenger()->addError($this->t("You are not allowed to disable 2FA. Please ask the site administrator"));
        return;
      }
    }
    MoAuthUtilities::updateMfaSettingsForUser($this->entityId,$formValues["mo_mfa_enable"]);
    $this->messenger()->addStatus($this->t('2FA settings are updated for your account'));
  }
}