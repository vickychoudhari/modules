<?php

namespace Drupal\miniorange_2fa\Form;

use Drupal\Core\Form\FormStateInterface;
/**
 * @file
 * Email verification functions.
 */
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\MoAuthConstants;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;
/**
 * Menu callback for email verification.
 */
class test_email_verification extends FormBase
{
    public function getFormId() {
        return 'mo_auth_test_email_verification';
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

        $form['actions'] = array('#type' => 'actions');
        $input = $form_state->getUserInput();
        if ( array_key_exists('txId', $input) === FALSE ) {
            global $base_url;
            $user = User::load(\Drupal::currentUser()->id());
            $user_id = $user->id();
            $utilities = new MoAuthUtilities();
            $custom_attribute = $utilities::get_users_custom_attribute($user_id);

            $user_email = $custom_attribute[0]->miniorange_registered_email;

            /** To check which method ( Push Notification, Email Verification ) is being tested by user */
            $url_parts = MoAuthUtilities::mo_auth_get_url_parts();
            if ( in_array(AuthenticationType::$PUSH_NOTIFICATIONS['id'], $url_parts ) ) {
                $authTypeCode = AuthenticationType::$PUSH_NOTIFICATIONS['code'];
                $messageHeader = t('Push notification has been sent to your miniOrange Authenticator App.');
                $mo_heading = 'Test Push Notification';
                $divMessage = t('Push notification has been sent to your miniOrange Authenticator App.');
            } else {
                $authTypeCode = AuthenticationType::$EMAIL_VERIFICATION['code'];
                $hidden_email = MoAuthUtilities::getHiddenEmail($user_email);
                $messageHeader = t('A verification email is sent to %email . Please click on the accept link to verify your email.',array('%email'=>$hidden_email));
                $mo_heading = 'Test Email Verification';
                $divMessage = t('A verification email has been sent to your <strong>%email</strong> email.',array('%email'=>$hidden_email));
            }

            $customer = new MiniorangeCustomerProfile();
            $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, $authTypeCode);
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->challenge($miniorange_user);
            if ( $response->status == 'SUCCESS' ) {
                $messenger = \Drupal::messenger();
                $messenger->addMessage( $messageHeader, $messenger::TYPE_STATUS );
                /**
                 * Create container to hold @testEmailVerification_PushNotification form elements.
                 */
                $form['mo_test_email_verification_push_notification'] = array(
                    '#type' => 'fieldset',
                    '#title' => t( $mo_heading ),
                    '#attributes' => array( 'style' => 'padding:2% 2% 20% 2%; margin-bottom:2%' ),
                );
                $form['mo_test_email_verification_push_notification']['header']['#markup'] = '<br><hr><br><div class="mo2f-text-center"><div class="mo_auth_font_type">'. $divMessage .'</div><br><div class="mo_auth_font_type mo2f-text-center"><strong>'.t('We are waiting for your approval...').'</strong></div><br>';
                $image_path = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/ajax-loader-login.gif');
                $form['mo_test_email_verification_push_notification']['loader']['#markup'] = '<div class="mo2f-text-center"><img src="' . $image_path . '" /></div>';

                /**
                 * Accessed form mo_authentication.js file
                 */
                $form['mo_test_email_verification_push_notification']['txId'] = array(
                    '#type' => 'hidden',
                    '#value' => $response->txId,
                );
                $form['mo_test_email_verification_push_notification']['url'] = array(
                    '#type' => 'hidden',
                    '#value' => MoAuthConstants::getBaseUrl() . MoAuthConstants::$AUTH_STATUS_API,
                );

                $form['mo_test_email_verification_push_notification']['miniorange_2fa_cancel_button'] = array(
                    '#type' => 'submit',
                    '#value' => t('Cancel Test'),
                    '#button_type' => 'danger',
                    '#submit' => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
                    '#limit_validation_errors'=>array(),
                    '#prefix' => '<br>',
                );

            } else {
                $message = t('An error occurred while processing your request. Please try again.');
                MoAuthUtilities::show_error_or_success_message( $message , 'error' );
            }
        }
        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Test'),
            '#attributes' => array(
                'class' => array(
                    'hidebutton'
                )
            ),
        );

        $form['main_layout_div_end'] = array(
            '#markup' => '</div>',
        );

        MoAuthUtilities::miniOrange_advertise_network_security( $form, $form_state);

        return $form;
    }

    public function submitForm( array &$form, FormStateInterface $form_state ) {
        $form_state->setRebuild();
        $input            = $form_state->getUserInput();
        $txId             = $input['txId'];
        $customer         = new MiniorangeCustomerProfile();
        $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
        $response         = $auth_api_handler->getAuthStatus($txId);
        /** Clear all the messages */
        \Drupal::messenger()->deleteAll();
        // read API response
        if ( $response->status == 'SUCCESS' ) {
            $message = t('You have successfully completed the test.');
            MoAuthUtilities::show_error_or_success_message( $message , 'status');
            return;
        } elseif ( $response->status == 'DENIED' ) {
            $message = t('You have denied the transaction.');
            MoAuthUtilities::show_error_or_success_message( $message , 'error');
            return;
        }
        $message = t('An error occurred while processing your request. Please try again.');
        MoAuthUtilities::show_error_or_success_message( $message , 'error');
        return;
    }
}