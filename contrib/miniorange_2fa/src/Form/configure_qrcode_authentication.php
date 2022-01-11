<?php
namespace Drupal\miniorange_2fa\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\MoAuthConstants;
use Drupal\miniorange_2fa\UsersAPIHandler;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;

class configure_qrcode_authentication extends FormBase
{
    public function getFormId() {
        return 'mo_auth_configure_qrcode_authentication';
    }
    public function buildForm( array $form, FormStateInterface $form_state ) {
        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_2fa/miniorange_2fa.admin",
                    "miniorange_2fa/miniorange_2fa.license",
                )
            ),
        );

        global $base_url;
        $input            = $form_state->getUserInput();
        $user             = User::load(\Drupal::currentUser()->id());
        $user_id          = $user->id();
        $utilities        = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);

        $form['actions'] = array(
            '#type' => 'actions'
        );

        /** To check which method ( Soft Token, QR Code, Push Notification' ) is being configured by user
         * @authTypeCode:- Code of the authentication Type
         * @messageHeader:- Title of the Page
         */
        $url_parts = MoAuthUtilities::mo_auth_get_url_parts();
        if ( in_array(AuthenticationType::$SOFT_TOKEN['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$SOFT_TOKEN['code'];
            $messageHeader = t('Configure Soft Token' );
        } elseif ( in_array(AuthenticationType::$PUSH_NOTIFICATIONS['id'], $url_parts ) ) {
            $authTypeCode = AuthenticationType::$PUSH_NOTIFICATIONS['code'];
            $messageHeader = t('Configure Push Notification' );
        } else {
            $authTypeCode = AuthenticationType::$QR_CODE['code'];
            $messageHeader = t( 'Configure QR Code Authentication' );
        }

        $androidAppLink = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/android-google-authenticator-app-link.png');
        $iPhoneAppLink  = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/iphone-google-authenticator-app-link.png');
        $androidAppQR   = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/android-mo-authenticator-app-qr.jpg');
        $iPhoneAppQR    = file_create_url($base_url . '/' . drupal_get_path('module', 'miniorange_2fa') . '/includes/images/iphone-mo-authenticator-app-qr.png');

        if ( array_key_exists('txId', $input ) === FALSE ) {
            $user_email       = $custom_attribute[0]->miniorange_registered_email;
            $customer         = new MiniorangeCustomerProfile();
            $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$QR_CODE['code']);
            $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
            $response         = $auth_api_handler->register( $miniorange_user, AuthenticationType::$QR_CODE['code'], NULL, NULL, NULL );
            $qrCode           = isset( $response->qrCode ) ? $response->qrCode : '';
            $image            = new FormattableMarkup('<img src="data:image/jpg;base64, '.$qrCode.'"/>', [':src' => $qrCode] );

            $form['markup_top_2'] = array(
               '#markup' => '<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_2fa_container">'
            );


            $form['header']['#markup'] = '<h2>'.$messageHeader.'</h2><hr>';

            /**
             * Create container to hold @InstallMiniorangeAuthenticator form elements.
             */
            $form['mo_install_miniorange_authenticator'] = array(
                '#type' => 'details',
                '#title' => t('Step 1: Download & Install the miniOrange Authenticator app' ),
                //'#open' => TRUE,
                '#attributes' => array( 'style' => 'padding:0% 2%; margin-bottom:2%' )
            );

            $form['mo_install_miniorange_authenticator']['actions_1'] = array(
                '#markup' =>'
                      <br>                           
                      <div class="maindiv_1"> 
                          <div>
                              <div class="googleauth-download-header"><strong>'.t('Manual Installation').'</strong></div><hr>
                              <div class="subDivPosition" style="margin-right: 5%"><br>
                                '.t('<h6>iPhone Users</h6>
                                <ul>
                                  <li>Go to App Store.</li>
                                  <li>Search for miniOrange.</li>
                                  <li>Download and install the app.<br> <b>(NOT MOAuth)</b></li>
                                </ul>').'
                                <a target="_blank" href="https://apps.apple.com/app/id1482362759"><img src="' . $iPhoneAppLink . '"></a>
                              </div>
                                                
                              <div style="margin-left: 7%"><br>
                               '.t('<h6>Android Users</h6>
                                <ul>
                                  <li>Go to Google Play Store.</li>
                                  <li>Search for miniOrange.</li>
                                  <li>Download and install <b> Authenticator</b> app <br> <b>(NOT miniOrange Authenticator)</b></li>
                                </ul>').'
                                <div>
                                   <a target="_blank" href="https://play.google.com/store/apps/details?id=com.miniorange.android.authenticator"><img src="' . $androidAppLink . '"></a>
                                </div>
                              </div>
                          </div>
                      </div>
                      <br><br>
                      <div class="mo_2fa_config_option_or"><strong>OR</strong></div>
                      <br>
                      <div class="maindiv_2">
                          <div class="googleauth-download-header"><strong>'.t('Scan QR Code').'</strong></div><hr>
                            
                            <div class="subDivPosition">
                              <div><br>
                              <pre><img src="' . $iPhoneAppQR . '">           </pre> <!-- Do Not remove the spaces from PRE tag-->
                              </div>
                              <span>'.t('Apple App Store <b>(iOS)</b>').'</span>
                            </div>
                           
                            <div>
                              <div><br>
                              <pre>         <img src="' . $androidAppQR . '"></pre> <!-- Do Not remove the spaces from PRE tag-->
                              </div>
                              <pre>      '.t('Google Play Store <b>(Android)</b>').'</pre><!-- Do Not remove the spaces from PRE tag-->
                            </div>
                        <br><br><br></div>
                '
            );


            /**
             * Create container to hold @ScanQRCode form elements.
             */
            $form['mo_scan_qr_code_miniorange_authenticator'] = array(
                '#type' => 'fieldset',
                '#title' => t( 'Step 2: Scan below QR Code' ),
                '#attributes' => array( 'style' => 'padding:2% 2% 8%; margin-bottom:4%; text-align: center;' ),
                '#suffix '=> '<hr>',
            );
            $form['mo_scan_qr_code_miniorange_authenticator']['actions_2'] = array(
                '#markup' => '<br><hr><div class="googleauth-steps"><br></div><div class="mo_2fa_highlight_background_note">'.t('Please scan the below QR Code from miniOrange Authenticator app and the page will load automatically.').'</div><br><br>'
            );
            $form['mo_scan_qr_code_miniorange_authenticator']['actions_qrcode'] = array(
                '#markup' => $image,
            );

            /**
             * Accessed form mo_authentication.js file
             */
            $form['mo_scan_qr_code_miniorange_authenticator']['txId'] = array(
                '#type' => 'hidden',
                '#value' => $response->txId
            );
            $form['mo_scan_qr_code_miniorange_authenticator']['url'] = array(
                '#type' => 'hidden',
                '#value' => MoAuthConstants::getBaseUrl() . MoAuthConstants::$AUTH_REGISTRATION_STATUS_API,
            );
            $form['mo_scan_qr_code_miniorange_authenticator']['authTypeCode'] = array(
                '#type' => 'hidden',
                '#value' => $authTypeCode
            );
        }
        $form['mo_scan_qr_code_miniorange_authenticator']['actions_submit'] = array(
            '#type' => 'submit',
            '#value' => t('s'), //Save
            '#attributes' => array('class' => array('hidebutton')),
        );
        $form['mo_scan_qr_code_miniorange_authenticator']['actions_cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#button_type' => 'danger',
            '#submit' => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
            '#limit_validation_errors'=>array(),
            '#attributes' => array('style' => 'margin-right:13%'),
            '#suffix'=>'</div>',
            '#prefix' => '<br><br>'
        );

        MoAuthUtilities::miniOrange_advertise_network_security($form,$form_state);

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {

        $form_state->setRebuild();
        $input            = $form_state->getUserInput();
        $txId             = $input['txId'];
        $authTypeCode     = $input['authTypeCode'];
        $user             = User::load(\Drupal::currentUser()->id());
        $user_id          = $user->id();
        $utilities        = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);
        $user_email       = $custom_attribute[0]->miniorange_registered_email;
        $customer         = new MiniorangeCustomerProfile();
        $miniorange_user  = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$QR_CODE['code']);
        $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $response         = $auth_api_handler->getRegistrationStatus($txId);

        // Clear all the messages
        \Drupal::messenger()->deleteAll();
        // read API response
        if ( $response->status == 'SUCCESS' ) {
            $configured_methods = MoAuthUtilities::mo_auth_get_configured_methods($user_id);

            /**
             * If one of the methods in Soft Token, QR Code Authentication, Push Notification is configured then all three methods are configured.
             */
            if ( !in_array( AuthenticationType::$SOFT_TOKEN['code'], $configured_methods ) ) {
                array_push($configured_methods, AuthenticationType::$SOFT_TOKEN['code']);
            }
            if ( !in_array( AuthenticationType::$QR_CODE['code'], $configured_methods ) ) {
                array_push($configured_methods, AuthenticationType::$QR_CODE['code']);
            }
            if ( !in_array( AuthenticationType::$PUSH_NOTIFICATIONS['code'], $configured_methods ) ) {
                array_push($configured_methods, AuthenticationType::$PUSH_NOTIFICATIONS['code']);
            }

            $config_methods = implode(', ',$configured_methods);
            $user_api_handler = new UsersAPIHandler($customer->getCustomerID(), $customer->getAPIKey());

            /**
             * Updating the authentication method for the user
             */
            $miniorange_user->setAuthType( $authTypeCode );
            $response  = $user_api_handler->update($miniorange_user);
            if ( $response->status == 'SUCCESS' ) {
                // Save user
                $user_id   = $user->id();
                $utilities = new MoAuthUtilities();
                $available = $utilities::check_for_userID($user_id);
                $database  = \Drupal::database();

                if( $available == TRUE ) {
                    $database->update('UserAuthenticationType')->fields(['configured_auth_methods'=> $config_methods])->condition('uid', $user_id,'=')->execute();
                    $database->update('UserAuthenticationType')->fields(['activated_auth_methods'=> $authTypeCode])->condition('uid', $user_id,'=')->execute();
                }else {
                      echo t("error while saving the authentication method.");exit;
                }

                $message = t('QR Code Authentication configured successfully.');
                if ( $authTypeCode == AuthenticationType::$SOFT_TOKEN['code'] ) {
                    $message = t('Soft Token configured successfully.');
                } elseif ( $authTypeCode == AuthenticationType::$PUSH_NOTIFICATIONS['code'] ) {
                    $message = t('Push Notifications configured successfully.');
                }
                MoAuthUtilities::show_error_or_success_message( $message , 'status' );
                return;
            }
            return;
        }
        $message = t('An error occurred while processing your request. Please try again.');
        MoAuthUtilities::show_error_or_success_message($message , 'error');
        return;
    }
}