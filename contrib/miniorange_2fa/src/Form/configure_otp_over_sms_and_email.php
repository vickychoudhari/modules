<?php
namespace Drupal\miniorange_2fa\form;

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\UsersAPIHandler;
use Drupal\miniorange_2fa\MoAuthConstants;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\miniorange_2fa\MiniorangeCustomerSetup;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;

class configure_otp_over_sms_and_email extends FormBase
{
    public function getFormId() {
        return 'miniorange_configure_otp_over_sms_and_email';
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
        $phoneNumber      = MoAuthUtilities::getUserPhoneNumber($user_id);
        $utilities        = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);
        $user_email       = $custom_attribute[0]->miniorange_registered_email;

        /**
         * To check which method (OTP Over Email, OTP Over SMS, OTP Over Email and SMS, OTP Over Phone') is being configured by user
         */
        $url_parts   = $utilities::mo_auth_get_url_parts();
        if ( in_array(AuthenticationType::$OTP_OVER_EMAIL['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$OTP_OVER_EMAIL['code'];
            $pageTitle = 'Configure OTP Over Email';
            $note = '<li>Customize Email template.</li>';
            $method_name  = strtoupper( AuthenticationType::$OTP_OVER_EMAIL['name'] );
        } elseif ( in_array(AuthenticationType::$SMS['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$SMS['code'];
            $pageTitle = 'Configure OTP Over SMS';
            $note = '<li>Customize SMS template.</li>';
            $method_name  = strtoupper( AuthenticationType::$SMS['name'] );
        } elseif ( in_array(AuthenticationType::$SMS_AND_EMAIL['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$SMS_AND_EMAIL['code'];
            $pageTitle = 'Configure OTP Over SMS and Email';
            $note = '<li>Customize Email template.</li><li>Customize SMS template.</li>';
            $method_name  = strtoupper( AuthenticationType::$SMS_AND_EMAIL['name'] );
        } elseif ( in_array(AuthenticationType::$OTP_OVER_PHONE['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$OTP_OVER_PHONE['code'];
            $pageTitle    = 'Configure OTP Over Phone Call';
            $note         = '<li>Customize SMS template.</li>';
            $method_name  = strtoupper( AuthenticationType::$OTP_OVER_PHONE['name'] );
        } elseif ( in_array(AuthenticationType::$HARDWARE_TOKEN['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$HARDWARE_TOKEN['code'];
            $pageTitle    = 'Configure Yubikey Hardware Token';
            $note         = '';
            $method_name  = '';
        }

        $mo_note          = "<ul>$note<li>".t('Customize OTP Length and Validity.').'</li><li>'.t('For customization goto').' <a href="' . MoAuthUtilities::get_mo_tab_url( 'LOGIN' ) . '">'.t('Login Settings').'</a> '.t('tab and navigate to').' <u>'.t('CUSTOMIZE SMS AND EMAIL TEMPLATE').'</u> '.t('section.').'</li></ul>';

        /**
         * Create container to hold @ConfigureOTP_Over_SMS_Email_Phone form elements.
         */
        $form['mo_configure_otp_over_sms_email_phone'] = array(
            '#type' => 'fieldset',
            '#title' => t( $pageTitle ),
            '#attributes' => array( 'style' => 'padding:2% 2% 30% 2%; margin-bottom:2%' ),
        );

        /**
         * Show Yubikey configuration form
         */
        if( $authTypeCode === AuthenticationType::$HARDWARE_TOKEN['code'] ) {
            $mo_dashboard_url =  MoAuthConstants::getBaseUrl() . '/login?username=' . $user_email . '&redirectUrl=' . MoAuthConstants::getBaseUrl() . '/admin/customer/showcustomerconfiguration';

            $form['mo_configure_otp_over_sms_email_phone']['mo_configure_hardware_token_instruction'] = array(
                '#markup'=>'<br><hr><br><div class="mo_hardware_token_line_height">
                      <div>'.t('Please follow below-mentioned steps to configure the Yubikey Hardware Token:').'</div> 
                      <ol>
                          <li>'.t('Click').' <a target="_blank" href="'. $mo_dashboard_url .'"><strong>'.t('here').'</strong></a> '.t('to login into your miniOrange dashboard.').'</li>
                          <li>'.t('Please navigate to').'<strong>'.t('YUBIKEY HARDWARE TOKEN').'</strong> '.t('and click on ').'<strong>'.t('Configure').'</strong>.</li>
                          <li>'.t('Follow the instructions to configure the Yubikey.').'</li>
                          <li>'.t('When you are done with the configuration, please click on the <strong>Done</strong> button below.</li>').'
                      </ol>
                    </div><br>',

            );

            $form['mo_configure_otp_over_sms_email_phone']['cancel'] = array(
                '#type'        => 'submit',
                '#value'       => t('Done'),
                '#button_type' => 'primary',
                '#submit'      => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
                '#limit_validation_errors' => array(),
                '#suffix'      => '</div>'
            );

            $utilities::miniOrange_advertise_network_security($form, $form_state);

            return $form;
        }


        $form['mo_configure_otp_over_sms_email_phone']['header']['#markup'] = t('<br><hr><br><div class="mo_2fa_highlight_background_note"><strong>You can customize the following things of the ' . $method_name . ' method:</strong>'. $mo_note .'</div><br>');

        if ( $authTypeCode == AuthenticationType::$EMAIL['code'] || $authTypeCode == AuthenticationType::$SMS_AND_EMAIL['code'] ) {
            $form['mo_configure_otp_over_sms_email_phone']['miniorange_email'] = array(
                '#type' => 'textfield',
                '#title' => t('Verify Your Email').' <span style="color: red">*</span>',
                '#value' => $user_email,
                '#attributes' => array(
                    'style' => 'width:60%'
                ),
                '#disabled' => TRUE
            );
        }
        if( $authTypeCode == AuthenticationType::$SMS['code'] || $authTypeCode == AuthenticationType::$SMS_AND_EMAIL['code'] || $authTypeCode == AuthenticationType::$OTP_OVER_PHONE['code'] ) {
            $form['mo_configure_otp_over_sms_email_phone']['miniorange_phone'] = array(
                '#type' => 'textfield',
                '#title' => t('Verify Your Phone number') .'<span style="color: red">*</span>',
                '#id' => 'query_phone',
                '#default_value' => $phoneNumber,
                '#description' => t('<strong>Note:</strong> Enter phone number with country code Eg. +00xxxxxxxxxx'),
                '#attributes' => array(
                    'class' => array('query_phone',),
                    'pattern' => '[\+]?[0-9]{1,4}\s?[0-9]{7,12}',
                    'placeholder' => t('Enter phone number with country code Eg. +00xxxxxxxxxx'),
                    'style' => 'width:60%'
                ),
            );
        }

        $form['mo_configure_otp_over_sms_email_phone']['verifyphone'] = array(
            '#type' => 'submit',
            '#value' => t('Verify'),
            '#button_type' => 'primary',
            '#submit' => array('::mo_auth_configure_otp_over_sms_and_email_submit'),
        );

        $form['mo_configure_otp_over_sms_email_phone']['miniorange_saml_customer_setup_resendotp'] = array(
            '#type' => 'submit',
            '#value' => t('Resend OTP'),
            '#submit' => array('::mo_auth_configure_otp_over_sms_and_email_submit'),
            '#suffix' => '<br><br>',
        );

        $form['mo_configure_otp_over_sms_email_phone']['miniorange_OTP'] = array(
            '#type' => 'textfield',
            '#maxlength' => 8,
            '#attributes' => array(
                'placeholder' => t('Enter passcode'),
                'style' => 'width:60%'
            ),
            '#title' => t('OTP <span style="color: red">*</span>'),
        );

        $form['mo_configure_otp_over_sms_email_phone']['authTypeCode'] = array(
            '#type' => 'hidden',
            '#value' => $authTypeCode
        );

        $form['mo_configure_otp_over_sms_email_phone']['miniorange_saml_customer_validate_otp_button'] = array(
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => t('Validate OTP'),
            '#submit' => array('::miniorange_saml_validate_otp_submit'),
        );

        $form['mo_configure_otp_over_sms_email_phone']['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#button_type' => 'danger',
            '#submit' => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
            '#limit_validation_errors'=>array(),
        );

        $form['main_layout_div_end'] = array(
            '#markup' => '<br></div>',
        );

        $utilities::miniOrange_advertise_network_security( $form, $form_state);

        return $form;
    }

    function mo_auth_configure_otp_over_sms_and_email_submit( array &$form, FormStateInterface $form_state ) {
        $form_state->setRebuild();
        $form_values      = $form_state->getValues();
        $customer         = new MiniorangeCustomerProfile();
        $custID           = $customer->getCustomerID();
        $api_key          = $customer->getAPIKey();
        $user             = User::load(\Drupal::currentUser()->id());
        $user_id          = $user->id();
        $utilities        = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute( $user_id );
        $user_email       = $custom_attribute[0]->miniorange_registered_email;
        $phone_number     = isset( $form_values['miniorange_phone'] ) ? str_replace( ' ', '', $form_values['miniorange_phone'] ) : '';

        if( $form_values['authTypeCode'] === AuthenticationType::$OTP_OVER_EMAIL['code'] ) {
            $currentMethod = "OTP_OVER_EMAIL";
            $params = array('email' => $user_email );
            $mo_status_message = "We have sent an OTP to <strong>$user_email</strong>. Please enter the OTP to verify your email.";
        } elseif ( $form_values['authTypeCode'] === AuthenticationType::$SMS['code'] ) {
            $currentMethod = "OTP_OVER_SMS";
            $params = array('phone' => $phone_number );
            $mo_status_message = "We have sent an OTP to <strong>$phone_number</strong>. Please enter the OTP to verify your phone number.";
        } elseif ( $form_values['authTypeCode'] === AuthenticationType::$SMS_AND_EMAIL['code'] ) {
            $currentMethod = "OTP_OVER_SMS_AND_EMAIL";
            $params = array('phone' => $phone_number, 'email' => $user_email );
            $mo_status_message = "We have sent an OTP to <strong>$user_email</strong> and <strong>$phone_number</strong>. Please enter the OTP to verify your email and phone number.";
        } elseif ( $form_values['authTypeCode'] === AuthenticationType::$OTP_OVER_PHONE['code'] ) {
            $currentMethod = "PHONE_VERIFICATION";
            $params = array('phone' => $phone_number );
            $mo_status_message = "You will receive phone call on <strong>$phone_number</strong> shortly, which prompts OTP. Please enter the OTP to verify your phone number.";
        }

        $customer_config = new MiniorangeCustomerSetup( $user_email, $phone_number, NULL, NULL );
        $send_otp_response = $customer_config->send_otp_token( $params, $currentMethod, $custID, $api_key );

        if ( $send_otp_response->status == 'SUCCESS' ) {
            // Store txID.
            \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('mo_auth_tx_id', $send_otp_response->txId)->save();
            \Drupal::messenger()->addStatus(t( $mo_status_message ) );
            return;
        } elseif ( $send_otp_response->status == 'FAILED' ) {
            $utilities::mo_add_loggers_for_failures( $send_otp_response->message, 'error' );
            \Drupal::messenger()->addError(t( 'Something went wrong. Please click').' <a target="_blank" href="'.$utilities::get_mo_tab_url('LOGS').'">'.t('here').'</a> '.t('for more details.' ));
            return;
        } elseif ( $send_otp_response->status == 'CURL_ERROR' ) {
            \Drupal::messenger()->addError(t('cURL is not enabled. Please enable cURL'));
            return;
        }
    }

    function miniorange_saml_validate_otp_submit(array &$form, FormStateInterface $form_state) {
        $form_values      = $form_state->getValues();
        $customer         = new MiniorangeCustomerProfile();
        $cKey             = $customer->getCustomerID();
        $customerApiKey   = $customer->getAPIKey();
        $otpToken         = str_replace(' ', '', $form_values['miniorange_OTP'] );
        $user             = User::load( \Drupal::currentUser()->id() );
        $user_id          = $user->id();
        $utilities        = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);
        $user_email       = $custom_attribute[0]->miniorange_registered_email;
        $phone_number     = isset($form_values['miniorange_phone'])?str_replace( ' ', '', $form_values['miniorange_phone'] ):NULL;
        if ( !is_null( $phone_number ) && empty( $phone_number )) {
            \Drupal::messenger()->addError(t('Please enter your phone number first.'));
            return;
        }
        if ( empty( $otpToken ) ) {
            \Drupal::messenger()->addError(t('Please enter OTP first.'));
            return;
        }
        \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set( 'mo_phone', $phone_number )->save();
        $transactionId = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_tx_id');

        $customer_config = new MiniorangeCustomerSetup( $user_email, $phone_number, NULL, NULL);
        $otp_validation = $customer_config->validate_otp_token( $transactionId, $otpToken, $cKey, $customerApiKey );
        //$txId = $otp_validation->txId;

        if ( $otp_validation->status == 'FAILED' ) {
            \Drupal::messenger()->addError(t("Validation Failed. Please enter the correct OTP."));
            return;
        } elseif ( $otp_validation->status == 'SUCCESS' ) {
            $form_state->setRebuild();
            $authTypeCode     = $form_values['authTypeCode'];
            $user_email       = $custom_attribute[0]->miniorange_registered_email;
            $customer         = new MiniorangeCustomerProfile();
            $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $user_email, $phone_number, NULL, $authTypeCode );

            $configured_methods = MoAuthUtilities::mo_auth_get_configured_methods($user_id);

            if ( !in_array( $authTypeCode, $configured_methods ) ) {
                array_push($configured_methods, $authTypeCode );
            }

            $config_methods = implode(', ',$configured_methods);
            $user_api_handler = new UsersAPIHandler($customer->getCustomerID(), $customer->getAPIKey());

            // Updating the authentication method for the user
            $miniorange_user->setAuthType( $authTypeCode );
            $response = $user_api_handler->update($miniorange_user);

            if ($response->status == 'SUCCESS') {
                // Save User
                $user_id   = $user->id();
                $available = $utilities::check_for_userID($user_id);
                $database  = \Drupal::database();

                if( $available == TRUE ) {
                    $database->update('UserAuthenticationType')->fields(['activated_auth_methods'=> $authTypeCode ])->condition('uid', $user_id,'=')->execute();
                    $database->update('UserAuthenticationType')->fields(['configured_auth_methods'=> $config_methods])->condition('uid', $user_id,'=')->execute();
                }else {
                    echo t("error while saving authentication method.");exit;
                }

                if ( $authTypeCode == AuthenticationType::$SMS_AND_EMAIL['code'] ) {
                    $message = t('OTP Over Email has been configured successfully.');
                }elseif ( $authTypeCode == AuthenticationType::$SMS['code'] ) {
                    $message = t('OTP Over SMS has been configured successfully.');
                }elseif ( $authTypeCode == AuthenticationType::$SMS_AND_EMAIL['code'] ) {
                    $message = t('OTP Over SMS and Email has been configured successfully.');
                }elseif ( $authTypeCode == AuthenticationType::$OTP_OVER_PHONE['code'] ) {
                    $message = t('OTP Over Phone Call has been configured successfully.');
                }

                MoAuthUtilities::show_error_or_success_message($message , 'status');
                return;
            }
            return;
        }
        $message = t('An error occurred while processing your request. Please try again.');
        MoAuthUtilities::show_error_or_success_message($message , 'error');
        return;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {

    }
}