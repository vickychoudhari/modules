<?php
/**
 * @file
 * Contains Setup Two-Factor page for miniOrange 2FA Login Module.
 */
namespace Drupal\miniorange_2fa\Form;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\UsersAPIHandler;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;
use Symfony\Component\Routing\Exception\RouteNotFoundException;


/**
 * Showing Setup Two-Factor page.
 */
class MoAuthSetupTwoFactor extends FormBase {
    public function getFormId() {
        return 'miniorange_2fa_setup_two_factor';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        global $base_url;
        $utilities = new MoAuthUtilities();
        /**
         * used in test_otp_over_email, test_otp_over_sms and test_otp_over_sms_and_email forms
         */
        \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('txId_Value', 'EMPTY_VALUE')->save();


        $success_error_message = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_2fa_Success/Error message');
        $success_error_status = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_2fa_Success/Error status');

        if( $success_error_message != NULL && $success_error_status != NULL ) {
            \Drupal::messenger()->addMessage(t($success_error_message), $success_error_status);
            \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('mo_auth_2fa_Success/Error message', NULL)->save();
            \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('mo_auth_2fa_Success/Error status', NULL)->save();
        }

        $form['markup_top_2'] = array(
            '#markup' => '<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_container_second_factor">'
        );

        $disabled = False;
        if ( !$utilities::isCustomerRegistered() ) {
            $form['header'] = array(
                '#markup' => t('<div class="mo_2fa_register_message"><p>You need to <a href="'.$base_url.'/admin/config/people/miniorange_2fa/customer_setup">Register/Login</a> with miniOrange before using this module.</p></div>')
            );
            $disabled = True;
        }
        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_2fa/miniorange_2fa.admin",
                    "miniorange_2fa/miniorange_2fa.license",
                )
            ),
        );

        global $base_url;
        $user      = User::load(\Drupal::currentUser()->id());
        $user_id   = $user->id();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);
        $user_email       = NULL;
        if( !is_null( $custom_attribute ) && !empty( $custom_attribute ) ) {
            $user_email = $custom_attribute[0]->miniorange_registered_email;
        }

        $customer            = new MiniorangeCustomerProfile();
        $user_api_handler    = new UsersAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $miniorange_user     = new MiniorangeUser($customer->getCustomerID(), $user_email, '', '', '');
        $response            = $user_api_handler->get($miniorange_user);

        $configured_methods  = $utilities::mo_auth_get_configured_methods( $user_id );

        $users_active_method = AuthenticationType::$NOT_CONFIGURED;
        if( is_object( $response ) && isset( $response->authType ) && $response->authType != AuthenticationType::$GOOGLE_AUTHENTICATOR['code'] ) {
            $users_active_method = AuthenticationType::getAuthType( $response->authType );
        }else {
            if ( in_array( AuthenticationType::$GOOGLE_AUTHENTICATOR['code'], $configured_methods ) ) {
                $users_active_method = AuthenticationType::$GOOGLE_AUTHENTICATOR;
            } elseif ( in_array( AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'], $configured_methods ) ) {
                $users_active_method = AuthenticationType::$MICROSOFT_AUTHENTICATOR;
            } elseif ( in_array( AuthenticationType::$AUTHY_AUTHENTICATOR['code'], $configured_methods ) ) {
                $users_active_method = AuthenticationType::$AUTHY_AUTHENTICATOR;
            } elseif ( in_array( AuthenticationType::$LASTPASS_AUTHENTICATOR['code'], $configured_methods ) ) {
                $users_active_method = AuthenticationType::$LASTPASS_AUTHENTICATOR;
            } elseif ( in_array( AuthenticationType::$DUO_AUTHENTICATOR['code'], $configured_methods ) ) {
                $users_active_method = AuthenticationType::$DUO_AUTHENTICATOR;
            }
        }

        if( !in_array( $users_active_method['code'], $configured_methods ) ) {
            array_push($configured_methods, $users_active_method['code'] );
            $configured_methods_codes = implode(",", $configured_methods );
            \Drupal::database()->update('UserAuthenticationType')->fields( ['configured_auth_methods'=> $configured_methods_codes] )->condition('uid', $user_id,'=' )->execute();
        }

        /**
         * Create container to hold all the form elements.
         */
        $form['mo_setup_second_factor'] = array(
            '#type' => 'fieldset',
            '#title' => t('Setup Second Factor'),
            '#attributes' => array( 'style' => 'padding:0% 2% 2%' )
        );

        $form['mo_setup_second_factor']['header_top']['#markup'] = t(
            '<br><br><div class="mo2f-setup-header">
                        <span class="mo_2fa_welcome_message">Active Method - ' . strtoupper( $users_active_method['name'] ) . '</span>
                    </div>
                    <div><br></div>
                    <div></div>');

        $form['mo_setup_second_factor']['header_methods']['#markup'] = t(
              '<div class="mo2f-setup-methods-info-wrap">
                        <div class="mo2f-setup-methods-info-left"><span class="mo2f-color-icon mo2f-active-method"></span>- Active Method</div>
                        <div class="mo2f-setup-methods-info-center"><span class="mo2f-color-icon mo2f-configured-method"></span>- Configured Method</div>
                        <div class="mo2f-setup-methods-info-right"><span class="mo2f-color-icon mo2f-unconfigured-method"></span>- Unconfigured Method</div>
                    </div>');

        $emailVerificationOption      = $this->mo_auth_create_auth_type( AuthenticationType::$EMAIL_VERIFICATION, $configured_methods, $users_active_method['code'], FALSE, $base_url );
        $googleAuthenticatorOption    = $this->mo_auth_create_auth_type( AuthenticationType::$GOOGLE_AUTHENTICATOR, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $kbaAuth                      = $this->mo_auth_create_auth_type( AuthenticationType::$KBA, $configured_methods, $users_active_method['code'], TRUE, $base_url);
        $OTPOverSMSOption             = $this->mo_auth_create_auth_type( AuthenticationType::$SMS, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $OTPOverEMAILOption           = $this->mo_auth_create_auth_type( AuthenticationType::$OTP_OVER_EMAIL, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $OTPOverSMSandEMAILOption     = $this->mo_auth_create_auth_type( AuthenticationType::$SMS_AND_EMAIL, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $microsoftAuthenticatorOption = $this->mo_auth_create_auth_type( AuthenticationType::$MICROSOFT_AUTHENTICATOR, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $duoAuthenticatorOption       = $this->mo_auth_create_auth_type( AuthenticationType::$DUO_AUTHENTICATOR, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $authyAuthenticatorOption     = $this->mo_auth_create_auth_type( AuthenticationType::$AUTHY_AUTHENTICATOR, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $lastPassAuthenticatorOption  = $this->mo_auth_create_auth_type( AuthenticationType::$LASTPASS_AUTHENTICATOR, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $OTPOverPHONEOption           = $this->mo_auth_create_auth_type( AuthenticationType::$OTP_OVER_PHONE, $configured_methods, $users_active_method['code'], TRUE, $base_url);
        $OTPOverWhatsAppOption        = $this->mo_auth_create_auth_type( AuthenticationType::$OTP_OVER_WHATSAPP, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $HardwareToken                = $this->mo_auth_create_auth_type( AuthenticationType::$HARDWARE_TOKEN, $configured_methods, $users_active_method['code'], TRUE, $base_url);
        $pushNotificationsOption      = $this->mo_auth_create_auth_type( AuthenticationType::$PUSH_NOTIFICATIONS, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $qrCodeAuthenticatorOption    = $this->mo_auth_create_auth_type( AuthenticationType::$QR_CODE, $configured_methods, $users_active_method['code'], TRUE, $base_url );
        $softTokenOption              = $this->mo_auth_create_auth_type( AuthenticationType::$SOFT_TOKEN, $configured_methods, $users_active_method['code'], TRUE, $base_url );

        $options = array (
            AuthenticationType::$EMAIL_VERIFICATION['code']      => $emailVerificationOption,
            AuthenticationType::$GOOGLE_AUTHENTICATOR['code']    => $googleAuthenticatorOption,
            AuthenticationType::$KBA['code']                     => $kbaAuth,
            AuthenticationType::$SMS['code']                     => $OTPOverSMSOption,
            AuthenticationType::$OTP_OVER_EMAIL['code']          => $OTPOverEMAILOption,
            AuthenticationType::$SMS_AND_EMAIL['code']           => $OTPOverSMSandEMAILOption,
            AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'] => $microsoftAuthenticatorOption,
            AuthenticationType::$DUO_AUTHENTICATOR['code']       => $duoAuthenticatorOption,
            AuthenticationType::$AUTHY_AUTHENTICATOR['code']     => $authyAuthenticatorOption,
            AuthenticationType::$LASTPASS_AUTHENTICATOR['code']  => $lastPassAuthenticatorOption,
            AuthenticationType::$OTP_OVER_PHONE['code']          => $OTPOverPHONEOption,
            AuthenticationType::$OTP_OVER_WHATSAPP['code']       => $OTPOverWhatsAppOption,
            AuthenticationType::$HARDWARE_TOKEN['code']          => $HardwareToken,
            AuthenticationType::$PUSH_NOTIFICATIONS['code']      => $pushNotificationsOption,
            AuthenticationType::$QR_CODE['code']                 => $qrCodeAuthenticatorOption,
            AuthenticationType::$SOFT_TOKEN['code']              => $softTokenOption,
        );

        $form['mo_setup_second_factor']['mo_auth_method'] = array(
            '#type'          => 'radios',
            '#options'       => $options,
            '#default_value' => $users_active_method['code'],
            '#disabled'      => $disabled,
            '#markup'        => '<div class="mo_tfa_grid_view">',
            '#suffix'        => '</div><br><br>',
        );

        $form['mo_setup_second_factor']['Submit_setup_two_factor_form'] = array(
            '#type'        => 'submit',
            '#button_type' => 'primary',
            '#value'       => t('Save Configurations'),
            '#attributes'  => array( 'style' => 'margin-top:10%;' ),
            '#disabled'    => $disabled,
            '#suffix'      => '<br><br><br></div>'
        );

        return $form;
    }

    function submitForm(array &$form, FormStateInterface $form_state) {
        $utilities          = new MoAuthUtilities();
        $user_obj           = User::load(\Drupal::currentUser()->id());
        $user_id            = $user_obj->id();
        $configured_methods = $utilities::mo_auth_get_configured_methods($user_id);
        $custom_attribute   = $utilities::get_users_custom_attribute($user_id);
        $user_email         = $custom_attribute[0]->miniorange_registered_email;

        $form_state->setRebuild();
        $input    = $form_state->getUserInput();
        $authType = $input['mo_auth_method'];
        $database = \Drupal::database();
        $database->update('UserAuthenticationType')->fields(['activated_auth_methods'=> $authType])->condition('miniorange_registered_email', $user_email,'=')->execute();

        if ( in_array( $authType, $configured_methods ) ) {
            $custom_attribute = $utilities::get_users_custom_attribute( $user_id );
            $user_email       = $custom_attribute[0]->miniorange_registered_email;
            $customer         = new MiniorangeCustomerProfile();
            $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $user_email, NULL, NULL, $authType );
            $user_api_handler = new UsersAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
            $response         = $user_api_handler->update($miniorange_user);
            if ( $response->status == 'SUCCESS' ) {
                \Drupal::messenger()->addStatus(t('Authentication method updated successfully.'));
                return;
            }
            \Drupal::messenger()->addError(t('An error occurred while updating the authentication type. Please try again.'));
            return;
        }
        \Drupal::messenger()->addError(t('Please configure this authentication method first to enable it.'));
        return;
    }

    function mo_auth_create_auth_type( $authType, $configuredMethods, $active_method, $reconfigure_allowed, $base_url ) {
        $utilities   = new MoAuthUtilities();
        $label_title = 'Supported in ' . implode(', ', $authType['supported-for']);

        $supported_for_icon_class = '';
        if ( in_array(AuthenticationType::landline, $authType['supported-for'] ) ) {
            $supported_for_icon_class = 'mo2f-landline';
        } elseif ( in_array(AuthenticationType::feature_phones, $authType['supported-for'] ) ) {
            $supported_for_icon_class = 'mo2f-smartphone-feature-phone';
        } elseif ( in_array(AuthenticationType::laptops, $authType['supported-for'] ) ) {
            $supported_for_icon_class = 'mo2f-laptop';
        } elseif ( in_array(AuthenticationType::smartphones, $authType['supported-for'] ) ) {
            $supported_for_icon_class = 'mo2f-smartphone';
        } elseif ( in_array(AuthenticationType::laptops_phones, $authType['supported-for'] ) ) {
            $supported_for_icon_class = 'mo2f-smartphone-feature-phone-laptop';
        } elseif ( in_array(AuthenticationType::hardware_token, $authType['supported-for'] ) ) {
            $supported_for_icon_class = 'mo2f-yubikey-hardware-token';
        }

        $config_type_class = 'mo2f-unconfigured-method';
        if ( $authType['code'] == $active_method ) {
            $config_type_class = 'mo2f-active-method';
        } elseif ( in_array($authType['code'], $configuredMethods ) ) {
            $config_type_class = 'mo2f-configured-method';
        }

        $authTypeID        = $authType['id'];


         try{
           $Test              = Url::fromRoute($authType['test-route'])->toString();
         }
         catch (RouteNotFoundException $e){
         }

        try{
          $Re_Configure = $Configure = Url::fromRoute($authType['configure-route'])->toString();
        }
        catch (RouteNotFoundException $e){
        }

//        $Configure         = "$base_url/admin/config/people/miniorange_2fa/setup/user/configure/$authTypeID";
//        $Test              = "$base_url/admin/config/people/miniorange_2fa/setup/user/test/$authTypeID";
//        $Re_Configure      = "$base_url/admin/config/people/miniorange_2fa/setup/user/configure/$authTypeID";
        $configure_text    = t('Configure');
        $test_text         = t('Test');
        $re_configure_text = t('Re-configure');

        /**
         * Change all the links to Register/Login tab as user is not registered or logged in yet.
         */
        if ( !$utilities::isCustomerRegistered() ) {
            $Configure=$Re_Configure=$Test = Url::fromRoute('miniorange_2fa.customer_setup')->toString();
        }

        /**
         * Show Contact us link as these features are not implemented yet.
         */
        if($authType['id'] === AuthenticationType::$OTP_OVER_WHATSAPP['id'] ) {
            $Configure      = Url::fromRoute('miniorange_2fa.support')->toString();
            $configure_text = t('Request this method');
        }

        $configured = 'false';
        $classes    = $supported_for_icon_class .' '. $config_type_class;
        $test       = '<a href="' . $Configure . '">'. $configure_text .'</a>';
        if ( !empty( $configuredMethods ) && in_array( $authType['code'], $configuredMethods ) ) {
            $test   = '<a href="' . $Test . '">'. $test_text .'</a>';
            if ( $reconfigure_allowed === TRUE ) {
                $test .= ' | <a href="' . $Re_Configure .'" style="color:red">'.$re_configure_text.'</a>';
            }
            $configured = 'true';
        }
        $html = '<span>
                    <span class="mo2f-method" data-id="'. $authType['id'] .'" data-configured = "'. $configured .'">'. $authType['name'] .'</span>
                    <p>'. t($authType['description']) .'</p>
                    <div class="' . $classes .'" title="'. $label_title .'">' . $test . '</div>
                    <div><br><hr><hr><br></div>
                </span>';

        return $html;
    }
}