<?php
/**
 * @file
 * Contains miniOrange Support class.
 */
namespace Drupal\miniorange_2fa;
/**
 * This class represents support information for customer.
 */
class Miniorange2FASupport {
    public $email;
    public $phone;
    public $query;
    public $query_type;

    /**
    * Constructor.
    */
    public function __construct( $email, $phone, $query, $query_type ) {
    $this->email      = $email;
    $this->phone      = $phone;
    $this->query      = $query;
    $this->query_type = $query_type;
    }

    /**
    * Send support query.
    */
    public function sendSupportQuery() {
        if ( !MoAuthUtilities::isCurlInstalled() ) {
          return (object)(array (
            "status" => 'CURL_ERROR',
            "message" => 'PHP cURL extension is not installed or disabled.'
          ));
        }

        $modules_info = \Drupal::service('extension.list.module')->getExtensionInfo('miniorange_2fa');
        $modules_version = $modules_info['version'];

        if( $this->query_type === 'Demo Request' ){
            $this->query = 'Demo request for ' . $this->phone . ' .<br> '. $this->query;
        }

        $this->query = '[Drupal ' . MoAuthUtilities::mo_get_drupal_core_version() . ' 2FA Module ' .$this->query_type. ' | ' .$modules_version. '] ' . $this->query;

        $fields = array (
          'company' => $_SERVER['SERVER_NAME'],
          'email'   => $this->email,
          'phone'   => $this->query_type != 'Demo Request' ? $this->phone : '',
          'ccEmail' => 'drupalsupport@xecurify.com',
          'query'   => $this->query
        );
        $field_string = json_encode( $fields );

        $url = MoAuthConstants::getBaseUrl() . MoAuthConstants::$SUPPORT_QUERY;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array (
          'Content-Type: application/json',
          'charset: UTF-8',
          'Authorization: Basic'
        ));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec( $ch );
        if ( curl_errno($ch ) ) {
          \Drupal::logger('miniorange_2fa')->error("cURL Error at <strong>sendSupportQuery</strong> function of <strong>mo_auth_support.php</strong> file: " . curl_error( $ch ));
          return FALSE;
        }
        curl_close($ch);
        return TRUE;
    }
}
