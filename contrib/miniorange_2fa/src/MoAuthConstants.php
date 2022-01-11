<?php
/**
 * @file
 * Contains constants class.
 */
namespace Drupal\miniorange_2fa;
/**
 * @file
 * This class represents constants used throughout project.
 */

class MoAuthConstants {

    public static $PLUGIN_NAME                  = 'Drupal Two-Factor Plugin';
    public static $TRANSACTION_NAME             = 'Drupal Two-Factor Module';
    public static $PREMIUM_PLAN                 = 'drupal8_2fa_premium_plan';
    public static $ADD_USER_PLAN                = 'drupal8_2fa_add_user_plan';
    public static $RENEW_SUBSCRIPTION_PLAN      = 'drupal8_2fa_renew_subscription_plan';
    public static $WBSITE_SECURITY              = 'https://plugins.miniorange.com/drupal-web-security-pro';

    public static $DEFAULT_CUSTOMER_ID          = '16622';
    public static $DEFAULT_CUSTOMER_API_KEY     = 'XzjkmAaAOzmtJRmXddkXyhgDXnMCrdZz';

    public static $CUSTOMER_CHECK_API           = '/rest/customer/check-if-exists';
    public static $CUSTOMER_CREATE_API          = '/rest/customer/add';
    public static $CUSTOMER_GET_API             = '/rest/customer/key';
    public static $CUSTOMER_CHECK_LICENSE       = '/rest/customer/license';
    public static $SUPPORT_QUERY                = '/rest/customer/contact-us';

    public static $USERS_CREATE_API             = '/api/admin/users/create';
    public static $USERS_GET_API                = '/api/admin/users/get';
    public static $USERS_UPDATE_API             = '/api/admin/users/update';
    public static $USERS_SEARCH_API             = '/api/admin/users/search';

    public static $AUTH_CHALLENGE_API           = '/api/auth/challenge';
    public static $AUTH_VALIDATE_API            = '/api/auth/validate';
    public static $AUTH_STATUS_API              = '/api/auth/auth-status';
    public static $AUTH_REGISTER_API            = '/api/auth/register';
    public static $AUTH_REGISTRATION_STATUS_API = '/api/auth/registration-status';
    public static $AUTH_GET_GOOGLE_AUTH_API     = '/api/auth/google-auth-secret';
    public static $AUTH_GET_ALL_USER_API        = '/api/admin/users/getall';


    /**
    * Function that handles the custom organization name
    */
    public static function getBaseUrl() {
        $getBrandingName = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_custom_organization_name');
        return "https://" . $getBrandingName . ".xecurify.com/moas";
    }
}