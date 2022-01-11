<?php

namespace Drupal\miniorange_2fa\form;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\MoAuthConstants;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * @file
 *  This is used to authenticate user during login.
 */
class authenticate_user extends FormBase {
    public function getFormId() {
        return 'mo_auth_authenticate_user';
    }
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        \Drupal::service('page_cache_kill_switch')->trigger();
        global $base_url;
        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_2fa/miniorange_2fa.admin",
                    "miniorange_2fa/miniorange_2fa.license",
                )
            ),
        );


        $session = MoAuthUtilities::getSession();
        $moMfaSession = $session->get("mo_auth", null);
        if (is_null($moMfaSession) || !isset($moMfaSession['status']) || !isset($moMfaSession['uid']) || $moMfaSession['status'] !== '1ST_FACTOR_AUTHENTICATED') {
            return $form;
        }

        $url_parts = MoAuthUtilities::mo_auth_get_url_parts();
        end($url_parts);
        $user_id = prev($url_parts);
        if ($moMfaSession['uid'] != $user_id) {
            return $form;
        }
        $custom_attribute = MoAuthUtilities::get_users_custom_attribute($user_id);

        $user_email = $custom_attribute[0]->miniorange_registered_email;
        if ($moMfaSession['status'] === '1ST_FACTOR_AUTHENTICATED' && $moMfaSession['challenged'] === 0 ) {
            $moMfaSession = $this->mo_auth_challenge_user($form, $user_email, $user_id, $form_state);
        }
        $moMfaSession['challenged'] = 1;
        $session->set('mo_auth', $moMfaSession);
        $session->save();
        $moMfaSession = $session->get("mo_auth",null);

        if( empty( $moMfaSession['mo_challenge_response'] ) ) {
            return 0;
        }

        if ( isset( $moMfaSession['status']) && $moMfaSession['status'] === '1ST_FACTOR_AUTHENTICATED' ) {
            $challenge_response = $moMfaSession['mo_challenge_response'];
            $authType = $challenge_response->authType;
            $form['actions'] = array('#type' => 'actions');
            if (!empty($authType)) {
                $form['authType'] = array(
                    '#type' => 'hidden',
                    '#value' => $authType,
                );
                $authType = AuthenticationType::getAuthType($authType);
                $form = self::mo_auth_build_form($form, $base_url, $authType, $challenge_response, TRUE, $custom_attribute[0]->activated_auth_methods );

                unset($form['mo_message']);

            } else {
                $form['actions']['submit'] = array(
                    '#type' => 'submit',
                    '#value' => t('Save'),
                    '#attributes' => array(
                        'class' => array(
                            'hidebutton'
                        )
                    ),
                );
            }
            return $form;
        }


        $url      = Url::fromRoute('user.login')->toString();
        $response = new RedirectResponse($url);
        $response->send();
        exit;
    }
  public function mo_auth_challenge_user($form, $user_email, $user_id, FormStateInterface $form_state) {

    $customer   = new MiniorangeCustomerProfile();
    $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL,NULL );

    $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
    $response = $auth_api_handler->challenge($miniorange_user);
    $session = MoAuthUtilities::getSession();
    $moMfaSession = $session->get("mo_auth",null);
    if ( is_object( $response ) && $response->status == 'SUCCESS' ) {
      $moMfaSession['mo_challenge_response'] = $response;
      $session->replace(array('mo_auth'=>$moMfaSession));
      $session->save();
      return $moMfaSession;

    } else {
        MoAuthUtilities::mo_add_loggers_for_failures( is_object( $response )? $response->message : '-' , 'error');
        \Drupal::messenger()->addMessage(t('An error occured while processing your request. Please contact administrator.'), 'error', TRUE);
        $url     = Url::fromRoute('user.login')->toString();
        $response = new RedirectResponse($url);
        $response->send();
        exit;
    }
  }

  function mo_auth_authenticate_user_submit(array $form, FormStateInterface $form_state) {
        $input = $form_state->getUserInput();

        $change2FARequest = array_key_exists('mo_auth_change_2fa', $input ) && !is_null( $input['mo_auth_change_2fa'] ) ? TRUE : FALSE;
        $session = MoAuthUtilities::getSession();
        $moMfaSession = $session->get("mo_auth",null);
        $challenge_response = $moMfaSession['mo_challenge_response'];
        $form_state->setRebuild();
        $url_parts = MoAuthUtilities::mo_auth_get_url_parts();
        end($url_parts);
        $user_id = prev($url_parts);


        if(!isset($moMfaSession['uid']) || $moMfaSession['uid']!=$user_id){
          $session->remove('mo_auth');
          MoAuthUtilities::mo_add_loggers_for_failures( 'URL change detected', 'error');
          \Drupal::messenger()->addError(t("Authentication failed try again. URL change detected while login."), TRUE);
          $url = Url::fromRoute('user.login')->toString();
          $response = new RedirectResponse($url);
          $response->send();
          exit;
        }
        $custom_attribute = MoAuthUtilities::get_users_custom_attribute($user_id);

        $user_email = $custom_attribute[0]->miniorange_registered_email;
        $authType = AuthenticationType::getAuthType( $challenge_response->authType );

        if( $authType['code'] == AuthenticationType::$HARDWARE_TOKEN['code'] ) {
          $token            = array_key_exists('token', $input) ? $input['token'] : '';
          $customer         = new MiniorangeCustomerProfile();
          $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$HARDWARE_TOKEN['code'] );
          $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
          $response         = $auth_api_handler->validate( $miniorange_user, null, $token, null );
        }
        else if ( $authType['oob'] === FALSE ) {
            $token = '';
            if ( array_key_exists('token', $input ) )
                $token = $input['token'];
            $txId = '';
            $kba = array();
            if ( $authType['challenge'] === TRUE ) {
                $txId = $challenge_response->txId;
                if ( $challenge_response->authType == AuthenticationType::$KBA['code'] ) {
                    $count  = count( $challenge_response->questions );
                    for ($i = 1; $i <= $count; $i++) {
                        $ques = $input['mo2f_kbaquestion' . $i];
                        $ans = $input['mo2f_kbaanswer' . $i];
                        $qa = array(
                            "question" => $ques,
                            "answer" => $ans,
                        );
                        array_push($kba, $qa);
                    }
                }
            }

            $customer         = new MiniorangeCustomerProfile();
            $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $user_email, NULL, NULL, NULL );
            $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
            $response         = $auth_api_handler->validate( $miniorange_user, $txId, $token, $kba );
        } else {
            $txId             = $input['txId'];
            $customer         = new MiniorangeCustomerProfile();
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response         = $auth_api_handler->getAuthStatus($txId);
        }

        /** read API response */
        if ( $response->status === 'SUCCESS' ) {
            $user = User::load( $user_id );
            user_login_finalize( $user );

            $custom_redirect_url = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_redirect_user_after_login');
            $mo_redirect_url     = isset( $custom_redirect_url ) && !empty( $custom_redirect_url ) ? $custom_redirect_url : Url::fromRoute('user.login')->toString();
            /**
             * if change of 2fa method requested then set $mo_redirect_url to inline url.
             * mark that user has requested to change his method
             */
            if( $change2FARequest ) {
              $mo_redirect_url = Url::fromRoute('miniorange_2fa.inline_registration', ['mo2faresetrequest'=>'reset','user'=>$user->id()])->toString();
            }

            $response = new RedirectResponse( $mo_redirect_url );
            $response->send();
            exit;
        } elseif ( $response->status === 'DENIED' ) {
            $session->remove('mo_auth');
            $session->save();
            \Drupal::messenger()->addError(t('Authentication denied.'), TRUE);
            $url = Url::fromRoute('user.login')->toString();
            $response = new RedirectResponse($url);
            $response->send();
            exit;
        } elseif ( $response->status === 'FAILED' ) {
            $session->remove('mo_auth');
            $session->save();
            MoAuthUtilities::mo_add_loggers_for_failures( $response->message , 'error');
            \Drupal::messenger()->addError(t("Authentication failed try again."), TRUE);
            $url = Url::fromRoute('user.login')->toString();
            $response = new RedirectResponse($url);
            $response->send();
            exit;
        } else {
            $session->remove('mo_auth');
            $session->save();
            MoAuthUtilities::mo_add_loggers_for_failures( $response->message , 'error');
            \Drupal::messenger()->addError(t('An error occured while processing your request. Please try again.'), TRUE);
            $url = Url::fromRoute('user.login')->toString();
            $response = new RedirectResponse($url);
            $response->send();
            exit;
        }
    }

   function mo_auth_build_form( $form, $base_url, $authType, $challenge_response, $success_form, $activated_auth_methods ) {
       $variables_and_values = array(
           'mo_auth_2fa_allow_reconfigure_2fa',
       );
        $mo_db_values = MoAuthUtilities::miniOrange_set_get_configurations( $variables_and_values, 'GET' );

        $form['main-header']['#markup'] = t('<style>#messages div.messages{visibility:hidden;}</style>');
        $form['header']['#markup'] = '<div class="mo2f-modal">
              <div class="mo2f-modal-content">
                <div class="mo2f-modal-container mo2f-modal-header">'.t('Verify your identity').'</div>
                <div class="mo2f-modal-container">';

        $form = $this->mo_auth_build_form_content( $form, $base_url, $authType, $challenge_response, $activated_auth_methods, $success_form );

        if( $mo_db_values['mo_auth_2fa_allow_reconfigure_2fa'] == 'Allowed' ) {
            $form['mo_auth_change_2fa'] = array(
                '#type' => 'checkbox',
                '#title' => t('I want to change/reconfigure my 2FA method.'),
                '#description' => t('<strong>Note:</strong> If you want to change/reconfigure the 2FA method then please check this checkbox and complete the authentication.'),
            );
        }

        $authTypeToHideButton = $authType['code'];
        $hide_button = '';
        if( $authTypeToHideButton == AuthenticationType::$EMAIL_VERIFICATION['code'] || $authTypeToHideButton == AuthenticationType::$QR_CODE['code'] || $authTypeToHideButton == AuthenticationType::$PUSH_NOTIFICATIONS['code'] ) {
            $hide_button = 'hidebutton';
        }

        $form['loader']['#markup'] = '</div><div class="mo2f-modal-container mo2f-modal-footer">';
         $form['actions'] = array(
            '#type' => 'actions'
        );

        $form['actions']['verify'] = array(
            '#type' => 'submit',
            '#value' => t('Verify'),
            '#attributes' => array (
                'class' => array (
                    $hide_button, 'mo2f_button'
                )
            ),
            '#submit' => array('::mo_auth_authenticate_user_submit'),
        );

        $url_parts = MoAuthUtilities::mo_auth_get_url_parts();
        end($url_parts);
        $user_id = prev($url_parts);
        if( $authType['code'] != AuthenticationType::$KBA['code'] && !MoAuthUtilities::mo_auth_is_kba_configured( $user_id ) === FALSE )
        $form['actions']['forgot'] = array(
            '#type' => 'submit',
            '#attributes' => array (
                'class' => array (
                    'mo_auth_forgot_phone_btn_class','mo2f_button'
                )
            ),
            '#value' => t('forgot phone'),
            '#submit' => array('::mo_auth_forgot_phone'),
            '#limit_validation_errors' => array(), //no validation required for forgot phone
        );
        $form['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#submit' => array('::handle_page_cancel'),
            '#attributes' => array('class' => array('mo2f_button')),
            '#limit_validation_errors' => array(),
        );

        return $form;
    }

    function mo_auth_forgot_phone($form,FormStateInterface  $form_state) {
        global $base_url;
        $customer         = new MiniorangeCustomerProfile();
        $url_parts        = MoAuthUtilities::mo_auth_get_url_parts();
        end($url_parts);
        $user_id          = prev($url_parts);
        $custom_attribute = MoAuthUtilities::get_users_custom_attribute( $user_id );
        $user_email       = $custom_attribute[0]->miniorange_registered_email;
        $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$KBA['code'] );
        $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
        $response         = $auth_api_handler->challenge( $miniorange_user );
        $session = MoAuthUtilities::getSession();
        $moMfaSession = $session->get("mo_auth",null);
        if ( $response->status == 'SUCCESS' ) {
          $moMfaSession['mo_challenge_response'] = $response;
          $session->replace(array('mo_auth'=>$moMfaSession));
          $session->save();
          $authType = AuthenticationType::getAuthType( AuthenticationType::$KBA['code'] );
          $form_state->setRebuild();
          return self::mo_auth_build_form( $form, $base_url, $authType, $response, TRUE, '' );
        }
        else {
          global $base_url;
          \Drupal::messenger()->addError(t('An error occurred while processing your request. Please contact the administrator.'),TRUE);
           $url = Url::fromRoute('user.login')->toString();
           $response = new RedirectResponse( $url );
           $response->send();
       }
    }

    function handle_page_cancel() {
        $url = Url::fromRoute('user.login')->toString();
        $response = new RedirectResponse($url);
        $response->send();
    }

    function mo_auth_build_form_content($form, $base_url, $authType, $challenge_response, $activated_auth_methods, $success_form ) {
        switch ( $authType['code'] ) {
            case AuthenticationType::$EMAIL_VERIFICATION['code']:
                return self::mo_auth_build_oobemail_form($form, $base_url, $challenge_response);
            case AuthenticationType::$GOOGLE_AUTHENTICATOR['code']:
                return self::mo_auth_build_google_authenticator_form($form, $activated_auth_methods, $success_form);
            case AuthenticationType::$QR_CODE['code']:
                return self::mo_auth_build_qrcode_authentication_form($form, $challenge_response);
            case AuthenticationType::$KBA['code']:
                return self::mo_auth_build_kba_authentication_form($form, $challenge_response, $success_form);
            case AuthenticationType::$SOFT_TOKEN['code']:
                return self::mo_auth_build_soft_token_form($form, $success_form);
            case AuthenticationType::$PUSH_NOTIFICATIONS['code']:
                return self::mo_auth_build_push_notifications_form($form, $base_url );
            case AuthenticationType::$SMS['code']:
                return self::mo_auth_build_otp_over_sms_form($form, $success_form);
            case AuthenticationType::$SMS_AND_EMAIL['code']:
                return self::mo_auth_build_otp_over_sms_and_email_form($form, $success_form);
            case AuthenticationType::$EMAIL['code']:
                return self::mo_auth_build_otp_over_email_form($form, $success_form);
            case AuthenticationType::$OTP_OVER_PHONE['code']:
                return self::mo_auth_build_otp_over_phone_form($form, $success_form);
            case AuthenticationType::$HARDWARE_TOKEN['code']:
                return self::mo_auth_build_hardware_token_authenticator_form($form, $success_form);
            default:
                return $form;
        }
    }

  function mo_auth_build_hardware_token_authenticator_form($form, $success_message = TRUE ) {
    if ( $success_message === TRUE ) {
      $message = t('Please press the key from your Yubikey Hardware device.');
    } else {
      $message = t('The passcode you have entered is incorrect. Please enter the valid passcode.');
    }

    $message_div_class = $success_message === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
    $form['header']['#markup'] .= t('<div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>');
    $form['header']['#markup'] .= t('<div class="mo2f-info">Enter the passcode: </div>');
    $form['token'] = array(
      '#type' => 'textfield',
      '#attributes' => array('placeholder' => t('Enter  the code.'),
        'class' => array('mo2f-textbox', 'mo2f-textbox-otp'),
        'autofocus' => TRUE,
      ),
      '#required' => TRUE,
    );

    $form['mo_message'] = $message;
    return $form;
  }

    function mo_auth_build_google_authenticator_form($form, $activated_auth_methods, $success_message = TRUE ) {

        $app_name = 'Google';
        if( $activated_auth_methods == AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'] ) {
            $app_name = 'Microsoft';
        } elseif ( $activated_auth_methods == AuthenticationType::$AUTHY_AUTHENTICATOR['code'] ) {
            $app_name = 'Authy';
        } elseif ( $activated_auth_methods == AuthenticationType::$LASTPASS_AUTHENTICATOR['code'] ) {
            $app_name = 'LastPass';
        } elseif ( $activated_auth_methods == AuthenticationType::$DUO_AUTHENTICATOR['code'] ) {
            $app_name = 'Duo';
        }

        if ( $success_message === TRUE ) {
            $message = t('Please enter the passcode generated on your %appName Authenticator app.',array('%appName'=>$app_name));
        } else {
            $message = t('The passcode you have entered is incorrect. Please enter the valid passcode.');
        }

        $message_div_class = $success_message === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
        $form['header']['#markup'] .= t('<div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>');
        $form['header']['#markup'] .= t('<div class="mo2f-info">Enter the passcode: </div>');
        $form['token'] = array(
            '#type' => 'textfield',
            '#attributes' => array('placeholder' => t('Enter passcode'),
                'class' => array('mo2f-textbox', 'mo2f-textbox-otp'),
                'autofocus' => TRUE,
            ),
            '#required' => TRUE,
            '#maxlength' => 8,
        );

        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_qrcode_authentication_form($form, $challenge_response ) {

        $message = t('Please scan the below QR Code from miniOrange Authenticator app to authenticate yourself.');
        $form['header']['#markup'] .= t('<div class="mo2f-message mo2f-message-status">' . $message . '</div>');
        $form['header1']['#markup'] = '<div class="mo2f-text-center">';

        $image = new FormattableMarkup('<img class="mo2f_image" src="data:image/jpg;base64, '. $challenge_response->qrCode .'"/>', [':src' => $challenge_response->qrCode] );

        $form['actions_qrcode'] = array(
            '#markup' =>$image
        );

        $form['header2']['#markup'] = '</div>';

        $form['txId'] = array(
            '#type' => 'hidden',
            '#value' => $challenge_response->txId,
        );
        $form['url'] = array(
            '#type' => 'hidden',
            '#value' => MoAuthConstants::getBaseUrl() . MoAuthConstants::$AUTH_STATUS_API,
        );
        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_oobemail_form( $form, $base_url, $challenge_response ) {
        $session = MoAuthUtilities::getSession();
        $moMfaSession = $session->get("mo_auth",null);
        $response     = $moMfaSession['mo_challenge_response'];
        $user_email   = $challenge_response->emailDelivery->contact;
        $hidden_email = MoAuthUtilities::getHiddenEmail($user_email);

        $message      = t('A verification email is sent to <b class="mo2f_bold"> %email </b>. Please click on accept link to verify your email.',array('%email'=>$hidden_email));

        $form['header']['#markup'] .= t('<div class="mo2f-message mo2f-message-status">' . $message . '</div>');

        $form['header']['#markup'] .= ('<div class="mo2f-info mo2f-text-center">'.t('A verification email is sent to your registered email.').'</div>
                  <div class="mo2f-info mo2f-text-center">'.t('We are waiting for your approval...').'</div>');
        $image_path = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/ajax-loader-login.gif');
        $form['header']['#markup'] .= '<div class="mo2f-text-center"><img class="mo2f_image" src="' . $image_path . '"></div>';

        $form['txId'] = array(
            '#type'  => 'hidden',
            '#value' => $response->txId,
        );
        $form['url'] = array(
            '#type'  => 'hidden',
            '#value' =>MoAuthConstants::getBaseUrl() . MoAuthConstants::$AUTH_STATUS_API,
        );

        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_kba_authentication_form($form, $challenge_response, $success_message ) {
        if ( $success_message === TRUE ) {
            $message = t('Please answer the following questions.');
        } else {
            $message = t('The answers you have entered are incorrect.');
        }

        $message_div_class = $success_message === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
        $form['header']['#markup'] .= t('<div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>');
        $i = 0;

        $questions = isset( $challenge_response->questions ) ? $challenge_response->questions : '';
        if( is_array( $questions ) ) {
            foreach ( $questions as $ques ) {
                $i++;
                $form['mo2f_kbaanswer'.$i] = array(
                    '#type' => 'textfield',
                    '#title' => t($i. '. ' . $ques->question),
                    '#required' => TRUE,
                    '#attributes' => array(
                        'placeholder' => t('Enter your answer'),
                    ),
                );
                $form['mo2f_kbaquestion'.$i] = array(
                    '#type' => 'hidden',
                       '#value' => $ques->question,
                );
            }
        }

        $form['txId'] = array(
            '#type' => 'hidden',
            '#value' => isset( $challenge_response->txId ) ? $challenge_response->txId : '',
        );

        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_soft_token_form($form, $success_message = TRUE ) {
        if ( $success_message === TRUE )
            $message = t('Please enter the passcode generated on your miniOrange Authenticator app.');
        else
            $message = t('The passcode you have entered is incorrect. Please enter the valid  passcode.');

        $message_div_class = $success_message === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
        $form['header']['#markup'] .= t('<div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>');
        $form['header']['#markup'] .= ('<div class="mo2f-info">'.t('Enter the passcode:').'</div>');
        $form['token'] = array(
            '#type' => 'textfield',
            '#attributes' => array('placeholder' => t('Enter passcode'),
                'class' => array('mo2f-textbox', 'mo2f-textbox-otp'),
                'autofocus' => TRUE,
            ),
            '#required' => TRUE,
            '#maxlength' => 8,
        );

        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_push_notifications_form($form, $base_url ) {
        $session      = MoAuthUtilities::getSession();
        $moMfaSession = $session->get("mo_auth",null);
        $response     = $moMfaSession['mo_challenge_response'];
        $message      = t('Please accept the push notification sent to your miniOrange Authenticator App.');
        $form['header']['#markup'] .= t('<div class="mo2f-message mo2f-message-status">' . $message . '</div>');
        $form['header']['#markup'] .= ('<div class="mo2f-info mo2f-text-center">'.t('A Push Notification has been sent to your miniOrange Authenticator App.').'</div>
                  <div class="mo2f-info mo2f-text-center">'.t('We are waiting for your approval...').'</div>');
        $image_path = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/ajax-loader-login.gif');
        $form['header']['#markup'] .= '<div class="mo2f-text-center"><img class="mo2f_image" src="' . $image_path . '"></div>';

        $form['txId'] = array(
            '#type' => 'hidden',
            '#value' => $response->txId,
        );
        $form['url'] = array(
            '#type' => 'hidden',
            '#value' => MoAuthConstants::getBaseUrl() . MoAuthConstants::$AUTH_STATUS_API,
        );

        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_otp_over_sms_form($form, $success_message = TRUE ) {
        if ( $success_message === TRUE )
            $message = t('Please enter the OTP sent to your registered mobile number.');
        else
            $message = t('The OTP you have entered is incorrect. Please enter the valid OTP.');

        $message_div_class = $success_message === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
        $form['header']['#markup'] .= t('<div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>');

        $form['token'] = array(
            '#type' => 'textfield',
            '#title' => t('Please enter the OTP you received:'),
            '#attributes' => array(
                'autofocus' => TRUE,
                'placeholder' => t('Enter OTP'),
            ),
            '#required' => TRUE,
            '#maxlength' => 8,
        );
        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_otp_over_phone_form($form, $success_message = TRUE ) {
        if ( $success_message === TRUE )
            $message = t('Please enter the OTP prompted on phone call.');
        else
            $message = t('The OTP you have entered is incorrect. Please enter the valid OTP.');

        $message_div_class = $success_message === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
        $form['header']['#markup'] .= t('<div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>');
        $form['header']['#markup'] .= t('<div class="mo2f-info">Enter the passcode:</div>');

        $form['token'] = array(
            '#type' => 'textfield',
            '#title' => t('Please enter the OTP prompted on phone call'),
            '#attributes' => array(
                'autofocus' => TRUE,
                'placeholder' => t('Enter OTP'),
            ),
            '#required' => TRUE,
            '#maxlength' => 8,
        );

        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_otp_over_sms_and_email_form($form, $success_message = TRUE ) {
        if ($success_message === TRUE)
            $message = t('Please enter the OTP sent to your registered mobile number and email.');
        else
            $message = t('The OTP you have entered is incorrect. Please enter the valid OTP.');

        $message_div_class = $success_message === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
        $form['header']['#markup'] .= t('<div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>');
        $form['header']['#markup'] .= t('<div class="mo2f-info">Enter the passcode:</div>');

        $form['token'] = array(
            '#type' => 'textfield',
            '#title' => t('Please enter the OTP you received:'),
            '#attributes' => array(
                'autofocus' => TRUE,
                'placeholder' => t('Enter OTP'),
            ),
            '#required' => TRUE,
            '#maxlength' => 8,
        );

        $form['mo_message'] = $message;
        return $form;
    }

    function mo_auth_build_otp_over_email_form($form, $success_message = TRUE ) {
        if ( $success_message === TRUE )
            $message = t('Please enter the OTP sent to your registered email.');
        else
            $message = t('The OTP you have entered is incorrect. Please enter the valid OTP.');

        $message_div_class = $success_message === TRUE ? 'mo2f-message-status' : 'mo2f-message-error';
        $form['header']['#markup'] .= t('<div class="mo2f-message ' . $message_div_class . '">' . $message . '</div>');
        $form['header']['#markup'] .= t('<div class="mo2f-info">Enter the passcode:</div>');

        $form['token'] = array(
            '#type' => 'textfield',
            '#title' => t('Please enter the OTP you received:'),
            '#attributes' => array(
                'autofocus' => TRUE,
                'placeholder' => t('Enter OTP'),
            ),
            '#required' => TRUE,
            '#maxlength' => 8,
        );
        $form['mo_message'] = $message;
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {

    }
}