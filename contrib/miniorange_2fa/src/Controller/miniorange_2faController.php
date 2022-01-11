<?php

/**
 * Default controller for the miniorange_2fa module.
 */

namespace Drupal\miniorange_2fa\Controller;

use Drupal\Core\Form\formBuilder;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\UsersAPIHandler;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Symfony\Component\HttpFoundation\Response;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;
use Symfony\Component\DependencyInjection\ContainerInterface;

class miniorange_2faController extends ControllerBase {
    protected $formBuilder;
    public function __construct(FormBuilder $formBuilder) {
        $this->formBuilder = $formBuilder;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get("form_builder")
        );
    }

  /**
   * @return Response
   */
    public function openModalForm() {
        $response = new AjaxResponse();
        $modal_form = $this->formBuilder->getForm('\Drupal\miniorange_2fa\Form\MoAuthRemoveAccount');
        $response->addCommand(new OpenModalDialogCommand('Remove Account', $modal_form, ['width' => '800'] ) );
        return $response;
    }

  /**
   * Check whether user has entered valid credentials and if available on Xecurify dashboard.
   */
    public function headless_2fa_authenticate() {
        $utilities = new MoAuthUtilities();
        $variables_and_values = array(
          'mo_auth_enable_headless_two_factor',
          'mo_auth_headless_2fa_method',
        );
        $mo_db_values = $utilities->miniOrange_set_get_configurations( $variables_and_values, 'GET' );

      /**
       * Check if headless 2FA enabled
       */
        if( $mo_db_values['mo_auth_enable_headless_two_factor'] !== TRUE ) {
            $utilities->mo_add_loggers_for_failures('Headless 2FA settings not enabled. Please enable the same under Headless 2FA Setup tab of the module.','error');
            $json['status'] = 'ERROR';
            $json['message'] = 'Something went wrong, please contact your administrator.';
            $json = json_encode($json);
            header("HTTP/1.1 404 Not Found");
            header('Content-Type: application/json;charset=utf-8');
            echo $json;
            exit;
        }

        $getCredentials = file_get_contents('php://input');
        $jsonCredentials = json_decode($getCredentials, TRUE);
        $username = $jsonCredentials['username'];
        $password = $jsonCredentials['password'];

        if( !( \Drupal::service('user.auth')->authenticate( $username, $password ) ) ) {
            $utilities->mo_add_loggers_for_failures($username .' - Invalid username/password','error');
            $json['username'] = $username;
            $json['status'] = 'ERROR';
            $json['message'] = 'Invalid username/password';
            $json = json_encode($json);
            header("HTTP/1.1 401 Unauthorized");
            header('Content-Type: application/json;charset=utf-8');
            echo $json;
            exit;
        }

        $user   = user_load_by_name( $username );
        $email  = $user->getEmail();
        $phone  = $utilities->getUserPhoneNumber($user->id());
        $method = $mo_db_values['mo_auth_headless_2fa_method'];

        $customer = new MiniorangeCustomerProfile();
        $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $email, $phone, NULL, $method, $email);
        $user_api_handler = new UsersAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
        $response = $user_api_handler->search( $miniorange_user );

        if ( $response->status === 'USER_FOUND' ) {
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->challenge($miniorange_user);

            if ( is_object( $response ) && $response->status === 'SUCCESS' ) {
                $json['username'] = $username;
                $json['status'] = 'SUCCESS';
                $json['message'] = $response->message;
                $json['transactionID'] = $response->txId;
                $json['authType'] = $response->authType;
                $json = json_encode($json);
                header("HTTP/1.1 200 Ok");
                header('Content-Type: application/json;charset=utf-8');
                echo $json;
                exit;
            } else {
                $utilities->mo_add_loggers_for_failures( $response->message,'error');
                $json['username'] = $username;
                $json['status'] = 'ERROR';
                $json['message'] = 'Something went wrong, please contact your administrator.';
                $json = json_encode($json);
                header("HTTP/1.1 500 Internal Server Error");
                header('Content-Type: application/json;charset=utf-8');
                echo $json;
                exit;
            }
        }

        if ( $response->status === 'USER_NOT_FOUND' ) {
            /**
             * Create (end)user on Xecurify servers under admin account
             */
             $create_response = $user_api_handler->create($miniorange_user);
             if( isset( $create_response ) && isset( $create_response->status ) && isset( $create_response->message ) && $create_response->status == 'ERROR' && $create_response->message == t('Your user creation limit has been completed. Please upgrade your license to add more users.') ) {
                 $utilities->mo_add_loggers_for_failures('Your user creation limit has been completed. Please upgrade your license to add more users','error');
                 $json['username'] = $username;
                 $json['status'] = 'ERROR';
                 $json['message'] = 'Something went wrong, please contact your administrator.';
                 $json = json_encode($json);
                 header("HTTP/1.1 500 Internal Server Error");
                 header('Content-Type: application/json;charset=utf-8');
                 echo $json;
                 exit;
             }

            /**
             * Update User Auth method on Xecurify
             */
             $user_update_response = $user_api_handler->update($miniorange_user);
             if ( is_object( $user_update_response ) && $user_update_response->status != 'SUCCESS') {
                 $utilities->mo_add_loggers_for_failures($user_update_response->message,'error');
                 $json['username'] = $username;
                 $json['status'] = 'ERROR';
                 $json['message'] = 'Something went wrong, please contact your administrator.';
                 $json = json_encode($json);
                 header("HTTP/1.1 500 Internal Server Error");
                 header('Content-Type: application/json;charset=utf-8');
                 echo $json;
                 exit;
             }

             $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
             $response = $auth_api_handler->challenge($miniorange_user);
             if ( is_object( $response ) && $response->status === 'SUCCESS' ) {
                 $json['username'] = $username;
                 $json['status'] = 'SUCCESS';
                 $json['message'] = $response->message;
                 $json['transactionID'] = $response->txId;
                 $json['authType'] = $response->authType;
                 $json = json_encode($json);
                 header("HTTP/1.1 200 Ok");
                 header('Content-Type: application/json;charset=utf-8');
                 echo $json;
                 exit;
             } else {
                 $utilities->mo_add_loggers_for_failures( $response->message,'error');
                 $json['username'] = $username;
                 $json['status'] = 'ERROR';
                 $json['message'] = 'Something went wrong, please contact your administrator.';
                 $json = json_encode($json);
                 header("HTTP/1.1 500 Internal Server Error");
                 header('Content-Type: application/json;charset=utf-8');
                 echo $json;
                 exit;
             }
        }
    }

  /**
   * Check whether user has entered valid OTP. if yes generate session.
   */
    public function headless_2fa_login() {

        $utilities = new MoAuthUtilities();
        $variables_and_values = array(
          'mo_auth_enable_headless_two_factor',
        );
        $mo_db_values = $utilities->miniOrange_set_get_configurations( $variables_and_values, 'GET' );

        /**
         * Check if headless 2FA enabled
         */
        if( $mo_db_values['mo_auth_enable_headless_two_factor'] !== TRUE ) {
            $utilities->mo_add_loggers_for_failures('Headless 2FA settings not enabled. Please enable the same under Headless 2FA Setup tab of the module.','error');
            $json['status'] = 'ERROR';
            $json['message'] = 'Something went wrong, please contact your administrator.';
            $json = json_encode($json);
            header("HTTP/1.1 404 Not Found");
            header('Content-Type: application/json;charset=utf-8');
            echo $json;
            exit;
        }

        $getCredentials  = file_get_contents('php://input');
        $jsonCredentials = json_decode($getCredentials, TRUE);

        $username        = $jsonCredentials['username'];
        $txId            = $jsonCredentials['transactionID'];
        $otp             = $jsonCredentials['otp'];
        $authType        = $jsonCredentials['authType'];

        $user  = user_load_by_name( $username );
        $email = $user->getEmail();
        $phone = $utilities->getUserPhoneNumber($user->id());

        $customer         = new MiniorangeCustomerProfile();
        $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $email, $phone, NULL, $authType, $email );
        $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
        $response         = $auth_api_handler->validate( $miniorange_user, $txId, $otp, NULL );

        $UserProfile = $user->toArray();
        unset($UserProfile['pass']);

        if ( $response->status === 'SUCCESS' ) {
            user_login_finalize( $user );
            $json['username'] = $username;
            $json['status']  = $response->status;
            $json['message'] = $response->message;
            $json['userprofile'] = $UserProfile;
            $json = json_encode($json);
            header("HTTP/1.1 200 Ok");
            header('Content-Type: application/json;charset=utf-8');
            echo $json;
            exit;

        } elseif ( $response->status === 'FAILED' ) {
            MoAuthUtilities::mo_add_loggers_for_failures( $response->message,'error');
            $json['username'] = $username;
            $json['transactionID'] = $response->txId;
            $json['status'] = $response->status;
            $json['message'] = $response->message;
            $json['authType'] = $response->authType;
            $json = json_encode($json);
            header("HTTP/1.1 403 Forbidden");
            header('Content-Type: application/json;charset=utf-8');
            echo $json;
            exit;

        } else {
            MoAuthUtilities::mo_add_loggers_for_failures( $response->message,'error');
            $json['username'] = $username;
            $json['status'] = 'ERROR';
            $json['message'] = 'Something went wrong, please contact your administrator.';
            $json = json_encode($json);
            header("HTTP/1.1 500 Internal Server Error");
            header('Content-Type: application/json;charset=utf-8');
            echo $json;
            exit;
        }
    }
}