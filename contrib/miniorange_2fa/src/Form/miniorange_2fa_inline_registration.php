<?php
namespace Drupal\miniorange_2fa\form;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\UsersAPIHandler;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\MoAuthConstants;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * @file
 * Page 1: Select Email address.
 * Page 2: Verify OTP.
 * Page 3: Select Auth Method.
 * Page 4: Configure Auth Method.
 * Page 5: Configure KBA.
 */
class miniorange_2fa_inline_registration extends FormBase
{
    public function getFormId() {
        return 'mo_auth_inline_registration';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_2fa/miniorange_2fa.admin",
                    "miniorange_2fa/miniorange_2fa.license",
                )
            ),
        );

      $session = MoAuthUtilities::getSession();
      $moMfaSession = $session->get("mo_auth",null);


      if(is_null($moMfaSession) || !isset($moMfaSession['status']) || !isset($moMfaSession['uid']) || $moMfaSession['status'] !=='1ST_FACTOR_AUTHENTICATED'){
        return $form;
      }


        if( !isset( $_SESSION['success_status'] ) ){
            $_SESSION['success_status'] = TRUE;
        }

        $url_parts =  MoAuthUtilities::mo_auth_get_url_parts();

        end($url_parts );
        $user_id = prev($url_parts );
        if($moMfaSession['uid']!=$user_id)
          return $form;
        $form_state->uid = $user_id;
        $utilities = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute( $user_id );
        $account = User::load( $user_id );
        $mo2FAResetRequest = isset($_GET['mo2faresetrequest']);

        $storage = $form_state->getStorage();

        if ( empty( $custom_attribute[0]->miniorange_registered_email ) || (!empty( $custom_attribute[0]->miniorange_registered_email ) && $mo2FAResetRequest && \Drupal::currentUser()->isAuthenticated()) ) {

            /** Display page 5 if $storage['page_five'] is set */
            if ( isset( $storage['page_five'] ) ) {
                return $this->mo_auth_inline_registration_page_five($form, $form_state);
            } elseif ( isset( $storage['page_otp_validate'] ) ) {
                return $this->mo_auth_inline_registration_page_four_otp_validate($form, $form_state, $_SESSION['success_status'], $_SESSION['message']);
            } elseif ( isset( $storage['page_four'] ) ) {
                return $this->mo_auth_inline_registration_page_four($form, $form_state, $_SESSION['success_status'], $_SESSION['message']);
            } elseif ( isset( $storage['page_three'] ) || (!empty( $custom_attribute[0]->miniorange_registered_email ) && $mo2FAResetRequest && \Drupal::currentUser()->isAuthenticated())) {
                return $this->mo_auth_inline_registration_page_three($form, $form_state,$mo2FAResetRequest);
            } elseif ( isset( $storage['page_two'] ) ) {
                return $this->mo_auth_inline_registration_page_two($form, $form_state, $_SESSION['success_status']);
            } else {
                return $this->mo_auth_inline_registration_page_one($form, $form_state, $_SESSION['success_status'],$account->getEmail());
            }
        }
    }

    function mo_auth_inline_registration_page_four_otp_validate( array $form, FormStateInterface $form_state, $success_status, $message ) {
        return $this->mo_auth_get_otp_over_sms_validate_form($form, $form_state, $success_status, $message);
    }

    function mo_auth_inline_registration_page_one( $form, FormStateInterface &$form_state, $success_status,$user_email ) {

        $prefix = t('<div class="mo2f-modal">
              <div class="mo2f-modal-content">
              <div class="mo2f-modal-container mo2f-modal-header">Register (Step 1/5)</div>
              <div class="mo2f-modal-container">');
        if ( $success_status === FALSE ) {
            if( isset( $_SESSION['message'] ) ) {
                $message = $_SESSION['message'];
                $prefix .= '<div class="mo2f-message mo2f-message-error">' . $message . '</div>';
            }else{
                $message = 'This email is already in use. Please try another email.';
                $prefix .= '<div class="mo2f-message mo2f-message-error">' . $message . '</div>';
            }
            unset($_SESSION['message']);
            $_SESSION['success_status'] = TRUE;
        }
        $prefix = $prefix. '<div class="mo2f-info mo2f-text-center">'.t('A new security system has been enabled to better protect your account. Please configure your Two-Factor Authentication method by setting up your account').'</div>
        <div class="mo2f-text-center">';
        $sufix = '</div></div><div class="mo2f-modal-container mo2f-modal-footer">';

        $form['mo_auth_user_email'] = array(
            '#type'          => 'email',
            '#default_value' => $user_email,
            '#required'      => TRUE,
            '#attributes'    => array(
                'placeholder' => t('person@example.com'),
                'class' => array(
                    'mo2f_email_textbox'
                ),
            ),
            '#disabled' => TRUE,
            '#prefix'   => $prefix,
            '#suffix'   => $sufix
        );
        $form['actions'] = array(
            '#type' => 'actions'
        );

        $form['actions']['start'] = array(
            '#type' => 'submit',
            '#value' => t('Get Started'),
            '#submit' => array('::handle_page_one_submit'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        if( !MoAuthUtilities::isSkipNotAllowed( $form_state->uid ) ) {
          $form['actions']['skip_mfa'] = array(
            '#type' => 'submit',
            '#value' => t('Skip 2FA'),
           // '#limit_validation_errors'=> array(),
            '#submit' => array('::handle_skip_mfa'),
            '#attributes' => array('class' => array('mo2f_button')),
          );
        }

        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#limit_validation_errors'=> array(),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        return $form;
    }

    // We can't allow SKIP if only second factor option is set to TRUE

    function handle_skip_mfa ($form , FormStateInterface &$form_state){
      $session = MoAuthUtilities::getSession();
      $moMfaSession = $session->get('mo_auth');
      $account = NULL;
      $redirectUrl = Url::fromRoute('user.login')->toString();
      if(isset($moMfaSession['uid'])){
        $account = User::load( $uid = $moMfaSession['uid'] );
        MoAuthUtilities::updateMfaSettingsForUser($account->id(),0);
        user_login_finalize( $account );
        $this->messenger()->addWarning($this->t("You have successfully disabled 2FA for your account. You can enable it anytime from here."));
        $redirectUrl = Url::fromRoute('miniorange_2fa.user.mo_mfa_form', ['user'=>$account->id()])->toString();
      }
      $response = new RedirectResponse($redirectUrl);
      $response->send();
    }

    function handle_page_one_submit( array $form, FormStateInterface $form_state ) {
        $email = MoAuthUtilities::getSession()->get('mo_auth')['user_email'];

        if ( !\Drupal::service('email.validator')->isValid( $email ) ) {
            // Send Status as this to show error message
            $_SESSION['success_status'] = FALSE;
            $_SESSION['message'] = t('The email address <b class="mo2f_bold"> %email </b> is not valid.',array('%email'=>$email));
            $form_state->setRebuild();
            return $form;
        }
        $connection = \Drupal::database();
        $query  = $connection->query("SELECT * FROM {UserAuthenticationType} where miniorange_registered_email = '$email'");
        $result = $query->fetchAll();

        $email_used = FALSE;
        if( !empty( $result ) ) {
            $email_used = TRUE;
        }

        if ( $email_used ) {
            // Send Status as this to show error message
            $_SESSION['success_status'] = FALSE;
            $form_state->setRebuild();
            return $form;
        }
        $customer = new MiniorangeCustomerProfile();
        $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $email, NULL, NULL, NULL);
        $user_api_handler = new UsersAPIHandler($customer->getCustomerID(), $customer->getAPIKey());

        $response = $user_api_handler->search( $miniorange_user );

        if ( $response->status == 'USER_FOUND' || $response->status == 'USER_NOT_FOUND' ) {
            $challenge_response = $this->send_otp_email_to_user($email);
            if ($challenge_response->status == 'SUCCESS') {
                $page = [
                    'page_two' => TRUE,
                    'page_one_values' =>$form_state->getValues(), [
                        'user_search_response' => $response,
                        'user_challenge_response' => $challenge_response,
                    ],
                ];
                $form_state->setStorage($page);
                $form_state->setRebuild();
            } else {
                 MoAuthUtilities::mo_add_loggers_for_failures( $challenge_response->message , 'error');
                \Drupal::messenger()->addError(t('An error occured while registering. Please contact your administrator.'));
            }
        } elseif ( is_object( $response ) && $response->status == 'USER_FOUND_UNDER_DIFFERENT_CUSTOMER' ) {
            $_SESSION['success_status'] = FALSE;
            $form_state->setRebuild();
            return $form;
        } else {
            unset($_SESSION['success_status']);
            MoAuthUtilities::mo_add_loggers_for_failures( is_object( $response ) ? $response->message : '', 'error');
            \Drupal::messenger()->addError(t("An error occurred. Please contact your administrator."),TRUE);
            $url = Url::fromRoute('user.login')->toString();
            $response = new RedirectResponse($url);
            $response->send();
        }
    }

    function mo_auth_inline_registration_page_two( array $form, FormStateInterface $form_state, $success_status ) {
        $storage = $form_state->getStorage();
        $email = $storage['page_one_values']['mo_auth_user_email'];
        if ( $success_status === FALSE ) {
            $message = t('The OTP you have entered is incorrect.');
            $_SESSION['success_status'] = TRUE;
        } else {
            $message = t('We have sent an OTP to %email . Enter the OTP to verify your email.',array('%email'=>$email));
        }
        $message_div_class = $success_status === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';

        $prefix = t('<div class="mo2f-modal">
              <div class="mo2f-modal-content">
                <div class="mo2f-modal-container mo2f-modal-header">Verify Email (Step 2/5)</div>
                <div class="mo2f-modal-container">
                  <div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>
                  <div class="mo2f-info">Enter the passcode:</div>
                <div>');
        $suffix = '</div></div><div class="mo2f-modal-container mo2f-modal-footer">';
        $form['mo_auth_verify_token'] = array(
            '#type' => 'textfield',
            '#attributes' => array(
                'placeholder' => t('Enter the OTP'),
                'class' => array(
                    'mo2f-textbox',
                    'mo2f-textbox-otp'
                ),
                'autofocus' => 'true'
            ),
            '#required' => TRUE,
            '#maxlength' => 8,
            '#prefix' => $prefix,
            '#suffix' => $suffix
        );

        $form['actions'] = array(
            '#type' => 'actions'
        );

        $form['actions']['verify'] = array(
            '#type' => 'submit',
            '#value' => t('Verify'),
            '#submit' => array('::handle_page_two_submit'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#limit_validation_errors'=> array(),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        return $form;
    }

    function handle_page_two_submit(array $form, FormStateInterface $form_state) {
        global $base_url;
        $storage = $form_state->getStorage();
        $token = str_replace( ' ', '', $form['mo_auth_verify_token']['#value'] );
        $challenge_response = $storage[0]['user_challenge_response'];
        $validate_response = $this->validate_otp_for_user( $challenge_response->txId, $token );

        if ( is_object( $validate_response ) && $validate_response->status == 'FAILED' ) {
            $page = [
                'page_two' => TRUE,
                'page_one_values' => $storage['page_one_values'], [
                    'user_search_response' => $storage[0]['user_search_response'],
                    'user_challenge_response' => $storage[0]['user_challenge_response'],
                ],
            ];

            $form_state->setStorage($page);
            $_SESSION['success_status'] = FALSE;
            $form_state->setRebuild();
            return $form;

        } elseif ( is_object( $validate_response ) && $validate_response->status != 'SUCCESS' ) {
            unset($_SESSION['success_status']);
            $form_state->setRebuild();
            MoAuthUtilities::mo_add_loggers_for_failures( $validate_response->message , 'error');
            \Drupal::messenger()->addError(t('An error occurred while registering the user.'),TRUE);
            $url = Url::fromRoute('user.login')->toString();
            $response = new RedirectResponse($url);
            $response->send();
        }

        $form_state->setRebuild();
        $email = $storage['page_one_values']['mo_auth_user_email'];
        $user_search_response = $storage[0]['user_search_response'];
        $customer = new MiniorangeCustomerProfile();
        $miniorange_user  = new MiniorangeUser($customer->getCustomerID(), $email, NULL, NULL, AuthenticationType::$EMAIL_VERIFICATION['code']);
        $user_api_handler = new UsersAPIHandler($customer->getCustomerID(), $customer->getAPIKey());

        if ( $user_search_response->status == 'USER_NOT_FOUND' ) {
          $storage['page_one_values']["mo_2fa_new_user"]=TRUE;
            $create_response = $user_api_handler->create($miniorange_user);
        }
        else{
          $storage['page_one_values']["mo_2fa_new_user"]=FALSE;
        }

        /* Check whether user creation limit is exceeded or not */
        if( isset( $create_response ) && isset( $create_response->status ) && isset( $create_response->message ) && $create_response->status == 'ERROR' && $create_response->message == t('Your user creation limit has been completed. Please upgrade your license to add more users.') ) {
          \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('mo_user_limit_exceed', TRUE)->save();
        }else {
          \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->clear('mo_user_limit_exceed')->save();
        }

        if( isset( $create_response ) && isset( $create_response->status ) && $create_response->status == 'ERROR' ){
            unset($_SESSION['success_status']);
            $form_state->setRebuild();
            MoAuthUtilities::mo_add_loggers_for_failures( $create_response->message , 'error');
            \Drupal::messenger()->addError(t('An error occurred while creating the user. Please contact your administrator.'));
            $url = Url::fromRoute('user.login')->toString();
            $response = new RedirectResponse($url);
            $response->send();
            exit;
        }
        // Update User Auth method to OUT OF BAND EMAIL
        $user_update_response = $user_api_handler->update($miniorange_user);
        if ($user_update_response->status == 'SUCCESS') {
            $page = [
                'page_two_values' =>$form_state->getValues(),
                'page_one_values' => $storage['page_one_values'],
            ];
            // check if there is only one option to choose in step 3
            $selectedMfaMethods = MoAuthUtilities::get_2fa_methods_for_inline_registration(TRUE);
            $nextPage = "page_three";
            if( count( $selectedMfaMethods ) === 1 )
            {
              $nextPage = "page_four";
              $selectedMfaMethodCode = array_keys($selectedMfaMethods)[0];
              if( $selectedMfaMethodCode === AuthenticationType::$EMAIL['code'] || $selectedMfaMethodCode === AuthenticationType::$EMAIL_VERIFICATION['code'] ){
                $nextPage = "page_five";
              }
              $page["page_three_values"] = array("mo_auth_method"=>$selectedMfaMethodCode);
            }
            $page[$nextPage] = TRUE;
            $form_state->setStorage($page);
            return;
        }
        // Handle error. return to login.
    }


    function mo_auth_inline_registration_page_three(array $form, FormStateInterface $form_state, $reset2FA = FALSE) {
        $heading = $reset2FA?t('Select Authentication method'):t('Select Authentication method  (Step 3/5)');
        $prefix = '<div class="mo2f-modal">
            <div class="mo2f-modal-content">
              <div class="mo2f-modal-container mo2f-modal-header">'.$heading.'</div>
              <div class="mo2f-modal-container">
        <div class="mo2f-info">'.t('Select your authentication method:').'</div><div>';
        $suffix = '</div></div><div class="mo2f-modal-container mo2f-modal-footer">';

        $options = MoAuthUtilities::get_2fa_methods_for_inline_registration( TRUE );

        $form['mo_auth_method'] = array(
            '#type' => 'select',
            '#default_value' => array_keys( $options )[0],
            '#options' =>  $options,
            '#required' => TRUE,
            '#prefix' => $prefix,
            '#suffix' => $suffix
        );

        $form['actions'] = array(
            '#type' => 'actions'
        );
        $form['actions']['next'] = array(
            '#type' => 'submit',
            '#value' => t('Next'),
            '#submit' => array('::handle_page_three_submit'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        return $form;
    }

    function handle_page_three_submit(array $form, FormStateInterface &$form_state) {
        $storage = $form_state->getStorage();

        if( !isset( $storage['page_one_values'] ) ) {
          $utilities = new MoAuthUtilities();
          $custom_attribute = $utilities::get_users_custom_attribute( $this->currentUser()->id() );
          $storage['page_one_values']['mo_auth_user_email']=$custom_attribute[0]->miniorange_registered_email;
          $storage['page_one_values']['mo_2fa_new_user']=FALSE;
          $storage['page_one_values']['mo_2fa_reset']=TRUE;
          $storage['page_two_values']=array();
          ;
        }
        $form_state->setRebuild();
        $method = $form['mo_auth_method']['#value'];
        if ( $method == AuthenticationType::$EMAIL_VERIFICATION['code'] || $method == AuthenticationType::$EMAIL['code'] ) {
            // Go to Step 5 directly
            $page = [
                'page_five' => TRUE,
                'page_three_values' =>$form_state->getValues(),
                'page_two_values' => $storage['page_two_values'],
                'page_one_values' => $storage['page_one_values'],

            ];
            $form_state->setStorage($page);
        } else {
            $page = [
                'page_four' => TRUE,
                'page_three_values' =>$form_state->getValues(),
                'page_two_values' => $storage['page_two_values'],
                'page_one_values' => $storage['page_one_values'],

            ];
            $_SESSION['message']='';
            $_SESSION['success_status'] = TRUE;
            $form_state->setStorage($page);
        }
    }

    function mo_auth_inline_registration_page_four(array $form, FormStateInterface $form_state, $success_status, $message) {
        $storage = $form_state->getStorage();
        $method = $storage['page_three_values']['mo_auth_method'];
        if ( $method == AuthenticationType::$GOOGLE_AUTHENTICATOR['code'] || $method == AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'] || $method == AuthenticationType::$AUTHY_AUTHENTICATOR['code'] || $method == AuthenticationType::$LASTPASS_AUTHENTICATOR['code'] || $method == AuthenticationType::$DUO_AUTHENTICATOR['code'] ) {
            return $this->mo_auth_get_google_authentication_form($form, $form_state, $success_status);
        } elseif ( $method == AuthenticationType::$KBA['code'] ) {
            return $this->mo_auth_get_kba_authentication_form($form, $form_state);
        } elseif ( $method == AuthenticationType::$QR_CODE['code'] || $method == AuthenticationType::$PUSH_NOTIFICATIONS['code'] || $method == AuthenticationType::$SOFT_TOKEN['code'] ) {
            return $this->mo_auth_get_qrcode_authentication_form($form, $form_state, $success_status);
        } elseif ( $method == AuthenticationType::$SMS['code'] || $method == AuthenticationType::$SMS_AND_EMAIL['code'] || $method == AuthenticationType::$EMAIL['code'] || $method == AuthenticationType::$OTP_OVER_PHONE['code'] ) {
            return $this->mo_auth_get_otp_over_sms_authentication_form($form, $form_state, $success_status, $message);
        } elseif ($method == AuthenticationType::$HARDWARE_TOKEN['code']){
          return $this->mo_auth_get_hardware_token_validate_form($form, $form_state, $success_status, $message);
        }
        return null;
    }

    function mo_auth_inline_registration_page_five($form, $form_state) {
      $this->handle2FAReset($form,$form_state);

      $enable_kba = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_2fa_kba_questions') == 'Not_Allowed' ? false : true;
      if (!$enable_kba  ){
          $this->handle_page_five_submit($form,$form_state);
      }

        $prefixQuestion1 = '<div class="mo2f-modal">
            <div class="mo2f-modal-content">
        <div class="mo2f-modal-container mo2f-modal-header">'.t('Configure Backup method (Step 5/5)').'</div>
      <div class="mo2f-modal-container">
        <div class="mo2f-info">'.t('Please choose your backup security questions:').'</div>
          <div>
            <div class="mo2f-kba-header mo2f-kba-row">
              <div class="mo2f-kba-srno">Sr. No.</div>
              <div class="mo2f-kba-question">'.t('Questions').'</div>
              <div class="mo2f-kba-answer">'.t('Answers').'</div>
            </div>
            <div class="mo2f-kba-row">
              <div class="mo2f-kba-srno">1.</div>
              <div class="mo2f-kba-question">';
        $suffixQuestion1 = '</div>';

        $prefixAnswer1 = '<div class="mo2f-kba-answer">';
        $suffixAnswer1 = '</div></div>';

        $prefixQuestion2 = '<div class="mo2f-kba-row">
            <div class="mo2f-kba-srno">2.</div>
            <div class="mo2f-kba-question">';
        $suffixQuestion2 = '</div>';

        $prefixAnswer2 = '<div class="mo2f-kba-answer">';
        $suffixAnswer2 = '</div></div>';

        $prefixQuestion3 = '<div class="mo2f-kba-row">
            <div class="mo2f-kba-srno">3.</div>
            <div class="mo2f-kba-question">';
        $suffixQuestion3 = '</div>';

        $prefixAnswer3 = '<div class="mo2f-kba-answer">';
        $suffixAnswer3 = '</div></div></div></div><div class="mo2f-modal-container mo2f-modal-footer">';

        $options_one = MoAuthUtilities::mo_get_kba_questions('ONE' );

        $options_two = MoAuthUtilities::mo_get_kba_questions('TWO' );

        $form['mo_auth_question1'] = array(
            '#type' => 'select',
            '#options' => $options_one,
            '#prefix' => $prefixQuestion1,
            '#suffix' => $suffixQuestion1,
            '#attributes' => array(
                'style' => 'width:85%;height:34px;padding-top:1px !important;',
            ),
            '#required' => TRUE,
        );
        $form['mo_auth_answer1'] = array(
            '#type' => 'textfield',
            '#prefix' => $prefixAnswer1,
            '#suffix' => $suffixAnswer1,
            '#size' => '20',
            '#attributes' => array(
                'placeholder' => t('Enter your answer'),
                'style' => 'width:85%;height:34px;',
            ),
            '#required' => TRUE,
        );
        $form['mo_auth_question2'] = array(
            '#type' => 'select',
            '#options' => $options_two,
            '#prefix' => $prefixQuestion2,
            '#suffix' => $suffixQuestion2,
            '#attributes' => array(
                'style' => 'width:85%;height:34px;padding-top:1px !important;',
            ),
            '#required' => TRUE,
        );
        $form['mo_auth_answer2'] = array(
            '#type' => 'textfield',
            '#prefix' => $prefixAnswer2,
            '#suffix' => $suffixAnswer2,
            '#attributes' => array(
                'placeholder' => t('Enter your answer'),
                'style' => 'width:85%;height:34px;',
            ),
            '#required' => TRUE,
        );
        $form['mo_auth_question3'] = array(
            '#type' => 'textfield',
            '#prefix' => $prefixQuestion3,
            '#suffix' => $suffixQuestion3,
            '#attributes' => array(
                'placeholder' => t('Enter your custom question here'),
                'style' => 'width:85%;height:34px;padding-top:1px !important;',
            ),
            '#required' => TRUE,
        );
        $form['mo_auth_answer3'] = array(
            '#type' => 'textfield',
            '#prefix' => $prefixAnswer3,
            '#suffix' => $suffixAnswer3,
            '#attributes' => array(
                'placeholder' => t('Enter your answer'),
                'style' => 'width:85%;height:34px;',
            ),
            '#required' => TRUE,
        );

        $form['actions'] = array(
            '#type' => 'actions'
        );

        $form['actions']['register'] = array(
            '#type' => 'submit',
            '#value' => t('Register'),
            '#submit' => array('::handle_page_five_submit'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#limit_validation_errors'=> array(),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        return $form;
    }

    function handle_page_four_submit(array $form, FormStateInterface $form_state) {
        $storage  = $form_state->getStorage();
        $method   = $storage['page_three_values']['mo_auth_method'];
        $form_state->setRebuild();

        if ( $method == AuthenticationType::$GOOGLE_AUTHENTICATOR['code'] || $method == AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'] || $method == AuthenticationType::$AUTHY_AUTHENTICATOR['code'] || $method == AuthenticationType::$LASTPASS_AUTHENTICATOR['code'] || $method == AuthenticationType::$DUO_AUTHENTICATOR['code'] ) {

            $email                = $storage['page_one_values']['mo_auth_user_email'];
            $google_auth_response = $storage['page_four_values']['google_auth_response'];
            $token                = $form['mo_auth_googleauth_token']['#value'];
            $secret               = $google_auth_response->secret;

            $customer             = new MiniorangeCustomerProfile();
            $miniorange_user      = new MiniorangeUser($customer->getCustomerID(), $email, NULL, NULL, AuthenticationType::$GOOGLE_AUTHENTICATOR['code']);
            $auth_api_handler     = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->register($miniorange_user, AuthenticationType::$GOOGLE_AUTHENTICATOR['code'], $secret, $token, NULL);
            if ($response->status == 'SUCCESS') {
                $page = [
                    'page_five' => TRUE,
                    'page_four_values'  => $form_state->getValues(),
                    'page_three_values' => $storage['page_three_values'],
                    'page_two_values'   => $storage['page_two_values'],
                    'page_one_values'   => $storage['page_one_values'],
                ];
                $_SESSION['success_status'] = TRUE;
                $form_state->setStorage($page);

                return;
            } elseif ($response->status == 'FAILED') {
                // Passcode incorrect. Try again - Show error form
                $page = [
                    'page_four' => TRUE,
                    'page_four_values' => [
                        'google_auth_response' => $storage['page_four_values']['google_auth_response']
                    ],
                    'page_three_values' =>$storage['page_three_values'],
                    'page_two_values' => $storage['page_two_values'],
                    'page_one_values' => $storage['page_one_values'],
                ];
                $form_state->setStorage($page);
                $_SESSION['success_status'] = FALSE;
                $form_state->setRebuild();
                return;
            } elseif ($response->status != 'SUCCESS') {
                $form_state->setRebuild();
                unset($_SESSION['success_status']);
                MoAuthUtilities::mo_add_loggers_for_failures( $response->message , 'error');
                \Drupal::messenger()->addError(t('An error occurred while registering the user.'),TRUE);
                $url = Url::fromRoute('user.login')->toString();
                $response = new RedirectResponse($url);
                $response->send();
            }

        } elseif ($method == AuthenticationType::$QR_CODE['code'] || $method == AuthenticationType::$SOFT_TOKEN['code'] || $method == AuthenticationType::$PUSH_NOTIFICATIONS['code']) {
            $qrcode_response = $storage['page_four_values']['qrcode_response'];
            $customer = new MiniorangeCustomerProfile();
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->getRegistrationStatus($qrcode_response->txId);
            if ($response->status == 'SUCCESS') {
                $page = [
                    'page_five' => TRUE,
                    'page_four_values' => $form_state->getValues(),
                    'page_three_values' => $storage['page_three_values'],
                    'page_two_values' => $storage['page_two_values'],
                    'page_one_values' => $storage['page_one_values'],
                ];
                $form_state->setStorage($page);
                return;
            }
        } elseif ($method ==AuthenticationType::$HARDWARE_TOKEN['code'] || $method == AuthenticationType::$SMS['code'] || $method == AuthenticationType::$SMS_AND_EMAIL['code'] || $method == AuthenticationType::$EMAIL['code'] || $method == AuthenticationType::$OTP_OVER_PHONE['code']) {
            $input = $form_state->getUserInput();
            $phone = isset( $input['mo_auth_otpoversms_phone'] ) ? str_replace(' ', '', $input['mo_auth_otpoversms_phone'] ) : '';

            $email = $storage['page_one_values']['mo_auth_user_email'];
            $customer = new MiniorangeCustomerProfile();

            if ($method == AuthenticationType::$SMS_AND_EMAIL['code']) {
                $miniorange_user = new MiniorangeUser($customer->getCustomerID(), NULL, $phone, NULL, $method, $email );
            }
            elseif ($method == AuthenticationType::$EMAIL['code']) {
                $miniorange_user = new MiniorangeUser($customer->getCustomerID(), NULL, NULL, NULL, $method, $email );
            }
            elseif ( $method == AuthenticationType::$OTP_OVER_PHONE['code'] || $method == AuthenticationType::$SMS['code'] ) {
                $miniorange_user = new MiniorangeUser($customer->getCustomerID(), NULL, $phone, NULL, $method, NULL );
            }
            elseif ( $method == AuthenticationType::$HARDWARE_TOKEN['code'] ) {
               $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $email, null, NULL, $method, NULL );
            }

            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->challenge($miniorange_user);

            if ( is_object( $response ) && $response->status == 'SUCCESS' ) {

                $page = [
                    'page_otp_validate' => TRUE,
                    'page_four_values' => $form_state->getValues(), [
                        'user_challenge_response' => $response,
                     ],
                    'page_three_values' => $storage['page_three_values'],
                    'page_two_values' => $storage['page_two_values'],
                    'page_one_values' => $storage['page_one_values'],
                ];
                $_SESSION['message'] = 'INVALID OTP';
                $_SESSION['success_status'] = TRUE;
                $form_state->setStorage($page);
                return;

            } elseif ( is_object( $response ) && $response->status == 'FAILED' ) {
                //$error = $response->message;
                $page = [
                    'Page_four' => TRUE,
                    'page_three_values' => $storage['page_three_values'],
                    'page_two_values' => $storage['page_two_values'],
                    'page_one_values' => $storage['page_one_values'],
                ];
                if( $method == AuthenticationType::$HARDWARE_TOKEN['code'] ){
                  $_SESSION['message'] = t('Error during creating a Hardware Token challenge. You may choose other methods.');
                }
                $_SESSION['message'] = t('Error during sending OTP. You may choose other methods.');
                $_SESSION['success_status'] = FALSE;
                $form_state->setStorage($page);
                $form_state->setRebuild();
                return;
            }

        } elseif ( $method == AuthenticationType::$KBA['code'] ) {
            $this->handle_page_five_submit($form, $form_state);
            return;
        }
        // Handle all error
    }

    function handle_page_five_submit(array $form, FormStateInterface $form_state) {
        $form_state->setRebuild();
        $storage = $form_state->getStorage();
        $user_email = $storage['page_one_values']['mo_auth_user_email'];

        $user_phone = isset($storage['page_four_values']['mo_auth_otpoversms_phone']) ? $storage['page_four_values']['mo_auth_otpoversms_phone'] : NULL;

        $enable_kba = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_2fa_kba_questions') == 'Not_Allowed' ? false : true;
        if ($enable_kba  ) {

            $question1 = $form['mo_auth_question1']['#value'];
            $answer1   = $form['mo_auth_answer1']['#value'];

            $question2 = $form['mo_auth_question2']['#value'];
            $answer2   = $form['mo_auth_answer2']['#value'];

            $question3 = $form['mo_auth_question3']['#value'];
            $answer3   = $form['mo_auth_answer3']['#value'];

            $qa1 = array(
                "question" => $question1,
                "answer" => $answer1
            );

            $qa2 = array(
                "question" => $question2,
                "answer" => $answer2
            );

            $qa3 = array(
                "question" => $question3,
                "answer" => $answer3
            );

            $kba = array(
                $qa1,
                $qa2,
                $qa3
            );
        }

        $method = $storage['page_three_values']['mo_auth_method'];
        $url_parts = MoAuthUtilities::mo_auth_get_url_parts();
        end($url_parts);
        $user_id = prev($url_parts);
        $session = MoAuthUtilities::getSession();
        $moMfaSession = $session->get('mo_auth');
        if(!isset($moMfaSession['uid']) || $moMfaSession['uid']!=$user_id) {
          $session->remove('mo_auth');
          MoAuthUtilities::mo_add_loggers_for_failures( t('URL change detected'), 'error');
          \Drupal::messenger()->addError(t("Authentication failed try again. URL change detected while inline registration."), TRUE);
          $url = Url::fromRoute('user.login')->toString();
          $response = new RedirectResponse($url);
          $response->send();
          exit;
        }
        $customer = new MiniorangeCustomerProfile();
        $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $user_email, $user_phone, NULL, $method == AuthenticationType::$AUTHY_AUTHENTICATOR['code'] || $method == AuthenticationType::$DUO_AUTHENTICATOR['code'] || $method == AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'] || $method == AuthenticationType::$LASTPASS_AUTHENTICATOR['code'] ? AuthenticationType::$GOOGLE_AUTHENTICATOR['code'] : $method );

        $bypass_register = true;
        $enable_kba = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_2fa_kba_questions') == 'Not_Allowed' ? false : true;
        if ($enable_kba) {
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->register($miniorange_user, AuthenticationType::$KBA['code'], NULL, NULL, $kba);
            $bypass_register = false;
        }

        if ($bypass_register || $response->status == 'SUCCESS' ) {
            $user_api_handler = new UsersAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $user_update_response = $user_api_handler->update($miniorange_user);
            if ($user_update_response->status == 'SUCCESS') {

                $database = \Drupal::database();
                $fields = array(
                    'uid' => $user_id,
                    'configured_auth_methods' => AuthenticationType::$EMAIL_VERIFICATION['code'],
                    'miniorange_registered_email' => $user_email,
                    'activated_auth_methods' => $method,
                );
                $result = MoAuthUtilities::get_users_custom_attribute($user_id);
                if( count( $result ) > 0 ) {
                  $database->update('UserAuthenticationType')->fields($fields)->condition('uid', $user_id,'=')->execute();
                }
                else
                  try {
                    $database->insert('UserAuthenticationType')->fields($fields)->execute();
                  } catch (\Exception $e) {
                  }

              $configured_methods = MoAuthUtilities::mo_auth_get_configured_methods($user_id);
                $available = MoAuthUtilities::check_for_userID($user_id);

                if ( !in_array( $method, $configured_methods ) ) {
                    array_push($configured_methods, $method);
                }
                if ($method != AuthenticationType::$KBA['code'] && $enable_kba) {
                    array_push($configured_methods, AuthenticationType::$KBA['code']);
                }
                $config_methods = implode(', ',$configured_methods);
                if($available == TRUE) {
                    $database->update('UserAuthenticationType')->fields(['configured_auth_methods' => $config_methods])->condition('uid', $user_id,'=')->execute();
                }else {
                    echo t("error while updating authentication method.");exit;
                }
                $user = User::load($user_id);
                user_login_finalize( $user );
                $custom_redirect_url = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_redirect_user_after_login');
                $mo_redirect_url = isset( $custom_redirect_url ) && !empty( $custom_redirect_url ) ? $custom_redirect_url : Url::fromRoute('user.login')->toString();
                $response = new RedirectResponse( $mo_redirect_url );
                $response->send();
            }
        } elseif ( $bypass_register || $response->status != 'SUCCESS' ) {
            // Error out. Send to login.
            MoAuthUtilities::mo_add_loggers_for_failures( $response->message , 'error');
            \Drupal::messenger()->addError(t('Unable to setup the second factor. Please contact your administrator.'),TRUE);
            $url = Url::fromRoute('user.login')->toString();
            $response = new RedirectResponse($url);
            $response->send();
        }
    }

    function send_otp_email_to_user( $username ) {
        $customer = new MiniorangeCustomerProfile();
        $miniorange_user = new MiniorangeUser($customer->getCustomerID(), NULL, NULL, NULL, AuthenticationType::$EMAIL['code'], $username);
        $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $response = $auth_api_handler->challenge($miniorange_user);
        return $response;
    }

    function validate_otp_for_user($txId, $token) {
        $customer = new MiniorangeCustomerProfile();
        $miniorange_user = new MiniorangeUser($customer->getCustomerID(), NULL, NULL, NULL, NULL);
        $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $response = $auth_api_handler->validate($miniorange_user, $txId, $token);
        return $response;
    }

    function mo_auth_get_google_authentication_form(array $form, FormStateInterface $form_state, $success_status ) {
        global $base_url;
        $storage = $form_state->getStorage();
        $email   = $storage['page_one_values']['mo_auth_user_email'];

        $method = $storage['page_three_values']['mo_auth_method'];
        $configuration_method = t('Google');
        if ( $method == AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'] ) {
            $configuration_method = t('Microsoft');
        } elseif ( $method == AuthenticationType::$AUTHY_AUTHENTICATOR['code'] ) {
            $configuration_method = t('Authy');
        } elseif ( $method == AuthenticationType::$LASTPASS_AUTHENTICATOR['code'] ) {
            $configuration_method = t('LastPass');
        } elseif ( $method == AuthenticationType::$DUO_AUTHENTICATOR['code'] ) {
            $configuration_method = t('Duo');
        }

        if ( isset( $storage['page_four_values']['google_auth_response'] ) ) {
            $google_auth_response = $storage['page_four_values']['google_auth_response'];
            $qrCode = $google_auth_response->qrCodeData;
            $image  = new FormattableMarkup('<img class="mo2f_image" src="data:image/jpg;base64, '.$qrCode.'"/>', [':src' => $qrCode]);
        } else {
            $customer         = new MiniorangeCustomerProfile();
            $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
            $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $email, NULL, NULL, NULL );
            $response         = $auth_api_handler->getGoogleAuthSecret( $miniorange_user );
            if ( $response->status == 'SUCCESS' ) {
                $page = [
                    'page_four_values' => [
                        'google_auth_response' => $response
                    ],
                    'page_three_values' =>$storage['page_three_values'],
                    'page_two_values' => $storage['page_two_values'],
                    'page_one_values' => $storage['page_one_values'],
                ];
                $form_state->setStorage($page);
                $qrCode = $response->qrCodeData;
                $image = new FormattableMarkup('<img class="mo2f_image" src="data:image/jpg;base64, '.$qrCode.'"/>', [':src' => $qrCode]);
            }
        }

        $iPhoneAppLink = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/iphone-google-authenticator-app-link.png');
        $androidAppLink = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/android-google-authenticator-app-link.png');
        $stepMessage = $this->is2FAResetRequest($form_state)?'':'(Step 4/5)';
        $form['actions_21'] = array(
            '#markup' =>'<div class="mo2f-modal">
              <div class="mo2f-modal-content">
                <div class="mo2f-modal-container mo2f-modal-header">'.t('Configure '). $configuration_method .t(' Authenticator ').$stepMessage.'</div>
                <div class="mo2f-modal-container">'
        );
        if ($success_status === FALSE) {
            $message = t('The passcode you have entered is incorrect.');
            $form['actions_22'] = array(
                '#markup' =>'
                    <div class="mo2f-message mo2f-message-error">' . $message . '</div>'
            );
            $_SESSION['success_status'] = TRUE;
        }
        $form['actions_23'] = array(
                '#markup' =>'<div><div class="mo2f-info"><b class="mo2f_bold">'.t('Step 1:').'</b> '.t('Download and install the '). $configuration_method .' Authenticator app</div>
            <div class="mo2f-text-center" style="display:inline-block;width:60%;text-align:center">
              <a target="_blank" href="https://itunes.apple.com/in/app/google-authenticator/id388497605?mt=8"><img class="mo2f_image" src="' . $iPhoneAppLink . '"></a>
                </div>
                <div class="mo2f-text-center" style="display:inline-block">
                  <a target="_blank" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"><img class="mo2f_image" src="' . $androidAppLink . '"></a>
                </div>
                </div>'
        );
        $form['actions_24'] = array(
            '#markup' =>'<div><div class="mo2f-info"><b class="mo2f_bold">Step 2:</b> Scan the QR Code from '. $configuration_method .' Authenticator app</div><div class="mo2f-info mo2f-text-center">'

        );
        $form['actions_qrcode_google_authenticator'] = array(
            '#markup' => $image,
        );

        $form['actions_25'] = array(
            '#markup' =>'</div><div class="mo2f-info"><b class="mo2f_bold">Step 3:</b> Enter the passcode generated by '. $configuration_method .' Authenticator app</div>'
        );

        $sufix = '</div><div class="mo2f-modal-container mo2f-modal-footer">';

        $form['mo_auth_googleauth_token'] = array(
            '#type' => 'textfield',
            '#attributes' => array(
                'placeholder' => t('Enter the passcode'),
                'class' => array(
                    'mo2f-textbox',
                    'mo2f-textbox-otp'
                ),
                'autofocus' => 'true'
            ),
            '#maxlength' => 8,
            '#suffix' => $sufix,
        );
        $form['actions'] = array(
            '#type' => 'actions'
        );
        $form['actions']['verify'] = array(
            '#type' => 'submit',
            '#value' => t('Verify and Save'),
            '#submit' => array('::handle_page_four_submit'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        return $form;
    }

    function mo_auth_get_qrcode_authentication_form(array $form, FormStateInterface $form_state, $success_form) {
        global $base_url;
        $storage = $form_state->getStorage();
        $email = $storage['page_one_values']['mo_auth_user_email'];
        $method = $storage['page_three_values']['mo_auth_method'];

        $configureMessage = t('Configure QR Code Authentication');
        if ( $method == AuthenticationType::$SOFT_TOKEN['code']) {
            $configureMessage = t('Configure Soft Token');
        } elseif ( $method == AuthenticationType::$PUSH_NOTIFICATIONS['code']) {
            $configureMessage = t('Configure Push Notification');
        }
        $stepMessage = $this->is2FAResetRequest($form_state)?'':t('(Step 4/5)');

        $customer = new MiniorangeCustomerProfile();
        $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $email, NULL, NULL, NULL);
        $response = $auth_api_handler->register($miniorange_user, AuthenticationType::$QR_CODE['code'], NULL, NULL, NULL);

        if ( $response->status == 'IN_PROGRESS' ) {
            $page = [
                'page_four_values' => [
                    'qrcode_response' => $response
                ],
                'page_three_values' => $storage['page_three_values'],
                'page_two_values' => $storage['page_two_values'],
                'page_one_values' => $storage['page_one_values']
            ];
            $form_state->setStorage($page);

            $qrCode = $response->qrCode;
            $image = new FormattableMarkup('<img class="mo2f_image" src="data:image/jpg;base64, '.$qrCode.'"/>', [':src' => $qrCode]);

            $iPhoneAppLink = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/iphone-google-authenticator-app-link.png');
            $androidAppLink = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/android-google-authenticator-app-link.png');

            $form['actions_31'] = array(
                    '#markup' => '<div class="mo2f-modal">
              <div class="mo2f-modal-content">
                <div class="mo2f-modal-container mo2f-modal-header">' . $configureMessage .' '. $stepMessage.' </div>
                <div class="mo2f-modal-container">'
            );

            $form['actions_32'] = array(
                '#markup' => '<div><div class="mo2f-info"><b class="mo2f_bold">'.t('Step 1:').'</b> '.t('Download and install the miniOrange Authenticator app').'</div>
                <div class="mo2f-text-center" style="display:inline-block;width:60%;text-align:center">
                  <a target="_blank" href="https://itunes.apple.com/us/app/miniorange-authenticator/id796303566?ls=1"><img class="mo2f_image" src="' . $iPhoneAppLink . '"></a>
                </div>
                <div class="mo2f-text-center" style="display:inline-block">
                  <a target="_blank" href="https://play.google.com/store/apps/details?id=com.miniorange.authbeta"><img class="mo2f_image" src="' . $androidAppLink . '"></a>
                </div>
                </div>'
            );

            $form['actions_33'] = array(
                    '#markup' => '<div><div class="mo2f-info"><b class="mo2f_bold">Step 2:</b> Scan the QR Code from miniOrange Authenticator app</div>
                                  <div class="mo2f-info"><b class="mo2f_bold">Note:</b> Once you scan the QR code, page will get automatically submitted (Dont refresh the page).</div>
                                  <div class="mo2f-info mo2f-text-center">'
            );

            $form['actions_qrcode_miniOrange'] = array(
                '#markup' => $image
            );

            $form['actions_34'] = array(
                    '#markup' => '</div></div>'
            );
            $sufix = '</div><div class="mo2f-modal-container mo2f-modal-footer">';

            $form['txId'] = array(
                '#type' => 'hidden',
                '#value' => $response->txId,
                '#suffix' => $sufix
            );

            $form['url'] = array(
                '#type' => 'hidden',
                '#value' => MoAuthConstants::getBaseUrl() . MoAuthConstants::$AUTH_REGISTRATION_STATUS_API,
            );

            $form['actions'] = array(
                '#type' => 'actions'
            );
            $form['actions']['verify'] = array(
                '#type' => 'submit',
                '#value' => t('Save'),
                '#attributes' => array(
                    'class' => array(
                        'hidebutton','mo2f_button'
                    )
                ),
                '#submit' => array('::handle_page_four_submit'),
            );

            $form['actions']['cancel'] = array(
                '#type' => 'submit',
                '#value' => t('Cancel'),
                '#submit' => array('::handle_page_cancel'),
                '#attributes' => array('class' => array('mo2f_button')),
            );
        }

        return $form;
    }

    function mo_auth_get_kba_authentication_form(array $form, FormStateInterface $form_state) {
        $stepMessage = $this->is2FAResetRequest($form_state)?'':t('(Step 5/5)');
        $prefixQuestion1 = '<div class="mo2f-modal">
            <div class="mo2f-modal-content">
            <div class="mo2f-modal-container mo2f-modal-header">'.t('Configure Security Questions').' '.$stepMessage.'</div>
            <div class="mo2f-modal-container">';
        $prefixQuestion1 .= '<div class="mo2f-info">'.t('Please choose your backup security questions:').'</div><div>
            <div class="mo2f-kba-header mo2f-kba-row">
                <div class="mo2f-kba-srno">'.t('Sr. No.').'</div>
                <div class="mo2f-kba-question">'.t('Questions').'</div>
                <div class="mo2f-kba-answer">'.t('Answers').'</div>
            </div>
            <div class="mo2f-kba-row">
            <div class="mo2f-kba-srno">1.</div>
            <div class="mo2f-kba-question">';
        $suffixQuestion1 = '</div>';

        $prefixAnswer1 = '<div class="mo2f-kba-answer">';
        $suffixAnswer1 = '</div></div>';

        $prefixQuestion2 = '<div class="mo2f-kba-row">
            <div class="mo2f-kba-srno">2.</div>
            <div class="mo2f-kba-question">';
        $suffixQuestion2 = '</div>';

        $prefixAnswer2 = '<div class="mo2f-kba-answer">';
        $suffixAnswer2 = '</div></div>';

        $prefixQuestion3 = '<div class="mo2f-kba-row">
            <div class="mo2f-kba-srno">3.</div>
            <div class="mo2f-kba-question">';
        $suffixQuestion3 = '</div>';

        $prefixAnswer3 = '<div class="mo2f-kba-answer">';
        $suffixAnswer3 = '</div></div></div></div><div class="mo2f-modal-container mo2f-modal-footer">';

        $options_one = MoAuthUtilities::mo_get_kba_questions('ONE' );

        $options_two = MoAuthUtilities::mo_get_kba_questions('TWO' );

        $form['mo_auth_question1'] = array(
            '#type' => 'select',
            '#options' => $options_one,
            '#prefix' => $prefixQuestion1,
            '#suffix' => $suffixQuestion1,
            '#attributes' => array(
                'style' => 'width:85%;height: 29px',
            ),
            '#required' => TRUE,
        );
        $form['mo_auth_answer1'] = array(
            '#type' => 'textfield',
            '#prefix' => $prefixAnswer1,
            '#suffix' => $suffixAnswer1,
            '#size' => '20',
            '#attributes' => array(
                'placeholder' => t('Enter your answer'),
                'style' => 'width:85%;',
            ),
            '#required' => TRUE,
        );
        $form['mo_auth_question2'] = array(
            '#type' => 'select',
            '#options' => $options_two,
            '#prefix' => $prefixQuestion2,
            '#suffix' => $suffixQuestion2,
            '#attributes' => array(
                'style' => 'width:85%;height: 29px',
            ),
            '#required' => TRUE,
        );

        $form['mo_auth_answer2'] = array(
            '#type' => 'textfield',
            '#prefix' => $prefixAnswer2,
            '#suffix' => $suffixAnswer2,
            '#attributes' => array(
                'placeholder' => t('Enter your answer'),
                'style' => 'width:85%'
            ),
            '#required' => TRUE,
        );

        $form['mo_auth_question3'] = array(
            '#type' => 'textfield',
            '#prefix' => $prefixQuestion3,
            '#suffix' => $suffixQuestion3,
            '#attributes' => array(
                'placeholder' => t('Enter your custom question here'),
                'style' => 'width:85%'
            ),
            '#required' => TRUE,
        );

        $form['mo_auth_answer3'] = array(
            '#type' => 'textfield',
            '#prefix' => $prefixAnswer3,
            '#suffix' => $suffixAnswer3,
            '#attributes' => array(
                'placeholder' => t('Enter your answer'),
                'style' => 'width:85%'
            ),
            '#required' => TRUE,
        );

        $form['actions'] = array(
            '#type' => 'actions'
        );

        $form['actions']['register'] = array(
            '#type' => 'submit',
            '#value' => t('Register'),
            '#submit' => array('::handle_page_four_submit'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#limit_validation_errors'=> array(),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        return $form;
    }

    function mo_auth_get_otp_over_sms_authentication_form(array $form, FormStateInterface $form_state, $success_status, $message) {

        $storage = $form_state->getStorage();
        $method = $storage['page_three_values']['mo_auth_method'];
        $email = $storage['page_one_values']['mo_auth_user_email'];

        $otp_over_sms           = AuthenticationType::$SMS['code'];
        $otp_over_email         = AuthenticationType::$EMAIL['code'];
        $otp_over_sms_and_email = AuthenticationType::$SMS_AND_EMAIL['code'];
        $otp_over_phone         = AuthenticationType::$OTP_OVER_PHONE['code'];

        if ( $method == $otp_over_sms ) {
            $authmethod = t(AuthenticationType::$SMS['name']);
        } elseif ( $method == $otp_over_sms_and_email ) {
            $authmethod = t(AuthenticationType::$SMS_AND_EMAIL['name']);
        } elseif( $method == $otp_over_email ) {
            $authmethod = t(AuthenticationType::$OTP_OVER_EMAIL['name']);
        } elseif( $method == $otp_over_phone ){
            $authmethod = t(AuthenticationType::$OTP_OVER_PHONE['name']);
        }

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_2fa/miniorange_2fa.admin",
                    "miniorange_2fa/miniorange_2fa.license",
                )
            ),
        );
        $stepMessage = $this->is2FAResetRequest($form_state)?'':' '.t('(Step 4/5)');
        $prefix = '<div class="mo2f-modal">
              <div class="mo2f-modal-content">
                <div class="mo2f-modal-container mo2f-modal-header">'.t('Configure '). $authmethod . $stepMessage.' </div><div class="mo2f-modal-container">';

        if ( $success_status === FALSE ) {
            $prefix .= '<div class="mo2f-message mo2f-message-error">' . $message . '</div>';
        }

        if ( $method == $otp_over_sms || $method == $otp_over_phone ) {
            $step1 = t('Verify your phone number.');
        } elseif ( $method == $otp_over_sms_and_email ) {
            $step1 = t('Verify your phone number and email.');
        } elseif ( $method == $otp_over_email ) {
            $step1 = t('Verify your email.');
        }

        $prefix .= '<div><div class="mo2f-info">' . $step1;
        if ( $method == $otp_over_sms_and_email || $method == $otp_over_email ) {
            $prefix .= '<input type="text" class="mo2f-textbox mo2f-textbox-otp" value="' . $email . '" disabled><br>';
        }

        $prefix .= '</div></div>';
        $sufix   = '</div><div class="mo2f-modal-container mo2f-modal-footer">';
        $request = \Drupal::request();
        $session = $request->getSession();
        $moMfaSession = $session->get("mo_auth",null);
        $phoneNumber  = MoAuthUtilities::getUserPhoneNumber($moMfaSession['uid']);
        if( $method != $otp_over_email ) {
            $form['mo_auth_otpoversms_phone'] = array(
                '#type' => 'textfield',
                '#id'    =>'query_phone',
                '#description' => t('<strong>Note:</strong> Enter number with country code Eg. +00xxxxxxxxxx'),
                '#attributes' => array(
                    'placeholder' => t('Phone Number'),
                    'pattern' => '[\+]?[0-9]{1,4}\s?[0-9]{7,12}',
                    'class' => array(
                        'query_phone',
                        'mo2f-textbox',
                        'mo2f-textbox-otp'
                    ),
                    'autofocus' => TRUE,
                ),
                '#required' => TRUE,
                '#prefix' => $prefix,
                '#suffix' => $sufix
            );

            if(!is_null($phoneNumber)){
              $form['mo_auth_otpoversms_phone']['#default_value']=$phoneNumber;
            }

        } else {
            $form['mo_auth_otpoversms_phone'] = array(
                '#type' => 'textfield',
                '#default_value' => $email,
                '#disabled' => 'true',
                    '#description' => t('<strong>Note: </strong>Your Email Id to which an OTP will be sent'),
                    '#attributes' => array(
                        'class' => array(
                            'mo2f-textbox',
                            'mo2f-textbox-otp'
                        ),
                        'autofocus' => TRUE
                    ),
                    '#required' => TRUE,
                    '#prefix' => $prefix,
                    '#suffix' => $sufix
                );
            }

        $form['actions'] = array(
            '#type' => 'actions'
        );
        $form['actions']['send'] = array(
            '#type' => 'submit',
            '#value' => t('Send OTP'),
            '#submit' => array('::handle_page_four_submit'),
            '#attributes' => array('class' => array('mo2f_button')),
        );
        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#limit_validation_errors'=> array(),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        return $form;
    }

    function mo_auth_get_otp_over_sms_validate_form(array $form, FormStateInterface $form_state, $success_status, $message) {

        $storage                = $form_state->getStorage();
        $method                 = $storage['page_three_values']['mo_auth_method'];
        $otp_over_sms           = AuthenticationType::$SMS['code'];
        $otp_over_sms_and_email = AuthenticationType::$SMS_AND_EMAIL['code'];
        $otp_over_email         = AuthenticationType::$EMAIL['code'];
        $otp_over_phone         = AuthenticationType::$OTP_OVER_PHONE['code'];
        $hardware_token         = AuthenticationType::$HARDWARE_TOKEN['code'];

        $authmethod = '';
        if ( $method == $otp_over_sms) {
            $authmethod = t(AuthenticationType::$SMS['name']);
        }elseif ( $method == $otp_over_sms_and_email ) {
            $authmethod = t(AuthenticationType::$SMS_AND_EMAIL['name']);
        }elseif ( $method == $otp_over_email ) {
            $authmethod = t(AuthenticationType::$OTP_OVER_EMAIL['name']);
        }elseif ( $method == $otp_over_phone ) {
            $authmethod = t(AuthenticationType::$OTP_OVER_PHONE['name']);
        }elseif ( $method == $hardware_token ){
          $authmethod = t(AuthenticationType::$HARDWARE_TOKEN['name']);
        }

        $email = $storage['page_one_values']['mo_auth_user_email'];

        if ( $success_status === TRUE ) {
            if ( $method == $otp_over_sms ) {
                $phone = str_replace( ' ', '', $storage['page_four_values']['mo_auth_otpoversms_phone'] );
                $message = t('We have sent an OTP to  %phone Enter the OTP received to verify your phone.',array('%phone'=> $phone));
            } elseif ( $method == $otp_over_sms_and_email ) {
                $phone = str_replace( ' ', '', $storage['page_four_values']['mo_auth_otpoversms_phone'] );
                $message = t('We have sent an OTP to %phone and %email . Enter the OTP received to verify your phone and email.',array('%phone'=>$phone,'%email'=>$email));
            } elseif ( $method == $otp_over_email ) {
                $message = t('We have sent an OTP to %email . Enter the OTP received to verify your email.',array('%email'=>$email));
            } elseif ( $method == $otp_over_phone ) {
                $phone = str_replace( ' ', '', $storage['page_four_values']['mo_auth_otpoversms_phone'] );
                $message = t('You will receive a call on %phone shortly, which prompts OTP. Please enter the OTP to verify your phone number.',array('%phone'=>$phone));
            } elseif ( $method = $hardware_token ) {
                $message = t('Please press the key from your Yubikey Hardware device.');
            }
        }

        $message_div_class = $success_status === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
        $stepMessage = $this->is2FAResetRequest($form_state)?'':' '.t('(Step 4/5)');
        $prefix = '<div class="mo2f-modal">
              <div class="mo2f-modal-content">
                <div class="mo2f-modal-container mo2f-modal-header">Configure ' . $authmethod . $stepMessage.'</div>
                <div class="mo2f-modal-container"><div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>
                  <div class="mo2f-info">'.t('Enter the passcode:').'</div>
                <div>';

        $suffix = '</div><div class="mo2f-modal-container mo2f-modal-footer">';

        $form['mo_auth_otpoversms_code'] = array(
            '#type' => 'textfield',
            '#attributes' => array(
                'placeholder' => t('Enter the passcode'),
                'class' => array(
                    'mo2f-textbox',
                    'mo2f-textbox-otp'
                ),
                'autofocus' => 'true'
            ),
            '#required' => TRUE,
            '#prefix' => $prefix,
            '#suffix' => $suffix
        );

        $form['actions'] = array(
            '#type' => 'actions'
        );

        $form['actions']['validate'] = array(
            '#type' => 'submit',
            '#value' => t('Validate OTP'),
            '#submit' => array('::handle_page_otp_validate_submit'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#limit_validation_errors'=> array(),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
        );

        return $form;
    }

    function mo_auth_get_hardware_token_validate_form(array $form, FormStateInterface $form_state, $success_status, $message){
      $storage = $form_state->getStorage();
      $method = $storage['page_three_values']['mo_auth_method'];
      $email = $storage['page_one_values']['mo_auth_user_email'];

      $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "miniorange_2fa/miniorange_2fa.admin",
                "miniorange_2fa/miniorange_2fa.license",
            )
        ),
    );
      $stepMessage = $this->is2FAResetRequest($form_state)?'' : ' ' . t('(Step 4/5)');
      $prefix = '<div class="mo2f-modal">
              <div class="mo2f-modal-content">
                <div class="mo2f-modal-container mo2f-modal-header">'.t('Configure ').$method.$stepMessage.'</div><div class="mo2f-modal-container">';

      if ( $success_status === FALSE ) {
        $prefix .= '<div class="mo2f-message mo2f-message-error">' . $message . '</div>';
      }

      $prefix .= '<div><div class="mo2f-info">';

      $passwordResetMessage = $storage['page_one_values']['mo_2fa_new_user']?'<li>'.t('You may have received password reset link. Please reset your password.').'</li>':'';

      $prefix .= '</div></div>';
      $sufix = '</div><div class="mo2f-modal-container mo2f-modal-footer">';
      $form['mo_hardware_step1'] = array(
        '#markup' =>'<div><div class="mo2f-info"><b class="mo2f_bold">'.t('Step 1:</b> configure Yubikey Hardware token as your second factor method.').'</div>
            <div  class="mo_hardware_token">
                  <ul>
                      '.$passwordResetMessage.'
                      <li>'.t('Click').' <a target="_blank" href="' . MoAuthConstants::getBaseUrl() . '/login?username='.$email.'&redirectUrl=' . MoAuthConstants::getBaseUrl() . '/admin/customer/showcustomerconfiguration">'.t('here</a> to login in your 2FA dashboard.').'</li>
                      '.t('<li>Please navigate to <b>Yubikey Hardware Token</b> and click on <b>Configure</b>.</li>').
                      t('<li>Use your Hardware token to enter key.</li>').
                      t('<li>Once you complete the configuration in dashboard click on next button below.</li>').'
                  </ul>
                </div>
                </div>',
        '#prefix'=>$prefix
      );

      $form['actions'] = array(
        '#type' => 'actions'
      );
      $form['actions']['validate'] = array(
        '#type' => 'submit',
        '#value' => t('Next'),
        '#submit' => array('::handle_page_four_submit'),
        '#attributes' => array('class' => array('mo2f_button')),
      );

      $form['actions']['cancel'] = array(
        '#type' => 'submit',
        '#value' => t('Cancel'),
        '#limit_validation_errors'=> array(),
        '#submit' => array('::handle_page_cancel'),
        '#attributes' => array('class' => array('mo2f_button')),
      );
      return $form;
    }

    function handle_page_otp_validate_submit(array $form, FormStateInterface $form_state) {

        $storage = $form_state->getStorage();
        $input = $form_state->getUserInput();
        $otp_code = $input['mo_auth_otpoversms_code'];
        $form_state->setRebuild();
        $challenge_response = $storage[0]['user_challenge_response'];
        $method             = $storage['page_three_values']['mo_auth_method'];
        $email = $storage['page_one_values']['mo_auth_user_email'];
        $customer           = new MiniorangeCustomerProfile();
        $hardware_token     = AuthenticationType::$HARDWARE_TOKEN['code'];

        $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
        if( $method == $hardware_token ) {
          $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $email, NULL, NULL, $hardware_token );
          $validate_response         = $auth_api_handler->validate( $miniorange_user, null, $otp_code, null );
        }
        else
          $validate_response = $this->validate_otp_for_user($challenge_response->txId, $otp_code);

        if ( $validate_response->status == 'SUCCESS' ) {
            $page = [
                'page_five' => TRUE,
                'page_otp_validate_values' => $form_state->getValues(),
                'page_four_values' => $storage['page_four_values'],
                'page_three_values' => $storage['page_three_values'],
                'page_two_values' => $storage['page_two_values'],
                'page_one_values' => $storage['page_one_values'],
            ];
            $_SESSION['success_status'] = TRUE;
            $form_state->setStorage($page);
            return;
        } elseif ( $validate_response->status == 'FAILED' ) {

            //Retain All the values
            $page = [
                'page_otp_validate' => TRUE,
                'page_four_values' => $storage['page_four_values'],[
                    'user_challenge_response' => $storage[0]['user_challenge_response']
                ],
                'page_three_values' => $storage['page_three_values'],
                'page_two_values' => $storage['page_two_values'],
                'page_one_values' => $storage['page_one_values'],
            ];
            $_SESSION['message'] = 'INVALID OTP';
            $_SESSION['success_status'] = FALSE;
            $form_state->setStorage($page);
            $form_state->setRebuild();
            return;
        }
    }

    function handle_page_cancel() {
        $session = MoAuthUtilities::getSession();
         $session->set('mo_auth',array());
        $session->save();
        unset( $_SESSION['message'] );
        $_SESSION['success_status'] = TRUE;
        $url      = Url::fromRoute('user.login')->toString();
        $response = new RedirectResponse($url);
        $response->send();
    }

    public function handle2FAReset(array &$form, FormStateInterface $form_state) {
      $storage = $form_state->getStorage();
      $phone = isset($storage['page_four_values']['mo_auth_otpoversms_phone'])?$storage['page_four_values']['mo_auth_otpoversms_phone']:NULL;

      $form_state->setRebuild();
      $method = $storage['page_three_values']['mo_auth_method'];
      $email = $storage['page_one_values']['mo_auth_user_email'];
      $customer         = new MiniorangeCustomerProfile();
      if(isset($storage['page_one_values']['mo_2fa_reset']) && $storage['page_one_values']['mo_2fa_reset'] === TRUE && $method != AuthenticationType::$KBA['code'] ){
        $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $email, $phone, NULL, $method == AuthenticationType::$AUTHY_AUTHENTICATOR['code'] || $method == AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'] ? AuthenticationType::$GOOGLE_AUTHENTICATOR['code'] : $method );
        // update user info
        $user_api_handler = new UsersAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $user_update_response = $user_api_handler->update($miniorange_user);
        if( $user_update_response->status == 'SUCCESS' ){
          $database = \Drupal::database();
          $database->update('UserAuthenticationType')->fields(['activated_auth_methods'=> AuthenticationType::getAuthType($method)['code']])->condition('miniorange_registered_email', $email,'=')->execute();
          $type = 'status';
          $updateMsg = t('Successfully updated your 2FA method to ').AuthenticationType::getAuthType($method)['name'];
        }
        else{
          $type = 'error';
          $updateMsg = t('Facing issues in updating your 2FA method. Please try in your next login.');
        }
        \Drupal::messenger()->addMessage($updateMsg, $type,TRUE);
        $url      = Url::fromRoute('user.login')->toString();
        $response = new RedirectResponse($url);
        $response->send();
        exit;
      }
    }

    public function is2FAResetRequest(FormStateInterface $form_state){
      $storage = $form_state->getStorage();
      return isset($storage['page_one_values']['mo_2fa_reset']) && $storage['page_one_values']['mo_2fa_reset'] === TRUE ? TRUE : FALSE;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {

    }
}