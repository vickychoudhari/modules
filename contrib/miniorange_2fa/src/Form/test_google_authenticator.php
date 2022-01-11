<?php

namespace Drupal\miniorange_2fa\Form;

/**
 * @file
 * Email verification functions.
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
 * Menu callback for email verification.
 */
class test_google_authenticator extends FormBase
{
    public function getFormId() {
        return 'miniorange_googleAuthenticator';
    }
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['markup_top_2'] = array(
            '#markup' => '<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_2fa_container">'
        );

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => ['miniorange_2fa/miniorange_2fa.admin', 'miniorange_2fa/miniorange_2fa.license'],
            ),
        );

        /**
         * To check which method (Soft Token, Google Authenticator') is being tested by user
         */
        $url_parts   = MoAuthUtilities::mo_auth_get_url_parts();
        if ( in_array(AuthenticationType::$SOFT_TOKEN['id'], $url_parts ) ) {
            $authTypeCode  = AuthenticationType::$SOFT_TOKEN['code'];
            $pageTitle     = 'Test Soft Token';
            $messageHeader = t('Please enter the passcode generated on your miniOrange Authenticator app.');
        } elseif( in_array(AuthenticationType::$GOOGLE_AUTHENTICATOR['id'], $url_parts ) ) {
            $authTypeCode  = AuthenticationType::$GOOGLE_AUTHENTICATOR['code'];
            $pageTitle     = 'Test Google Authenticator';
            $messageHeader = t('Please enter the passcode generated on your Google Authenticator app.');
        } elseif( in_array(AuthenticationType::$MICROSOFT_AUTHENTICATOR['id'], $url_parts ) ) {
            $authTypeCode  = AuthenticationType::$GOOGLE_AUTHENTICATOR['code'];
            $pageTitle     = 'Test Microsoft Authenticator';
            $messageHeader = t('Please enter the passcode generated on your Microsoft Authenticator app.');
        } elseif( in_array(AuthenticationType::$AUTHY_AUTHENTICATOR['id'], $url_parts ) ) {
            $authTypeCode  = AuthenticationType::$GOOGLE_AUTHENTICATOR['code'];
            $pageTitle     = 'Test Authy Authenticator';
            $messageHeader = t('Please enter the passcode generated on your Authy Authenticator app.');
        } elseif( in_array(AuthenticationType::$LASTPASS_AUTHENTICATOR['id'], $url_parts ) ) {
            $authTypeCode  = AuthenticationType::$GOOGLE_AUTHENTICATOR['code'];
            $pageTitle     = 'Test LastPass Authenticator';
            $messageHeader = t('Please enter the passcode generated on your LastPass Authenticator app.');
        } elseif( in_array(AuthenticationType::$DUO_AUTHENTICATOR['id'], $url_parts ) ) {
            $authTypeCode  = AuthenticationType::$GOOGLE_AUTHENTICATOR['code'];
            $pageTitle     = 'Test Duo Authenticator';
            $messageHeader = t('Please enter the passcode generated on your Duo Authenticator app.');
        }

        /**
         * Create container to hold @testGoogleAuthenticator_SoftToken form elements.
         */
        $form['mo_test_google_authenticator'] = array(
            '#type' => 'fieldset',
            '#title' => t( $pageTitle ),
            '#attributes' => array( 'style' => 'padding:2% 2% 30% 2%; margin-bottom:2%;' ),
        );

        $form['mo_test_google_authenticator']['header']['#markup'] = t('<br><hr><br><div class="mo_auth_font_type">' . $messageHeader .'<br><br>');

        $form['mo_test_google_authenticator']['frame'] = array(
            '#type' => 'container',
            '#attributes' => array(
                'class' => 'container-inline'
            )
        );
        $form['mo_test_google_authenticator']['frame']['mo_auth_googleauth_token'] = array(
            '#type' => 'textfield',
            '#maxlength' => 8,
            '#title' => t('Passcode'),
            '#attributes' => array(
                'placeholder' => t('Enter passcode'),
                'style' => 'width:50%;margin-left:3%;',
                'autofocus' => TRUE,
            ),
            '#suffix' => '<br><br>',
        );
        $form['mo_test_google_authenticator']['authTypeCode'] = array(
            '#type' => 'hidden',
            '#value' => $authTypeCode
        );
        $form['mo_test_google_authenticator']['actions']['submit'] = array(
            '#type'        => 'submit',
            '#value'       => t('Verify'),
            '#button_type' => 'primary',
        );
        $form['mo_test_google_authenticator']['actions']['cancel'] = array(
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
        if( empty( $form_state->getValue('mo_auth_googleauth_token' ) ) ) {
            $form_state->setErrorByName('mo_auth_googleauth_token', $this->t('Please enter the passcode.'));
        }
    }

    /**
     * Form submit handler for email verify.
     */
    public function submitForm( array &$form, FormStateInterface $form_state ) {

        $form_state->setRebuild();
        $input            = $form_state->getUserInput();
        $user             = User::load(\Drupal::currentUser()->id());
        $user_id          = $user->id();
        $utilities        = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);
        $user_email       = $custom_attribute[0]->miniorange_registered_email;
        $token            = $input['mo_auth_googleauth_token'];
        $authTypeCode     = $input['authTypeCode'];
        $customer         = new MiniorangeCustomerProfile();
        $miniorange_user  = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, $authTypeCode);
        $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $response         = $auth_api_handler->validate($miniorange_user, '', $token);

        // Clear all the messages
        \Drupal::messenger()->deleteAll();

        // read API response
        if ( $response->status == 'SUCCESS' ) {
            $message = t('You have successfully completed the test.');
            $utilities::show_error_or_success_message($message , 'status');
            return;
        } elseif ( $response->status == 'FAILED' ) {
            \Drupal::messenger()->addMessage(t('The passcode you have entered is incorrect. Please try again.'), 'error');
            return;
        }
        $message = t('An error occurred while processing your request. Please try again.');
        $utilities::show_error_or_success_message($message , 'error');
        return;
    }
}