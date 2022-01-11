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
use Drupal\Component\Render\FormattableMarkup;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;

/**
 * Menu callback for email verification.
 */
class test_qrcode_authentication extends FormBase
{
    public function getFormId() {
        return 'mo_auth_test_qrcode_authentication';
    }
    public function buildForm(array $form, FormStateInterface $form_state) {
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

        global $base_url;
        $url = $base_url . '/admin/config/people/miniorange_2fa/setup_twofactor';
        $input = $form_state->getUserInput();

        $form['actions'] = array('#type' => 'actions');

        if ( array_key_exists('txId', $input) === FALSE ) {
            $user = User::load(\Drupal::currentUser()->id());
            $user_id = $user->id();
            $utilities = new MoAuthUtilities();
            $custom_attribute = $utilities::get_users_custom_attribute($user_id);
            $user_email = $custom_attribute[0]->miniorange_registered_email;
            $customer = new MiniorangeCustomerProfile();
            $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$QR_CODE['code']);
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());

            $response = $auth_api_handler->challenge($miniorange_user);
            if ( $response->status == 'SUCCESS' ) {
                $message = t('Please scan the below QR Code from miniOrange Authenticator app.');
                \Drupal::messenger()->addMessage( $message, 'status' );
                $qrCode  = $response->qrCode;
                $image   = new FormattableMarkup('<img src="data:image/jpg;base64, '.$qrCode.'"/>', [':src' => $qrCode]);

                /**
                 * Create container to hold @qrCodeAuthentication form elements.
                 */
                $form['mo_test_qr_code_autentication'] = array(
                    '#type' => 'fieldset',
                    '#title' => t( 'Test QR Code Authentication' ),
                    '#attributes' => array( 'style' => 'padding:2% 2% 18% 2%; margin-bottom:2%' ),
                );

                $form['mo_test_qr_code_autentication']['header']['#markup'] = t(
                    '<br><hr><br>
                            <div class="mo2f-text-center">
                                 <div class="mo_auth_font_type">'.t('Please scan the below QR Code with miniOrange Authenticator app to authenticate yourself.').'</div><br>
                                 <div class="mo_auth_font_type"><b><span style="color: red">'.t('Note:').'</span></b> '.t('After scanning below QR code, please wait until the page loads automatically.').'</div><br>'
                );

                $form['mo_test_qr_code_autentication']['actions_qrcode'] = array(
                    '#markup' =>$image
                );

                /** Accessed form mo_authentication.js file **/
                $form['mo_test_qr_code_autentication']['txId'] = array(
                    '#type' => 'hidden',
                    '#value' => $response->txId,
                );
                $form['mo_test_qr_code_autentication']['url'] = array(
                    '#type' => 'hidden',
                    '#value' => MoAuthConstants::getBaseUrl() . MoAuthConstants::$AUTH_STATUS_API,
                );

            } else {
                $message = t('An error occurred while processing your request. Please try again.');
                MoAuthUtilities::show_error_or_success_message($message , 'error');
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
        $form['mo_test_qr_code_autentication']['add_cancel_button'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#button_type' => 'danger',
            '#submit' => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
            '#limit_validation_errors'=>array(),
            '#prefix' => '<br><br>'
        );

        $form['main_layout_div_end'] = array(
            '#markup' => '</div>',
        );

        MoAuthUtilities::miniOrange_advertise_network_security( $form, $form_state);

        return $form;
    }

    /**
     * Form submit handler for email verify.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Clear all the messages
        \Drupal::messenger()->deleteAll();
        $form_state->setRebuild();
        $input            = $form_state->getUserInput();
        $txId             = $input['txId'];
        $customer         = new MiniorangeCustomerProfile();
        $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $response         = $auth_api_handler->getAuthStatus($txId);
        if ( $response->status == 'SUCCESS' ) {
            $message = t('You have successfully completed the test.');
            MoAuthUtilities::show_error_or_success_message($message , 'status');
            return;
        } elseif ($response->status == 'FAILED') {
            $message = t('Authentication failed.');
        } else {
            $message = t('An error occurred while processing your request. Please try again.');
        }
        MoAuthUtilities::show_error_or_success_message($message , 'error');
        return;
    }
}