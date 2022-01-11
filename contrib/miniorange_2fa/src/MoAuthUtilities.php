<?php
namespace Drupal\miniorange_2fa;
/**
 * @file
 * This file is part of miniOrange 2FA module.
 *
 * The miniOrange 2FA module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 *(at your option) any later version.
 *
 * miniOrange 2FA module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange 2FA module.  If not, see <http://www.gnu.org/licenses/>.
 */

use Exception;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\user\Entity\Role;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MoAuthUtilities {

    /**
     * HANDALE ALL THE DATABASE VARIABLE CALLS LIKE SET|GET|CLEAR
     * -----------------------------------------------------------------------
     * @variable_array:
     * FORMAT OF ARRAY FOR DIEFRENT @param
     * SET array( vaviable_name1(key) => value, vaviable_name2(key) => value )
     * GET and CLEAR array( vaviable_name1(value), vaviable_name2(value) )  note: key doesnt matter here
     * -----------------------------------------------------------------------
     * @mo_method:  SET | GET | CLEAR
     * -----------------------------------------------------------------------
     * @return array | void
     */
    public static function miniOrange_set_get_configurations( $variable_array, $mo_method ) {
        if( $mo_method === 'GET' ) {
            $variables_and_values = array();
            $miniOrange_config = \Drupal::config('miniorange_2fa.settings');
            foreach ( $variable_array as $variable => $value ) {
                $variables_and_values[$value] = $miniOrange_config->get( $value );
            }
            return $variables_and_values;
        }
        $configFactory = \Drupal::configFactory()->getEditable('miniorange_2fa.settings');
        if( $mo_method === 'SET' ) {
            foreach ($variable_array as $variable => $value) {
                $configFactory->set($variable, $value)->save();
            }
            return;
        }
        foreach ($variable_array as $variable => $value) {
            $configFactory->clear($value)->save();
        }
    }

    /**
     * @param $form
     * @param $form_state
     * @return mixed
     * Advertise network security
     */
    public static function miniOrange_advertise_network_security( &$form, &$form_state ) {
        global $base_url;

        $form['miniorange_network_security_advertise'] = array(
            '#markup' => '<div class="mo_auth_table_layout mo_auth_container_2">',
        );
        $form['mo_idp_net_adv'] = array(
            '#markup'=>'<form name="f1">
                <table id="idp_support" class="idp-table" style="border: none;">
                <h3>'. t('Looking for a Drupal Web Security module?') .'</h3>
                    <tr class="mo_ns_row">
                        <th class="mo_ns_image1"><img
                                    src="'.$base_url . '/' . drupal_get_path("module", "miniorange_2fa") . '/includes/images/security.jpg"
                                    alt="security icon" height=150px width=44%>
                           <br>
                        <strong>'. t('Drupal Website Security') .'</strong>
                        </th>
                    </tr>
                    <tr class="mo_ns_row">
                        <td class="mo_ns_align">
                            '. t('Building a website is a time-consuming process that requires tremendous efforts. For smooth
                            functioning and protection from any sort of web attack appropriate security is essential and we
                            ensure to provide the best website security solutions available in the market.
                            We provide you enterprise-level security, protecting your Drupal site from hackers and malware.') .'
                        </td>
                    </tr>
                </table>
            </form>'
        );

        self::miniOrange_add_network_security_buttons($form, $form_state );

        return $form;
    }

    public static function miniOrange_add_network_security_buttons( &$form, &$form_state ) {

        $form['miniorange_radius_buttons'] = array(
            '#markup' => '<div class="mo2f_text_center"><b></b>
                          <a class=" mo_auth_button_left" href="https://www.drupal.org/project/security_login_secure" target="_blank">'.t('Download Module').'</a>
                          <b></b><a class=" mo_auth_button_right" href=" ' . MoAuthConstants::$WBSITE_SECURITY . ' " target="_blank">'.t('Know More').'</a></div></div>',
        );
    }

    /**
     * SEND SUPPORT QUERY | NEW FEATURE REQUEST | DEMO REQUEST
     * @param $email
     * @param $phone
     * @param $query
     * @param $query_type = Support | Demo Request | New Feature Request
     */
    public static function send_support_query( $email, $phone, $query, $query_type )   {
        $support = new Miniorange2FASupport( $email, $phone, $query, $query_type );
        $support_response = $support->sendSupportQuery();

        if ( $support_response->status == 'CURL_ERROR' ) {
          \Drupal::messenger()->addError(t('cURL is not enabled. Please enable cURL'));
          return;
        }
        elseif ( $support_response ) {
            \Drupal::messenger()->addStatus(t('Thanks for getting in touch! We will get back to you shortly.'));
        } else {
            \Drupal::messenger()->addError(t('Error submitting the support query. Please send us your query at <a href="mailto:info@xecurify.com">info@xecurify.com</a>.'));
        }
    }

    public static function get_2fa_methods_for_inline_registration( $methods_selected ) {
      if( $methods_selected === TRUE && \Drupal::config('miniorange_2fa.settings')->get('mo_auth_enable_allowed_2fa_methods') ) {
         $selected_2fa_methods = json_decode( \Drupal::config('miniorange_2fa.settings')->get('mo_auth_selected_2fa_methods'),TRUE );
         if( !empty( $selected_2fa_methods ) ) {
            return $selected_2fa_methods;
         }
      }

      $options = array(
          AuthenticationType::$EMAIL_VERIFICATION['code']      => AuthenticationType::$EMAIL_VERIFICATION['name'],
          AuthenticationType::$GOOGLE_AUTHENTICATOR['code']    => AuthenticationType::$GOOGLE_AUTHENTICATOR['name'],
          AuthenticationType::$MICROSOFT_AUTHENTICATOR['code'] => AuthenticationType::$MICROSOFT_AUTHENTICATOR['name'],
          AuthenticationType::$DUO_AUTHENTICATOR['code']       => AuthenticationType::$DUO_AUTHENTICATOR['name'],
          AuthenticationType::$AUTHY_AUTHENTICATOR['code']     => AuthenticationType::$AUTHY_AUTHENTICATOR['name'],
          AuthenticationType::$LASTPASS_AUTHENTICATOR['code']  => AuthenticationType::$LASTPASS_AUTHENTICATOR['name'],
          AuthenticationType::$SMS['code']                     => AuthenticationType::$SMS['name'],
          AuthenticationType::$EMAIL['code']                   => AuthenticationType::$EMAIL['name'],
          AuthenticationType::$SMS_AND_EMAIL['code']           => AuthenticationType::$SMS_AND_EMAIL['name'],
          AuthenticationType::$OTP_OVER_PHONE['code']          => AuthenticationType::$OTP_OVER_PHONE['name'],
          AuthenticationType::$KBA['code']                     => AuthenticationType::$KBA['name'],
          AuthenticationType::$QR_CODE['code']                 => AuthenticationType::$QR_CODE['name'],
          AuthenticationType::$PUSH_NOTIFICATIONS['code']      => AuthenticationType::$PUSH_NOTIFICATIONS['name'],
          AuthenticationType::$SOFT_TOKEN['code']              => AuthenticationType::$SOFT_TOKEN['name'],
          AuthenticationType::$HARDWARE_TOKEN['code']          => AuthenticationType::$HARDWARE_TOKEN['name'],
          /** DO NOT REMOVE OR UNCOMMENT UNTIL THESE FEATURES IMPLEMENTED */
          //AuthenticationType::$OTP_OVER_WHATSAPP['code']     => AuthenticationType::$OTP_OVER_WHATSAPP['name'],

      );
      return $options;
    }

    /**
     * @param string $question_set - which question set needs to be return
     * @param string $type = return type
     * @return array - Question set
     */
    public static function mo_get_kba_questions ( $question_set = 'ONE', $type = 'ARRAY' ) {
        $variables_and_values = array(
            'mo_auth_enable_custom_kba_questions',
            'mo_auth_custom_kba_set_1',
            'mo_auth_custom_kba_set_2',
        );
        $mo_db_values = self::miniOrange_set_get_configurations( $variables_and_values, 'GET' );

        if( ( $mo_db_values['mo_auth_enable_custom_kba_questions'] === FALSE || $mo_db_values['mo_auth_enable_custom_kba_questions'] == NULL ) || $mo_db_values['mo_auth_custom_kba_set_1'] == '' && $mo_db_values['mo_auth_custom_kba_set_2'] == '' ) {
            $question_set_one_string = 'What is your first company name?;What was your childhood nickname?;In what city did you meet your spouse/significant other?;What is the name of your favorite childhood friend?;What school did you attend for sixth grade?';
            $question_set_two_string = 'In what city or town was your first job?;What is your favorite sport?;Who is your favorite sports player?;What is your grandmothers maiden name?;What was your first vehicles registration number?';
        }else{
            $question_set_one_string = $mo_db_values['mo_auth_custom_kba_set_1'];
            $question_set_two_string = $mo_db_values['mo_auth_custom_kba_set_2'];
        }

        if ( $question_set === 'ONE' ) {
            /** If type == STRING then send unprocessed string to show in the textarea ( login Settings Tab ) **/
            return $type === 'STRING'? $question_set_one_string : self::get_kba_array( $question_set_one_string );
        }
        /** If type == STRING then send unprocessed string to show in the textarea ( login Settings Tab ) **/
        return $type === 'STRING' ? $question_set_two_string : self::get_kba_array( $question_set_two_string );
    }

    /**
     * @param $kba_question_string = to process and return question array
     * @return array = question array
     */
    public static function get_kba_array( $kba_question_string ) {
        $kba_question = explode(';', $kba_question_string);
        $question_array = array();
        foreach ( $kba_question as $key => $value ) {
            $question_array[$value] = $value;
        }
        return $question_array;
    }

    /**
     * Return current URL parts.
     */
    public static function mo_auth_get_url_parts() {
        $query_param = \Drupal::service('path.current')->getPath();
        $url_parts   = explode('/', $query_param );
        return $url_parts;
    }


    /**
     * Return module tab URL
     * @param $tab_name
     * @return string = URL
     */
    public static function get_mo_tab_url( $tab_name ){
        global $base_url;
        if( $tab_name === 'LOGIN' ) {
            return $base_url . '/admin/config/people/miniorange_2fa/login_settings';
        }elseif ( $tab_name === 'SUPPORT' ) {
            return $base_url . '/admin/config/people/miniorange_2fa/support';
        }elseif ( $tab_name === 'CUSTOMER_SETUP' ) {
            return  $base_url .'/admin/config/people/miniorange_2fa/customer_setup';
        }elseif ( $tab_name === 'LOGS' ) {
            return  $base_url .'/admin/reports/dblog';
        }
    }


    /**
     * When user cancel the test/configuration process redirect him to setup 2fa page
     */
    public static function mo_handle_form_cancel() {
        global $base_url;
        $url = $base_url . '/admin/config/people/miniorange_2fa/setup_twofactor';
        $response = new TrustedRedirectResponse( $url );
        $response->send();
    }

    public static function show_error_or_success_message( $message, $status ) {
        global $base_url;
        $url = $base_url . '/admin/config/people/miniorange_2fa/setup_twofactor';
        \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('mo_auth_2fa_Success/Error message', $message)->save();
        \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('mo_auth_2fa_Success/Error status', $status)->save();
        $response = new TrustedRedirectResponse($url);
        $response->send();
    }

    /**
     * @param $message = Which you want to add in the logger report.
     * @param $typeOfLogger = error, notice, info, emergency, warning, alert, critical, debug.
     */
    public static function mo_add_loggers_for_failures( $message, $typeOfLogger ) {
          \Drupal::logger('miniorange_2fa')->$typeOfLogger( $message );
    }

    public static function isCurlInstalled() {
        if (in_array('curl', get_loaded_extensions())) {
          return 1;
        }
        return 0;
    }

    public static function isCustomerRegistered() {
        $variables_and_values = array(
            'mo_auth_customer_admin_email',
            'mo_auth_customer_id',
            'mo_auth_customer_api_key',
            'mo_auth_customer_token_key',
        );
        $mo_db_values = self::miniOrange_set_get_configurations( $variables_and_values, 'GET' );

        if ( $mo_db_values['mo_auth_customer_admin_email'] == NULL || $mo_db_values['mo_auth_customer_id'] == NULL
            || $mo_db_values['mo_auth_customer_token_key'] == NULL || $mo_db_values['mo_auth_customer_api_key'] == NULL ) {
            return FALSE;
        }
        return TRUE;
    }

  /**
   * Add premium tag if free module activated
   */
  public static function mo_add_premium_tag() {
      global $base_url ;
      $url = $base_url .'/admin/config/people/miniorange_2fa/licensing';
      $mo_premium_tag = '<a href= "'.$url.'" style="color: red; font-weight: lighter;">[PREMIUM]</a>';
      if ( \Drupal::config('miniorange_2fa.settings')->get('mo_auth_2fa_license_type') != 'DEMO' ) {
          return '';
      }
      return $mo_premium_tag;
  }

    /**
     * @return array|false|string
     * Function to get the client IP address
     */
   static function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
   }

    /**
     * @param $mo_saved_IP_address = IP Addresses entered by user
     * @return boolean | string if error
     * Check whether provided IP is valid or not
     */
    Public static function check_for_valid_IPs( $mo_saved_IP_address ) {
        /** Separate IP address with the semicolon (;) **/
        $whitelisted_IP_array = explode(";", rtrim( $mo_saved_IP_address, ";") );
        foreach( $whitelisted_IP_array as $key => $value ) {
            if($value == "::1"){
              continue;
            }
            if( stristr( $value, '-' ) ) {
                /** Check if it is a range of IP address **/
                list( $lower, $upper ) = explode('-', $value, 2 );
                if ( !filter_var( $lower, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) && !filter_var( $upper, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
                    return "Invalid IP (<strong> ". $lower . "-" . $upper . "</strong> ) address. Please check lower range and upper range.";
                }
                $lower_range = ip2long( $lower );
                $upper_range = ip2long( $upper );
                if ( $lower_range >= $upper_range ){
                    return "Invalid IP range (<strong> ". $lower . "-" . $upper . "</strong> ) address. Please enter range in <strong>( lower_range - upper_range )</strong> format.";
                }
            }else {
                /** Check if it is a single IP address **/
                if ( !filter_var( $value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
                    return " Invalid IP (<strong> ". $value . "</strong> ) address. Please enter valid IP address.";
                }
            }
        }
        return TRUE;
    }

    /**
     * @return bool
     */
    public static function check_white_IPs(){
      $enable_whitelisted_IP = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_enable_whitelist_IPs');
      if( $enable_whitelisted_IP == FALSE ){
          return FALSE;
      }
      $current_IP_address = self::get_client_ip();
      $whitelisted_IP = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_whitelisted_IP_address');
      if( is_null( $whitelisted_IP ) || empty( $whitelisted_IP ) ){
          return FALSE;
      }
      $whitelisted_IP_array = explode(";", $whitelisted_IP );
      $mo_ip_found = FALSE;

      foreach( $whitelisted_IP_array as $key => $value ) {
          if( stristr( $value, '-' ) ){
              /** Search in range of IP address **/
              list($lower, $upper) = explode('-', $value, 2);
              $lower_range = ip2long( $lower );
              $upper_range = ip2long( $upper );
              $current_IP  = ip2long( $current_IP_address );
              if( $lower_range !== FALSE && $upper_range !== FALSE && $current_IP !== FALSE && ( ( $current_IP >= $lower_range ) && ( $current_IP <= $upper_range ) ) ){
                    $mo_ip_found = TRUE;
                    break;
              }
          }else {
              /** Compare with single IP address **/
              if( $current_IP_address == $value ){
                  $mo_ip_found = TRUE;
                  break;
              }
          }
      }
      return $mo_ip_found;
    }

    /**
     * @return array = All the roles available in the Drupal site
     */
    Public static function get_Existing_Drupal_Roles() {
        $roles = Role::loadMultiple();
        $roles_arr = array();
        foreach ( $roles as $key => $value ) {
          /** Skip Anonymous user role **/
          if( $key == 'anonymous' )
              continue;
           $roles_arr[$key] = $value->label();
        }
        return $roles_arr;
    }

    public static function check_roles_to_invoke_2fa( $roles ) {
        $variables_and_values = array(
            'mo_auth_enable_role_based_2fa',
            'mo_auth_use_only_2nd_factor',
            'mo_auth_role_based_2fa_roles',
        );
        $mo_db_values = MoAuthUtilities::miniOrange_set_get_configurations( $variables_and_values, 'GET' );

        if( $mo_db_values['mo_auth_enable_role_based_2fa'] !== TRUE || $mo_db_values['mo_auth_use_only_2nd_factor'] === TRUE ) {
            return TRUE;
        }
        $return_value   = FALSE;
        $selected_roles = ( array ) json_decode( $mo_db_values['mo_auth_role_based_2fa_roles'] );
        foreach( $selected_roles as $sysName => $displayName ) {
            if( in_array( $sysName, $roles, TRUE ) ) {
                $return_value = TRUE;
                break;
            }
        }
        return $return_value;
    }

    public static function check_domain_to_invoke_2fa( $moUserEmail ) {
        /* Need all the commneted code in this function */

		$variables_and_values = array(
            'mo_auth_enable_domain_based_2fa',
            'mo_auth_domain_based_2fa_domains',
            //'mo_auth_2fa_domain_exception_emails',
            //'mo_2fa_domains_are_white_or_black',
        );
        $mo_db_values = MoAuthUtilities::miniOrange_set_get_configurations( $variables_and_values, 'GET' );
        if( $mo_db_values['mo_auth_enable_domain_based_2fa'] != TRUE ) {
            return TRUE;
        }
        $return_value  = FALSE;
        $selected_domains = explode(';', $mo_db_values['mo_auth_domain_based_2fa_domains'] );
        $moUserDomain = substr( strrchr( $moUserEmail, "@" ), 1 );
        if ( in_array($moUserDomain, $selected_domains ) ) {
            $return_value = TRUE;
        }

        /*if( $return_value == TRUE ) {
            $exceptionEmails = $mo_db_values['mo_auth_2fa_domain_exception_emails'];
            $exceptionEmailsArray = explode(";", $exceptionEmails );
            foreach ( $exceptionEmailsArray as $key => $value ) {
                if( strcasecmp( $value, $moUserEmail ) == 0 ) {
                    $return_value = FALSE;
                    break;
                }
            }
        }
        $whiteOrBlack = $mo_db_values['mo_2fa_domains_are_white_or_black'] == 'white' ? FALSE : TRUE;
        return $return_value == $whiteOrBlack ;*/

        return $return_value;
    }


    public static function getHiddenEmail($email) {
        $split = explode("@", $email);
        if (count($split) == 2) {
            $hidden_email = substr($split[0], 0, 1) . 'xxxxxx' . substr($split[0], - 1) . '@' . $split[1];
            return $hidden_email;
        }
        return $email;
    }

    public static function indentSecret($secret) {
        $strlen = strlen( $secret );
        $indented = '';
        for ( $i = 0; $i <= $strlen; $i = $i + 4 ) {
            $indented .= substr($secret, $i, 4) . ' ';
        }
        $indented = trim( $indented );
        return $indented;
    }

  public static function callService( $customer_id, $apiKey, $url, $json, $redirect_to_error_page = true ) {
    if ( !self::isCurlInstalled() ) {
      if (!$redirect_to_error_page) {
        return (object)(array(
          "status" => 'CURL_ERROR',
          "message" => 'PHP cURL extension is not installed or disabled.'
        ));
      }
      self::showErrorMessage("cURL Error","","PHP cURL extension is not installed or disabled.");
    }

    $current_time_in_millis = round(microtime(TRUE ) * 1000 );
    $string_to_hash         = $customer_id . number_format( $current_time_in_millis, 0, '', '' ) . $apiKey;
    $hash_value             = hash("sha512", $string_to_hash );

    $moHeaders = array (
      "Content-Type"  => "application/json",
      "Customer-Key"  => $customer_id,
      "Timestamp"     => number_format( $current_time_in_millis, 0, '', '' ),
      "Authorization" => $hash_value
    );

    $response = \Drupal::httpClient()->post($url, [
          'body' => $json,
          'http_errors' => FALSE,
          'headers' => $moHeaders,
          'verify'=>false
    ]);

    return json_decode($response->getBody());
  }

  public static function check_for_userID($user_id) {
      $connection = \Drupal::database();
      $query = $connection->query("SELECT * FROM {UserAuthenticationType} where uid = $user_id");
      $query->allowRowCount = TRUE;
      if($query->rowCount()>0){
          return TRUE;
      }
      return FALSE;
  }

  public static function get_users_custom_attribute($user_id) {
      $connection = \Drupal::database();
      $query      = $connection->query("SELECT * FROM {UserAuthenticationType} where uid = $user_id");
      $result     = $query->fetchAll();
      return $result;
  }

    public static function mo_auth_get_configured_methods($user_id) {
        $utilities        = new MoAuthUtilities();
        $custom_attribute = $utilities->get_users_custom_attribute($user_id);
        if( is_null( $custom_attribute ) || empty( $custom_attribute ) )
             return array();
        $myArray            = explode(',', $custom_attribute[0]->configured_auth_methods );
        $configured_methods = array_map('trim', $myArray );
        return $configured_methods;
    }

    public static function mo_auth_is_kba_configured($user_id) {
        $utilities          = new MoAuthUtilities();
        $custom_attribute   = $utilities->get_users_custom_attribute( $user_id );
        $myArray            = explode(',', $custom_attribute[0]->configured_auth_methods );
        $configured_methods = array_map('trim', $myArray );
        return array_search( AuthenticationType::$KBA['code'], $configured_methods );
    }

    /**
     * @return string - Drupal core version
     */
    public static function mo_get_drupal_core_version() {
        return \DRUPAL::VERSION;
    }

    public static function isTFARequired( $roles, $email ) {
      $variables_and_values1 = array(
        'mo_auth_enable_domain_based_2fa',
        'mo_auth_enable_role_based_2fa',
        'mo_auth_use_only_2nd_factor'
      );
      $mo_db_values = MoAuthUtilities::miniOrange_set_get_configurations( $variables_and_values1, 'GET' );

      $userInRoles  = MoAuthUtilities::check_roles_to_invoke_2fa( $roles );
      $userInDomain = MoAuthUtilities::check_domain_to_invoke_2fa( $email);

      $TFARequired  = $userInDomain && $userInRoles;
      if( $mo_db_values['mo_auth_enable_domain_based_2fa'] == TRUE && $mo_db_values['mo_auth_enable_role_based_2fa'] == TRUE ){
        $TFARequired = $mo_db_values['mo_2fa_domain_and_role_rule'] === 'OR' ? $userInRoles || $userInDomain : $userInRoles && $userInDomain;
      }

      $TFARequired = $mo_db_values['mo_auth_use_only_2nd_factor']===TRUE || $TFARequired  ;

      return $TFARequired;
    }

    public static function invoke2fa_OR_inlineRegistration( $username, $tmpDestination = '' ) {
        $variables_and_values1 = array(
            'mo_auth_enforce_inline_registration',
            'mo_auth_2fa_license_type',
            'mo_2fa_domain_and_role_rule',
            'mo_auth_use_only_2nd_factor',
            'mo_auth_enable_backdoor',
            'mo_auth_backdoor_login_access',
            'mo_auth_enable_domain_based_2fa',
            'mo_auth_enable_role_based_2fa',

        );
        $mo_db_values = MoAuthUtilities::miniOrange_set_get_configurations( $variables_and_values1, 'GET' );

        $user = user_load_by_name( $username );

        if( $user === false ) {
            \Drupal::messenger()->addError(t('Invalid credentials'));
            return;
        }

        $user_id = $user->id();
        $roles   = $user->getRoles();

        $session = self::getSession();
        $session->set( 'mo_auth', array( 'status' => '1ST_FACTOR_AUTHENTICATED', 'uid' => $user_id, 'challenged' => 0, 'user_email' => $user->getEmail()) );
        $session->save();

        $custom_attribute = MoAuthUtilities::get_users_custom_attribute( $user_id );
        $tfaEnabled = FALSE;
        if( count( $custom_attribute ) > 0 ) {
            $user_email = $custom_attribute[0]->miniorange_registered_email;
            $tfaEnabled = $custom_attribute[0]->enabled == 1;
        }

        $customer      = new MiniorangeCustomerProfile();
        $loginSettings = $mo_db_values['mo_auth_enforce_inline_registration'];
        $license_type  = ( $mo_db_values['mo_auth_2fa_license_type'] == '') ? 'DEMO' : $mo_db_values['mo_auth_2fa_license_type'];
        if( empty( $user_email ) && $mo_db_values['mo_auth_use_only_2nd_factor'] && !isset( $_POST['pass'] ) ) {
            \Drupal::configFactory()->getEditable('miniorange_2fa.settings')->set('mo_auth_2fa_use_pass', TRUE)->save();
            return;
        }

        /**
         * Role based and Domain Based 2FA check
         */
        $mo_auth_backdoor_enabled = $mo_db_values['mo_auth_enable_backdoor'];
        $backdoor_url_query       = $mo_db_values['mo_auth_backdoor_login_access'];
        $query_parameters         = \Drupal::request()->query->get('login_2fa');

        // check for interaction only iff both are enabled
        $userInRoles  = MoAuthUtilities::check_roles_to_invoke_2fa( $roles );
        $userInDomain = MoAuthUtilities::check_domain_to_invoke_2fa( $user->getEmail() );

        if( count( $custom_attribute ) > 0 )
          $TFARequired =  $tfaEnabled;
        else {
          $TFARequired  = $userInDomain && $userInRoles;
          if($mo_db_values['mo_auth_enable_domain_based_2fa'] == TRUE && $mo_db_values['mo_auth_enable_role_based_2fa'] == TRUE ){
            $TFARequired = $mo_db_values['mo_2fa_domain_and_role_rule'] === 'OR' ? $userInRoles || $userInDomain : $userInRoles && $userInDomain;
          }
        }
          $TFARequired = $mo_db_values['mo_auth_use_only_2nd_factor']===TRUE || $TFARequired;

        if( ( !$mo_auth_backdoor_enabled || !isset( $query_parameters ) || ( $query_parameters != $backdoor_url_query ) ) || !( $user->hasRole('administrator') || $user->hasRole('admin') ) ) {
            if ( $TFARequired ) {
                if ( !empty( $user_email ) ) {
                    if ( $license_type == 'PREMIUM' || $license_type == 'DRUPAL_2FA_PLUGIN' || $license_type == 'DRUPAL8_2FA_MODULE' ) {
                        $url = Url::fromRoute('miniorange_2fa.authenticate_user', ['user'=>$user_id])->toString();
                        $response = new RedirectResponse( $url );
                        $response->send();
                        exit;
                    } elseif ( in_array('administrator', $roles ) || in_array('admin', $roles ) && $user_email == $customer->getRegisteredEmail() ) {
                        $url = Url::fromRoute('miniorange_2fa.authenticate_user', ['user'=>$user_id])->toString();
                        $response = new RedirectResponse( $url );
                        $response->send();
                        exit;
                    }
                } elseif ( ( $license_type == 'PREMIUM' || $license_type == 'DRUPAL_2FA_PLUGIN' || $license_type == 'DRUPAL8_2FA_MODULE' ) && $loginSettings ) {
                    $url = Url::fromRoute('miniorange_2fa.inline_registration', ['user'=>$user_id])->toString();
                    $response = new RedirectResponse( $url );
                    $response->send();
                    exit;
                }
            } else {
                $_GET['destination'] = $tmpDestination;
            }
        }
        $user = User::load( $user_id );
        user_login_finalize( $user );
        if ( $userInRoles ) {
            $url = Url::fromRoute('user.login')->toString();
            $response = new RedirectResponse($url);
            $response->send();
            exit;
        }
    }

    public static function showErrorMessage( $error, $message, $cause, $closeWindow = FALSE ) {
      global $base_url;
      $actionToTakeUponWindow = $closeWindow === TRUE ? 'onClick="self.close();"' : 'href="' . $base_url . '/user/login"';
      echo '<div style="font-family:Calibri;padding:0 3%;">';
      echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                                  <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>' . xss::filter( $error ) . '</p>
                                      <p>' . xss::filter( $message ) . '</p>
                                      <p><strong>Possible Cause: </strong>' . xss::filter( $cause ) . '</p>
                                  </div>
                                  <div style="margin:3%;display:block;text-align:center;"></div>
                                  <div style="margin:3%;display:block;text-align:center;">
                                      <a style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF; text-decoration: none;"type="button"  '.$actionToTakeUponWindow .' >Done</a>
                                  </div>';
      exit;
    }
    static function getSession(){
      $session_manager = \Drupal::service('session_manager');
      if(!$session_manager->isStarted()){
        $session_manager->start();
      }

      $request = \Drupal::request();
      return $request->getSession();
    }

    static function updateMfaSettingsForUser($uid, $enableMfa = 1 ) {
      // Enter the user details in the userAuthenticationType Table
      $database = \Drupal::database();
      $result = self::get_users_custom_attribute($uid);
      if( count( $result ) > 0 ) {
        $database->update('UserAuthenticationType')->fields(['enabled' => $enableMfa])->condition('uid', $uid,'=')->execute();
      }
      else{
          $fields = array('uid' => $uid,'enabled' => $enableMfa);
          try {
              $database->insert('UserAuthenticationType')->fields($fields)->execute();
          } catch (Exception $e) {

          }
      }
    }


  static function isUserCanSee2FASettings(){
    // User can see MFA settings only iff
    /**
     * 1. User is Admin
     * 2. User is authenticated and his roles and email is appropriate to see the MFA settings
     *
     * **/
    $variableAndValues = self::miniOrange_set_get_configurations(['allow_end_users_to_decide','end_users_can_decide_without_rules','skip_not_allowed_for_secured_users','only_some_admins_can_edit_2fa_configs','list_of_admins_who_can_edit_2fa_configs'],"GET");
    $account = \Drupal::currentUser();
    $separator = FALSE;
    $user_id = 0;
    $path = \Drupal::service('path.current')->getPath();
    if( strpos( $path,"user/" ) !== FALSE )
      $separator = "user/";
    if( strpos( $path,"mfa_setup" ) !== FALSE )
      $separator = "mfa_setup/";
    if( $separator !== FALSE )
      $user_id = explode("/",explode($separator, $path)[1])[0];

    // user is authenticated and he has the admin rights
    $includedAdmin = TRUE;
    if($account->isAuthenticated() && $account->hasPermission('administer users') && $variableAndValues['only_some_admins_can_edit_2fa_configs']){
      $userIdsOfAdmins = str_replace(" ","",$variableAndValues['list_of_admins_who_can_edit_2fa_configs']);
      $userIdsOfAdmins = explode(';',$userIdsOfAdmins);
      $includedAdmin =  in_array(strval($account->id()),$userIdsOfAdmins);
    }
    if($account->isAuthenticated() && $account->hasPermission('administer users') ){
      if($includedAdmin)
        return TRUE;
      if( !$includedAdmin && intval( $user_id ) !== intval( $account->id() ) )
        return FALSE;
    }
    // Iff opt-in opt out is disabled or user is not logged in then he can't see the 2FA settings
    if( !$variableAndValues['allow_end_users_to_decide'] || !$account->isAuthenticated() ) {
      return FALSE;
    }

    // if opt-in opt out is enabled for all users or TFA required for this user or tfa enabled for this user
    elseif( $variableAndValues['end_users_can_decide_without_rules'] || MoAuthUtilities::isTFARequired( $account->getRoles(), $account->getEmail() ) ) {
        return TRUE;
    }
    else{
      $tfaEnabled = FALSE;
      $custom_attributes = self::get_users_custom_attribute($account->id());
      if( count( $custom_attributes ) > 0 ) {
        $tfaEnabled = $custom_attributes[0]->enabled == 1;
      }
      if( $tfaEnabled )
        return TRUE;
    }
    return FALSE;
  }

    static  function isSkipNotAllowed($uid){
        $user = User::load(intval($uid));
        $variables_and_values = array(
            'allow_end_users_to_decide',
            'mo_auth_two_factor_instead_password',
            'skip_not_allowed_for_secured_users',
        );
        $mo_db_values = self::miniOrange_set_get_configurations($variables_and_values,"GET");
        if($mo_db_values['allow_end_users_to_decide']){
            return $mo_db_values['mo_auth_two_factor_instead_password'] || (self::isTFARequired($user->getRoles(),$user->getEmail()) && $mo_db_values['skip_not_allowed_for_secured_users']);
        }
        return TRUE;
    }

    static function getUserPhoneNumber( $uid ) {
        $variables_and_values = array(
            'auto_fetch_phone_number',
            'phone_number_field_machine_name',
            'auto_fetch_phone_number_country_code',
			'mo_auth_enable_headless_two_factor',
        );
        $mo_db_values = self::miniOrange_set_get_configurations($variables_and_values,"GET");
        if( $mo_db_values['auto_fetch_phone_number'] || $mo_db_values['mo_auth_enable_headless_two_factor'] ) {
            $fieldName = $mo_db_values['phone_number_field_machine_name'];
            $user = User::load( $uid );
            $countryCode = $phone = $mo_db_values['auto_fetch_phone_number_country_code'];
            if( !is_null($user ) ) {
                $user = $user->toArray();
            if( isset( $user[$fieldName]['0']['value'] ) )
                $phone = $user[$fieldName]['0']['value'];
            if( strpos( $phone,"+" ) === FALSE )
                $phone = strval($countryCode).strval($phone);
            }
            return $phone;
        }
        return null;
    }

    static function loadUserByPhoneNumber( $phoneNumber ) {
        $fieldSet = array (
            'status' => '',
            'userID' => '',
            'error'  => '',
        );

        $variables_and_values = array(
            'phone_number_field_machine_name',
            'mo_auth_enable_login_with_phone',
        );
        $mo_db_values   = self::miniOrange_set_get_configurations($variables_and_values,"GET");
        $phoneFieldName = $mo_db_values['phone_number_field_machine_name'];
        if( $mo_db_values['mo_auth_enable_login_with_phone'] !== TRUE || !isset( $phoneFieldName ) || empty( $phoneFieldName ) ) {
            $fieldSet['status'] = 'FAILED';
            $fieldSet['error']  = 'Login with phone number is not enabled on this site';
            return $fieldSet;
        }
        $tableName      = 'user__' . $phoneFieldName;
        $colomnName     = $phoneFieldName . '_value';

        $connection = \Drupal::database();
        $query      = $connection->query("SELECT {entity_id}, {$colomnName} FROM {$tableName} where $colomnName = $phoneNumber");
        $result     = $query->fetchAllkeyed();

        /** Get the count of each phone numbers available in the DB*/
        $phoneNumberCount = array_count_values( $result );

        if( !isset( $phoneNumberCount[$phoneNumber] ) ) { //check whether any accounts has given number
            $fieldSet['status'] = 'FAILED';
            $fieldSet['error']  = 'Account does not exist. Please enter the phone number in an exact format as mentioned under your account. ( i.e +1xxxxxxxxxx or 1xxxxxxxxxx or xxxxxxxxx )';
        } elseif( $phoneNumberCount[$phoneNumber] === 1 ) { //check whether only one accounts consist given number
            $userID = array_search( $phoneNumber, $result );
            $fieldSet['status'] = 'SUCCESS';
            $fieldSet['userID'] = $userID;
        } elseif ( $phoneNumberCount[$phoneNumber] >= 2 ) { //check whether multiple accounts consist given number
            $fieldSet['status'] = 'FAILED';
            $fieldSet['error']  = 'Multiple accounts found with the phone number <strong>' . $phoneNumber. '</strong>. Please login with username.';
        }
        return $fieldSet;
    }

    public static function getUpgradeURL( $upgradePlan ) {
        $variables_and_values = array(
            'mo_auth_customer_admin_email',
        );
        $mo_db_values = self::miniOrange_set_get_configurations($variables_and_values,"GET");

        return MoAuthConstants::getBaseUrl(). '/login?username=' . $mo_db_values['mo_auth_customer_admin_email'] . '&redirectUrl=' . MoAuthConstants::getBaseUrl() . '/initializepayment&requestOrigin=' . $upgradePlan;
    }

    public static function getIsLicenseExpired( $date ) {
        $days = intval(( strtotime( $date ) - time() ) / ( 60 * 60 * 24 ) );

        $returnLicenseExpiry = array();
        $returnLicenseExpiry['LicenseGoingToExpire'] = $days < 35 ? TRUE : FALSE;
        $returnLicenseExpiry['LicenseAlreadyExpired'] = $days < 0 ? TRUE : FALSE;

        return $returnLicenseExpiry;
    }
}