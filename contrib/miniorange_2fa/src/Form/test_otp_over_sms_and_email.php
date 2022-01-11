<?php

namespace Drupal\miniorange_2fa\Form;

/**
 * @file
 * OTP Over SMS and Email(test) functions.
 */

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;


/**
 * Menu callback for testing OTP Over SMS and Email.
 */
class test_otp_over_sms_and_email extends FormBase
{
    public function getFormId() {
        return 'miniorange_otp_over_sms_and_email';
    }
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['markup_top_2'] = array(
            '#markup' => '<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_2fa_container">'
        );

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_2fa/miniorange_2fa.admin",
                    "miniorange_2fa/miniorange_2fa.license",
                )
            ),
        );

        $user             = User::load(\Drupal::currentUser()->id());
        $user_id          = $user->id();
        $custom_attribute = MoAuthUtilities::get_users_custom_attribute($user_id);
        $user_email       = $custom_attribute[0]->miniorange_registered_email;
        $user_phone       = \Drupal::config('miniorange_2fa.settings')->get('mo_phone');

        /**
         * To check which method (OTP Over Email, OTP Over SMS, OTP Over Email and SMS, OTP Over Phone') is being tested by user
         */
        $url_parts   = MoAuthUtilities::mo_auth_get_url_parts();
        if ( in_array(AuthenticationType::$OTP_OVER_EMAIL['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$OTP_OVER_EMAIL['code'];
            $messageHeader = t('An OTP has been sent to <strong>%email</strong>. Please enter it here to complete the test.',array('%email'=>$user_email));
            $pageTitle = t('Test OTP Over Email');
            $divMessage = t('Please enter the passcode sent to your <strong>%email</strong>.',array('%email'=>$user_email));
        } elseif ( in_array(AuthenticationType::$SMS['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$SMS['code'];
            $messageHeader = t('An OTP has been sent to <strong>%phone</strong>. Please enter it here to complete the test.',array('%phone'=>$user_phone));
            $pageTitle = t('Test OTP Over SMS');
            $divMessage = t('Please enter the passcode sent to your <strong>%phone</strong>.',array('%phone'=>$user_phone));
        } elseif ( in_array(AuthenticationType::$SMS_AND_EMAIL['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$SMS_AND_EMAIL['code'];
            $messageHeader = t('An OTP has been sent to <strong>%phone</strong> and <strong>%email</strong>. Please enter it here to complete the test.',array('%email'=>$user_email,'%phone'=>$user_phone));
            $pageTitle = t('Test OTP Over SMS and Email');
            $divMessage = t('Please enter the passcode sent to your <strong>%phone</strong> and <strong>%email</strong>.',array('%email'=>$user_email,'%phone'=>$user_phone));
        } elseif ( in_array(AuthenticationType::$OTP_OVER_PHONE['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$OTP_OVER_PHONE['code'];
            $messageHeader = t('You will get a call on <strong>%phone</strong> shortly, which prompts OTP. Please enter the OTP to verify your phone number.',array('%phone'=>$user_phone));
            $pageTitle = t('Test OTP Over Phone Call');
            $divMessage = t('Please enter the passcode you received over phone call on <strong>%phone</strong>.',array('%phone'=>$user_phone));
        } elseif ( in_array(AuthenticationType::$HARDWARE_TOKEN['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$HARDWARE_TOKEN['code'];
            $messageHeader = t('Please use your configured<strong> Yubikey Hardware Token</strong> to complete the test.');
            $pageTitle = t('Test Yubikey Hardware Token');
            $divMessage = t('<strong>1.</strong> Insert your Yubikey Hardware Token into a USB port.<br><strong>2.</strong> When the otp is entered in the field below, click on Verify to complete the test.');
        }

        $txId_Value = \Drupal::config('miniorange_2fa.settings')->get('txId_Value');
        if( $txId_Value == 'EMPTY_VALUE' && $authTypeCode != AuthenticationType::$HARDWARE_TOKEN['code'] ) {
            $customer = new MiniorangeCustomerProfile();
            $miniorange_user = new MiniorangeUser( $customer->getCustomerID(), NULL, $user_phone, NULL, $authTypeCode, $user_email );
            $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
            $response = $auth_api_handler->challenge( $miniorange_user );
            if( isset( $response->status ) && $response->status != 'SUCCESS' ) {
                $message = t('An error occured while sending passcode. <em> ' . $response->message . ' </em>');
                MoAuthUtilities::show_error_or_success_message($message , 'error');
            }else {
                \Drupal::messenger()->addMessage( $messageHeader, 'status');
            }
            /** Store txId */
            \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('txId_Value', $response->txId)->save();
        }

        /**
         * Create container to hold @testOTP_Over_SMS_Email_Phone form elements.
         */
        $form['mo_test_otp_over_sms_email_phone'] = array(
            '#type' => 'fieldset',
            '#title' => $pageTitle,
            '#attributes' => array( 'style' => 'padding:2% 2% 30% 2%; margin-bottom:2%' ),
        );

        $form['mo_test_otp_over_sms_email_phone']['header']['#markup'] = t('<br><hr><br><div class="mo_auth_font_type">' . $divMessage . '</div><br>');
        $form['mo_test_otp_over_sms_email_phone']['frame'] = array(
            '#type' => 'container',
            '#attributes' => array(
                'class' => 'container-inline'
            )
        );
        $form['mo_test_otp_over_sms_email_phone']['frame']['mo_auth_otpoversms_and_email_token'] = array(
            '#type' => 'textfield',
            '#title' => t('Passcode').'<span style="color: red">*</span>',
            '#attributes' => array(
                'placeholder' => t('Enter passcode'),
                'style' => 'width:60%;margin-left:3%;',
                'autofocus' => TRUE,
            ),
            '#suffix' => '<br><br>',
        );

        $form['mo_test_otp_over_sms_email_phone']['authTypeCode'] = array(
            '#type' => 'hidden',
            '#value' => $authTypeCode
        );

        $form['mo_test_otp_over_sms_email_phone']['actions_form_submit'] = array(
            '#type' => 'submit',
            '#value' => t('Verify'),
            '#button_type' => 'primary'
        );

        $form['mo_test_otp_over_sms_email_phone']['actions_form_cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel Test'),
            '#button_type' => 'danger',
            '#submit' => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
            '#limit_validation_errors'=>array(),
        );

        $form['main_layout_div_end'] = array(
            '#markup' => '<br></div>',
        );

       MoAuthUtilities::miniOrange_advertise_network_security( $form, $form_state);

       return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        if( empty( $form_state->getValue('mo_auth_otpoversms_and_email_token' ) ) ) {
            $form_state->setErrorByName('mo_auth_otpoversms_and_email_token', $this->t('Please enter the passcode first.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Clear all the messages
        \Drupal::messenger()->deleteAll();

        $form_values      = $form_state->getValues();
        $token            = $form_values['mo_auth_otpoversms_and_email_token'];
        $authTypeCode     = $form_values['authTypeCode'];
        $user             = User::load( \Drupal::currentUser()->id() );
        $custom_attribute = MoAuthUtilities::get_users_custom_attribute( $user->id() );
        $user_email   = $custom_attribute[0]->miniorange_registered_email;
        $user_phone       = NULL;
        $txId             = NULL;
        if( $authTypeCode != AuthenticationType::$HARDWARE_TOKEN['code'] ) {
            $txId         = \Drupal::config('miniorange_2fa.settings')->get('txId_Value');
            $user_phone   = \Drupal::config('miniorange_2fa.settings')->get('mo_phone');
        }
        $customer         = new MiniorangeCustomerProfile();
        $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $user_email, $user_phone, NULL, $authTypeCode, NULL );
        $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $response         = $auth_api_handler->validate( $miniorange_user, $txId, $token, NULL );
        /** read API response */
        if ( $response->status == 'SUCCESS' ) {
            $message = t('You have successfully completed the test.');
            MoAuthUtilities::show_error_or_success_message( $message , 'status' );
            return;
        } elseif ( $response->status == 'FAILED' ) {
            \Drupal::messenger()->addMessage(t('The passcode you have entered is incorrect. Please try again.'), 'error' );
            return;
        }
        $message = t('An error occurred while processing your request. Please try again.');
        MoAuthUtilities::show_error_or_success_message( $message , 'error' );
        return;
    }
}