<?php
/**
 * @file
 * Contains support form for miniOrange 2FA Login Module.
 */
namespace Drupal\miniorange_2fa\Form;

use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\miniorange_2fa\MoAuthConstants;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;

/* Showing LoginSetting form info. */

class MoAuthHeadlessSetup extends FormBase {
    public function getFormId() {
        return 'miniorange_2fa_user_management';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        global $base_url ;
        $utilities = new MoAuthUtilities();

        $variables_and_values = array(
            'mo_auth_enable_headless_two_factor',
            'mo_auth_headless_2fa_method',
            'phone_number_field_machine_name',
            //'mo_auth_2fa_license_type'.
            //'mo_auth_customer_token_key',
        );
        $mo_db_values = $utilities->miniOrange_set_get_configurations( $variables_and_values, 'GET' );

        //$license_type = $mo_db_values['mo_auth_2fa_license_type'] == '' ? 'DEMO' : $mo_db_values['mo_auth_2fa_license_type'];
        //$is_free = $license_type == 'DRUPAL_2FA_PLUGIN' || $license_type == 'PREMIUM' || $license_type == 'DRUPAL8_2FA_MODULE' ? FALSE : TRUE;

        $form['markup_top_2'] = array(
            '#markup' => '<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_2fa_container">'
        );

        $disabled = False;
        if ( !$utilities::isCustomerRegistered() ) {
            $form['header']  = array(
                '#markup' => t('<div class="mo_2fa_register_message"><p>'.t('You need to').' <a href="'.$base_url.'/admin/config/people/miniorange_2fa/customer_setup">'.t('Register/Login').'</a> '.t('with miniOrange before using this module.').'</p></div>'),
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

        /**
         * Create container to hold all the form elements.
         */
        $form['mo_headless_setup'] = array(
            '#type' => 'fieldset',
            '#title' => t('Headless/Decoupled 2FA Setup'),
            '#attributes' => array( 'style' => 'padding:0% 2% 5%' )
        );

        $form['mo_headless_setup']['mo_auth_enable_headless_two_factor'] = array(
            '#type' => 'checkbox',
            '#default_value' => $mo_db_values['mo_auth_enable_headless_two_factor'],
            '#title' => t('Enable Headless Two-Factor.'),
            '#disabled' => $disabled,
            '#description' => t('<strong><span style="color: red">Note:</span></strong> Enable this if you wish to activate/use the Headless/Decoupled 2FA service.'),
            '#prefix' => t('<br><br><hr>'),
            '#suffix' => '<br><hr><br>',
        );

        $form['mo_headless_setup']['mo_auth_headless_2fa_method'] = array(
            '#type' => 'select',
            '#title' => t('Select 2FA method'),
            '#options' => array(
                AuthenticationType::$OTP_OVER_EMAIL['code'] => AuthenticationType::$OTP_OVER_EMAIL['name'],
                AuthenticationType::$SMS['code']            => AuthenticationType::$SMS['name'],
                AuthenticationType::$SMS_AND_EMAIL['code']  => AuthenticationType::$SMS_AND_EMAIL['name'],
                AuthenticationType::$OTP_OVER_PHONE['code'] => AuthenticationType::$OTP_OVER_PHONE['name'],
            ),
            '#default_value' => $mo_db_values['mo_auth_headless_2fa_method'],
            '#attributes' => array('style' => 'width:60%;height:29px;'),
            '#states' => array('disabled' => array(':input[name = "mo_auth_enable_headless_two_factor"]' => array('checked' => FALSE ), ), ),
            '#disabled' => $disabled,
        );

        $accountConfigUrl = Url::fromRoute('entity.user.field_ui_fields')->toString();

        $form['mo_headless_setup']['phone_number_field_machine_name'] = array(
            '#type' => 'textfield',
            '#title' => t('Enter machine name of the phone number field'),
            '#default_value' => $mo_db_values['phone_number_field_machine_name'],
            '#description' => t('<strong>Note: </strong><a target="_blank" href="'.$accountConfigUrl.'">Click here</a> to check available fields on your Drupal site.'),
            '#attributes' => array('style' => 'width:60%;'),
            '#states' => array('disabled' => array(':input[name = "mo_auth_enable_headless_two_factor"]' => array('checked' => FALSE ), ), ),
            '#disabled' => $disabled,
            '#suffix'      => '<br><br>'
        );

        $form['mo_headless_setup']['Submit_mo_headless_setup_form'] = array(
            '#type'        => 'submit',
            '#button_type' => 'primary',
            '#value'       => t('Save Settings'),
            '#states' => array('disabled' => array(':input[name = "mo_auth_enable_headless_two_factor"]' => array('checked' => FALSE ), ), ),
            '#disabled'    => $disabled,
            '#suffix'      => '<br><br><br><hr>'
        );

        $form['mo_headless_setup']['mo_headless_APIs'] = array(
            '#type' => 'fieldset',
            '#title' => t('Steps to integrate 2FA'),
            '#attributes' => array( 'style' => 'padding:0% 2% 8%' )
        );

        $form['mo_headless_setup']['mo_headless_APIs']['StepOneItemOne'] = array(
            '#type' => 'item',
            '#title' => t('<span style="color: orange">STEP 1:</span>'),
            '#description' => t('The first step is to authenticate user by sending Username and Password to our /headless/authenticate endpoint (API) so they can authenticated against the Drupal database. Once the user is authenticated successfully, OTP will be sent to registered mobile/email.'),
            '#prefix'  => '<br><br><hr><br>'
        );

      /**
       * Do not remove spaces here or do not change the alignments
       */

        $form['mo_headless_setup']['mo_headless_APIs']['StepOneItemTwo'] = array(
            '#type' => 'item',
            '#title' => 'POST ' . $base_url . '/headless/authenticate',
            '#prefix' => '<div class="mo_2fa_highlight_background_note"><code>',
            '#description' => t('<br>What you will send: <br> {"username":"xxxxx","password":"xxxxx"}  
    
    <br><br>If successful, you will receive back the following response: {"username":"xxxxx","status":"SUCCESS","message":"xxxxx","transactionID":"xxxxx","authType":"xxxxx"}<br><br>'),
        );

        /*$updateLicense = [
            'data' => Markup::create('Send this <code>' . $mo_db_values['mo_auth_customer_token_key'] . '</code> token key.')
        ];

        $mo_token_key =  !$utilities::isCustomerRegistered() ? 'Activate the module first to get the Token Key.' : $updateLicense;*/

        $mo_table_content = array (
            array( 'username', 'string', 'required', 'Entered by the user on the login form.' ),
            array( 'password', 'string', 'required', 'Entered by the user on the login form.' ),
            //array( 'tokenkey', 'string', 'required', $mo_token_key ),
        );

        $form['mo_headless_setup']['mo_headless_APIs']['StepOneTableOne'] = array(
            '#type' => 'table',
            '#header'=> array( 'Parameter', 'Type', 'Required?', 'Description' ),
            '#rows' => $mo_table_content,
            '#caption' => t('<h6>With the following parameters:</h6>'),
            '#responsive' => TRUE ,
            '#sticky'=> TRUE,
            '#size'=> 2,
            '#prefix' => '</code></div>',
        );

        $form['mo_headless_setup']['mo_headless_APIs']['StepTwoItemOne'] = array(
            '#type' => 'item',
            '#title' => t('<span style="color: orange">STEP 2:</span>'),
            '#description' => t('The second step is to validate the user by sending OTP (One time passcode) to our /headless/login endpoint (API).'),
            '#prefix'  => '<br><br><hr><hr><br>'
        );

        /**
         * Do not remove spaces here or do not change the alignments
         */

        $form['mo_headless_setup']['mo_headless_APIs']['StepTwoItemTwo'] = array(
            '#type' => 'item',
            '#title' => 'POST ' . $base_url . '/headless/login',
            '#prefix' => '<div class="mo_2fa_highlight_background_note"><code>',
            '#description' => t('<br>What you will send: <br> {"username":"xxxxx","transactionID":"xxxxx","authType":"xxxxx","otp":"xxxxx"}  
             <br><br>If successful, you will receive back the following response: <br> {"username":"xxxxx","status":"SUCCESS","message":"xxxxx","userprofile":"xxxxx"}<br><br>'),
        );

        $mo_table_content = array (
            array( 'username', 'string', 'required', 'You will get this in the response of first API call.' ),
            array( 'transactionID', 'string', 'required', 'You will get this in the response of first API call.' ),
            array( 'authType', 'string', 'required', 'You will get this in the response of first API call.' ),
            array( 'otp', 'string', 'required', 'One time passcode received over SMS/Email.' ),
            //array( 'tokenkey', 'string', 'required', $mo_token_key ),
        );

        $form['mo_headless_setup']['mo_headless_APIs']['StepTwoTableOne'] = array(
            '#type' => 'table',
            '#header'=> array( 'Parameter', 'Type', 'Required?', 'Description' ),
            '#rows' => $mo_table_content,
            '#caption' => t('<h6>With the following parameters:</h6>'),
            '#responsive' => TRUE ,
            '#sticky'=> TRUE,
            '#size'=> 2,
            '#prefix' => '</code></div>',
        );

        $InternalServerError = [
            'data' => Markup::create('You will get 500 Internal Server Error due to various reasons, please check </b><a target="_blank" href="' . $base_url . '/admin/reports/dblog">Drupal logs</a> for more details.' )
        ];

        $mo_table_content = array (
            array( '404 Not Found', 'Headless 2FA setting is not enabled. Please enable the same under Headless 2FA Setup tab of the module.' ),
            array( '401 Unauthorized', 'User has entered invalid credentials (username/password)' ),
            array( '403 Forbidden', 'User has entered the incorrect OTP (One time passcode)' ),
            array( '500 Internal Server Error', $InternalServerError ),
        );

        $form['mo_headless_setup']['mo_api_errors'] = array(
            '#type' => 'table',
            '#header'=> array( 'Error Code', 'Description' ),
            '#rows' => $mo_table_content,
            '#caption' => t('<h5>Possible errors:</h5>'),
            '#responsive' => TRUE ,
            '#sticky'=> TRUE,
            '#size'=> 2,
            '#prefix' => '<br>',
            '#suffix'   => '<br><br></div>'
        );

        $utilities::miniOrange_advertise_network_security( $form, $form_state);

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $utilities = new MoAuthUtilities();
        $form_values = $form_state->getValues();

        $variables_and_values = array(
          'mo_auth_enable_headless_two_factor'   => $form_values['mo_auth_enable_headless_two_factor'] === 1 ,
          'mo_auth_headless_2fa_method'          => $form_values['mo_auth_headless_2fa_method'],
          'phone_number_field_machine_name'      => $form_values['phone_number_field_machine_name'],
        );

        $utilities->miniOrange_set_get_configurations( $variables_and_values, 'SET' );

        \Drupal::messenger()->addStatus(t("Headless settings saved successfully."));
        return;
    }
}