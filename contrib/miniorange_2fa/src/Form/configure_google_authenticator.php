<?php
namespace Drupal\miniorange_2fa\form;

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\UsersAPIHandler;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;

class configure_google_authenticator extends FormBase
{
    public function getFormId() {
        return 'miniorange_configure_google_authenticator';
    }
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        global $base_url;
        $androidAppLink = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/android-google-authenticator-app-link.png');
        $iPhoneAppLink = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/iphone-google-authenticator-app-link.png');
        $androidAppQR = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/android-google-authenticator-app-qr.jpg');
        $iPhoneAppQR = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/iphone-google-authenticator-app-qr.jpg');

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array('miniorange_2fa/miniorange_2fa.admin',
                             "miniorange_2fa/miniorange_2fa.license",),
            ),
        );

        $user = User::load(\Drupal::currentUser()->id());
        $user_id = $user->id();
        $utilities = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);
        $input = $form_state->getUserInput();

        $variables_and_values = array(
            'mo_auth_google_auth_app_name',
        );
        $mo_db_values = $utilities->miniOrange_set_get_configurations( $variables_and_values, 'GET' );

        /**
         * To check which method (Google Authenticator, Microsoft Authenticator, Authy Authenticator') is being configured by user
         */
        $url_parts   = $utilities::mo_auth_get_url_parts();
        if ( in_array(AuthenticationType::$GOOGLE_AUTHENTICATOR['id'], $url_parts ) ) {
            $App_Name = 'Google';
            $method_to_configure = AuthenticationType::$GOOGLE_AUTHENTICATOR['code'];
        } elseif ( in_array(AuthenticationType::$MICROSOFT_AUTHENTICATOR['id'], $url_parts ) ) {
            $App_Name = 'Microsoft';
            $method_to_configure = AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'];
        } elseif ( in_array(AuthenticationType::$AUTHY_AUTHENTICATOR['id'], $url_parts ) ) {
            $App_Name = 'Authy';
            $method_to_configure = AuthenticationType::$AUTHY_AUTHENTICATOR['code'];
        } elseif ( in_array(AuthenticationType::$LASTPASS_AUTHENTICATOR['id'], $url_parts ) ) {
            $App_Name = 'LastPass';
            $method_to_configure = AuthenticationType::$LASTPASS_AUTHENTICATOR['code'];
        } elseif ( in_array(AuthenticationType::$DUO_AUTHENTICATOR['id'], $url_parts ) ) {
            $App_Name = 'Duo';
            $method_to_configure = AuthenticationType::$DUO_AUTHENTICATOR['code'];
        }

        if ( array_key_exists('secret', $input) === FALSE ) {
            $user_email = $custom_attribute[0]->miniorange_registered_email;
            $customer = new MiniorangeCustomerProfile();
            $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$GOOGLE_AUTHENTICATOR['code']);
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->getGoogleAuthSecret($miniorange_user);

            $qrCode = isset( $response->qrCodeData ) ? $response->qrCodeData : '';
            $image = new FormattableMarkup('<img src="data:image/jpg;base64, '.$qrCode.'"/>', [':src' => $qrCode]);
            $secret = isset($response->secret) ? $response->secret : '';
        } else {
            $secret = $input['secret'];
            $qrCode = $input['qrCode'];
        }

        $form['markup_top_2'] = array(
            '#markup' => '<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_2fa_container">'
        );
        $form['header']['#markup'] = t('<h2>Configure '. $App_Name .' Authenticator</h2><hr>');


        if( $App_Name === 'Google' ) {
            /**
             * Create container to hold @InstallGoogleAuthenticator form elements.
             */
            $form['mo_install_google_authenticator'] = array(
                '#type' => 'details',
                '#title' => t('Step 1: Download & Install the Google Authenticator app'),
                //'#open' => TRUE,
                '#attributes' => array('style' => 'padding:0% 2%; margin-bottom:2%')
            );

            $form['mo_install_google_authenticator']['actions_1'] = array(
                '#markup' => t('<br><div>
                      <br>                           
                      <div class="maindiv_1"> 
                          <div>
                              <div class="googleauth-download-header"><strong>Manual Installation</strong></div><hr>
                              <div class="subDivPosition" style="margin-right: 5%"><br>
                                <h6>iPhone Users</h6>
                                <ul>
                                  <li>Go to App Store.</li>
                                  <li>Search for Google Authenticator.</li>
                                  <li>Download and install the App.</li>
                                </ul>
                                <a target="_blank" href="https://itunes.apple.com/in/app/google-authenticator/id388497605?mt=8"><img src="' . $iPhoneAppLink . '"></a>
                              </div>   
                              <div style="margin-left: 7%"><br>
                                <h6>Android Users</h6>
                                <ul>
                                  <li>Go to Google Play Store.</li>
                                  <li>Search for Google Authenticator.</li>
                                  <li>Download and install the App.</li>
                                </ul>
                                <div>
                                   <a target="_blank" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"><img src="' . $androidAppLink . '"></a>
                                </div>
                              </div>
                          </div>
                      </div>
                      <br><br>
                      <div class="mo_2fa_config_option_or"><strong>OR</strong></div>
                      <br>
                      <div class="maindiv_2">
                          <div class="googleauth-download-header"><strong>Scan QR Code</strong></div><hr>
                            
                            <div class="subDivPosition">
                              <div><br>
                              <pre><img src="' . $iPhoneAppQR . '">           </pre> <!-- Do Not remove the spaces from PRE tag-->
                              </div>
                              <pre><span>Apple App Store <b>(iOS)</b></span></pre>
                            </div>
                            
                            <div>
                              <div><br>
                              <pre>         <img src="' . $androidAppQR . '"></pre><!-- Do Not remove the spaces from PRE tag-->
                              </div>
                              <pre>      <span>Google Play Store <b>(Android)</b></span></pre><!-- Do Not remove the spaces from PRE tag-->
                            </div>
                        
                      </div>
                <br><br><br></div>')
            );
        }


        $step_number_first = $App_Name === 'Google' ? 'Step 2:' : 'Step 1:';
        /**
         * Create container to hold @ScanQRCodeAndEnterPasscode form elements.
         */
        $form['mo_scan_qr_code_google_authenticator'] = array(
            '#type' => 'fieldset',
            '#title' => t( $step_number_first . ' Scan below QR Code' ),
            '#attributes' => array( 'style' => 'padding:2% 3% 8%; margin-bottom:4%;' ),
            '#suffix '=> '<hr>',
        );

        $googleAppName = $mo_db_values['mo_auth_google_auth_app_name'] == '' ? 'miniOrangeAuth' : urldecode( $mo_db_values['mo_auth_google_auth_app_name'] );

        $custmization_note = $App_Name === 'Google' ? '<div class="mo_2fa_highlight_background_note"><strong>Note: </strong>After scanning the below QR code, you will see the app in the Google Authenticator with the account name of <strong> '. $googleAppName .' </strong>. If you want to customize the account name goto <a href="' . MoAuthUtilities::get_mo_tab_url( 'LOGIN' ) . '">Login Settings</a> tab and navigate to <u>ADVANCE SETTINGS</u> section.</div><br>
                    <div class="mo_auth_font_type">Scan the below QR Code from Google Authenticator app <strong>or</strong> use the following secret to configure the app.</div>
                    <br>' : '';

        $form['mo_scan_qr_code_google_authenticator']['actions_2'] = array(
            '#markup' =>t('<br><hr><br>' . $custmization_note )
        );

        $form['mo_scan_qr_code_google_authenticator']['actions_qrcode'] = array(
            '#markup' =>  $image = isset( $image ) ? $image : '',
        );

        $secret = MoAuthUtilities::indentSecret($secret);

        $form['mo_scan_qr_code_google_authenticator']['actions_secret_key'] = array(
            '#markup' =>t('<div class="googleauth-secret">
                            <p>Use the following secret</p>
                            <p id="googleAuthSecret"><b>' . $secret . '</b></p>
                            <p>(Spaces don&#39;t matter)</p>
                          </div>')
        );

        $step_number_second = $App_Name === 'Google' ? 'Step 3:' : 'Step 2:';
        $form['mo_scan_qr_code_google_authenticator']['actions_3'] = array(
            '#markup' => t('<br><div>
                <div class="googleauth-steps mo_configure_google_authenticator"><br><br><strong>' . $step_number_second . '</strong> ENTER THE PASSCODE GENERATED BY ' . $App_Name . ' AUTHENTICATOR APP.</div><hr>
                </div>')
        );

        $form['mo_scan_qr_code_google_authenticator']['mo_auth_googleauth_token'] = array(
            '#type' => 'textfield',
            '#title' => t('Passcode:'),
            '#maxlength' => 8,
            '#attributes' => array(
                'placeholder' => t('Enter passcode.'),
                'class' => array(
                    'mo2f-textbox',
                ),
                'style' => 'width:50%',
            ),
            '#required' => TRUE,
            '#suffix' => '<br>',
        );

        $form['mo_scan_qr_code_google_authenticator']['secret'] = array(
            '#type' => 'hidden',
            '#value' => $secret
        );
        $form['mo_scan_qr_code_google_authenticator']['qrCode'] = array(
            '#type' => 'hidden',
            '#value' => $qrCode
        );
        $form['mo_scan_qr_code_google_authenticator']['methodToConfigure'] = array(
            '#type' => 'hidden',
            '#value' => $method_to_configure
        );
        $form['mo_scan_qr_code_google_authenticator']['actions'] = array(
            '#type' => 'actions'
        );
        $form['mo_scan_qr_code_google_authenticator']['actions_submit'] = array(
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => t('Verify and Save'),
        );
        $form['mo_scan_qr_code_google_authenticator']['actions_cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#button_type' => 'danger',
            '#submit' => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
            '#limit_validation_errors'=>array(),
            '#suffix' => '</div>'
        );

        MoAuthUtilities::miniOrange_advertise_network_security($form,$form_state);

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $form_state->setRebuild();
        $input             = $form_state->getValues();
        $secret            = $input['secret'];
        $methodToConfigure = $input['methodToConfigure'];
        $user              = User::load(\Drupal::currentUser()->id());
        $user_id           = $user->id();
        $utilities         = new MoAuthUtilities();
        $custom_attribute  = $utilities::get_users_custom_attribute($user_id);
        $user_email        = $custom_attribute[0]->miniorange_registered_email;
        $otpToken          = $input['mo_auth_googleauth_token'];
        $customer          = new MiniorangeCustomerProfile();
        $miniorange_user   = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$GOOGLE_AUTHENTICATOR['code']);
        $auth_api_handler  = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $response          = $auth_api_handler->register( $miniorange_user, AuthenticationType::$GOOGLE_AUTHENTICATOR['code'], $secret, $otpToken, NULL );

        // Clear all the messages
        \Drupal::messenger()->deleteAll();
        // read API response
        if ( $response->status == 'SUCCESS' ) {
            \Drupal::messenger()->addStatus(t(''));
            $configured_methods = $utilities::mo_auth_get_configured_methods( $user_id );
            /**
             * Delete all the configured TOTP methods as only one can be used at a time
             */
            $configured_methods = array_values( array_diff( $configured_methods, array( AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'], AuthenticationType::$GOOGLE_AUTHENTICATOR['code'], AuthenticationType::$AUTHY_AUTHENTICATOR['code'], AuthenticationType::$LASTPASS_AUTHENTICATOR['code'], AuthenticationType::$DUO_AUTHENTICATOR['code'] ) ) );

            array_push($configured_methods, $methodToConfigure );

            $config_methods   = implode(', ',$configured_methods );
            $user_api_handler = new UsersAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
            $response         = $user_api_handler->update($miniorange_user);
            if ( $response->status == 'SUCCESS' ) {
                // Save User
                $user_id   = $user->id();
                $available = $utilities::check_for_userID( $user_id );
                $database  = \Drupal::database();

                if( $available == TRUE ) {
                    $database->update('UserAuthenticationType')->fields(['activated_auth_methods'=> $methodToConfigure])->condition('uid', $user_id,'=')->execute();
                    $database->update('UserAuthenticationType')->fields(['configured_auth_methods'=> $config_methods])->condition('uid', $user_id,'=')->execute();
                }else {
                    echo "error while updating authentication method."; exit;
                }
                $message = t( ucwords( strtolower( $methodToConfigure ) ) . ' configured successfully.');
                $utilities::show_error_or_success_message($message , 'status');
                return;
            }
        } elseif ( $response->status == 'FAILED' ) {
           \Drupal::messenger()->addError(t('The passcode you have entered is incorrect. Please try again.'));
           return;
        }
        $message = t('An error occured while processing your request. Please try again.');
        MoAuthUtilities::show_error_or_success_message($message , 'error');
        return;
    }
}