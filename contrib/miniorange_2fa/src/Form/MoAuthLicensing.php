<?php

/**
 * @file
 * Contains support form for miniOrange 2FA Login Module.
 */
namespace Drupal\miniorange_2fa\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MoAuthConstants;
use Drupal\miniorange_2fa\MoAuthUtilities;

/* Showing Licensing form info. */

class MoAuthLicensing extends FormBase
{
    public function getFormId() {
        return 'miniorange_2fa_licensing';
    }
    public function buildForm(array $form, FormStateInterface $form_state) {

        $user_email = \Drupal::config('miniorange_2fa.settings')->get('mo_auth_customer_admin_email');

        $mo_Premium_Plan_URL = MoAuthConstants::getBaseUrl() . '/login?username='.$user_email.'&redirectUrl='. MoAuthConstants::getBaseUrl() . '/initializepayment&requestOrigin='. MoAuthConstants::$PREMIUM_PLAN;

        $mo_db_values = MoAuthUtilities::miniOrange_set_get_configurations( ['mo_auth_2fa_license_type',], 'GET' );
        $license_type = $mo_db_values['mo_auth_2fa_license_type'];
        $smsText = $license_type == "DEMO" ? 'style="visibility : hidden;"' : '';

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_2fa/miniorange_2fa.admin",

                )
            ),
        );

        $form['header_top_style_2'] = array(
            '#markup' => t('<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout"><div class="mo_otp_verification_table_layout_1"><div class="mo_otp_verification_table_layout">
                            <br><h2>&emsp; Upgrade Plans</h2><hr>')
        );

        $form['markup_free'] = array(
            '#markup' => t('<html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <!-- Main Style -->
            </head>
            <body>
            <!-- Pricing Table Section -->
            <section id="mo2f-pricing-table">
                <div class="container_1">
                    <div class="row">
                        <div class="mo2f-pricing">
                            <div>
                                <div class="mo2f-pricing-table class_2fa_inline">
                                    <div class="mo2f-pricing-header">
                                        <p class="mo2f-pricing-title">2FA Free Module<br><br><span>For 1 user Forever</span></p>
                                        <p class="mo2f-pricing-rate"><sup>$</sup> 0</sup></p>
                                        <div class="filler-2fa-class"></div>

                                    </div>
                                    <div class="pricing-2fa-list">
                                        <ul>
                                            <li>All Authentication Methods*</li>
                                            <li>Supports all the languages</li>
                                            <li>Backup security questions (KBA)</li>
                                            <li>Add your own security questions</li>
                                            <li>Customize number of KBA to be asked while login</li>
                                            <li>Enable login with email address</li>
                                            <li>Enable login with phone address</li>
                                            <li>Redirect URL (After login)</li>
                                            <li>Override login form username title and description</li>
                                            <li>Change app name in Google Authenticator app</li>
                                            <li>Customize Email Templates</li>
                                            <li>Customize SMS Templates</li>
                                            <li>Customize OTP length and validity</li>
                                            <li>Backdoor URL (incase you get locked out)</li>
                                            <li>Remember Device (available soon)</li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li>Basic Email Support</li>
                                            <li><a target="_blank" href="https://miniorange.com/contact">Contact us</a></li>
                                        </ul>
                                    </div>
                                </div>

                            <div class="mo2f-pricing-table class_2fa_inline">
                                <div class="mo2f-pricing-header">
                                   <p class="mo2f-pricing-title">2FA Premium Module<br><br>
                                    <span>For 1+ users, Setup and Custom Work</span></p>
                                    <p class="mo2f-pricing-rate"></p>
                                     <div class="filler-2fa-class-mo-gateway"></div>
                                      <a target="_blank" class="mo2f-pricing-button" href = " ' . $mo_Premium_Plan_URL . ' ">Check Pricing / Upgrade</a>
                                         <br/> '.  '<a '. $smsText .' target="_blank" class="mo2f-pricing-button" href = " ' . MoAuthUtilities::get_mo_tab_url('SUPPORT') . ' ">Contact us to buy SMS transactions</a> '  .'

                                </div>
                                <div class="pricing-2fa-list">
                                    <ul>
                                        <li>All Authentication Methods*</li>
                                        <li>Support for Headless/Decoupled setup</li>
                                        <li>Supports all the languages</li>
                                        <li>Backup security questions (KBA)</li>
                                        <li>Add your own security questions</li>
                                        <li>Customize number of KBA to be asked while login</li>
                                        <li>Enable login with email address</li>
                                        <li>Enable login with phone address</li>
                                        <li>Redirect URL (After login)</li>
                                        <li>Override login form username title and description</li>
                                        <li>Change app name in Google authenticator app</li>
                                        <li>Customize Email Templates</li>
                                        <li>Customize SMS Templates</li>
                                        <li>Customize OTP length and validity</li>
                                        <li>Backdoor URL (incase you get locked out)</li>
                                        <li>Remember Device (available soon)</li>
                                        <li>Enable Role based 2FA</li>
                                        <li>Enable Domain based 2FA</li>
                                        <li>Opt-in and opt-out from 2FA</li>
                                        <li>2FA reset or reconfigure</li>
                                        <li>Enforce 2FA registration for users</li>
                                        <li>Select 2FA methods to be configure by end users</li>
                                        <li>Login with 2nd Factor only (No password required)</li>
                                        <li>IP specific 2FA (Whitelisting IP Address)</li>
                                        <li>End to End 2FA Integration**</li>
                                        <li></li>
                                        <li>Option to Choose Premium Support</li>
                                        <li><a target="_blank" href="https://miniorange.com/contact">Contact us</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mo2f-pricing-table class_2fa_inline">
                                    <div class="mo2f-pricing-header">
                                   <p class="mo2f-pricing-title" style="margin-bottom: -17%">2FA Premium Module + Website Security Premium module</p>
                                    <p class="mo2f-pricing-rate"></p>
                                     <p class="mo2f-pricing-title">+<br><br><a target="_blank" href="https://plugins.miniorange.com/drupal-web-security-pro" style="color: white; text-decoration: none;">[50% off on website security]</a></p>
                                         <a class="mo2f-pricing-button" href = " ' . MoAuthUtilities::get_mo_tab_url('SUPPORT') . ' ">Contact us for more info</a>
                                     
                                </div>
                                    <div class="pricing-2fa-list">
                                        <ul>
                                            <li>2FA Premium Module</li>
                                            <li>+</li>
                                            <li>Website Security Premium Module</li>
                                            <li><a target="_blank" href=" ' . MoAuthConstants::$WBSITE_SECURITY . ' "  >Features of Website Security</a></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li></li>
                                            <li>Option to Choose Premium Support</li>
                                            <li><a target="_blank" href="https://miniorange.com/contact">Contact us</a></li>
                                        </ul>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <!-- Pricing Table Section End -->
    </br>
    </body>
    </html>'),
     );

    $form['hello1'] = [ '#type' => 'html_tag', '#tag' => 'script', '#attributes'=> ["src"=>"https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"]];

        $form['hello'] = [ '#type' => 'html_tag', '#tag' => 'script', '#value' => $this ->t('
            jQuery(document).ready(function($){
                  $($("p.mo2f-pricing-rate")[1]).append($(".js-form-item-mo-pricing-dropdown-premium1"));
                $($("p.mo2f-pricing-rate")[2]).append($(".js-form-item-mo-pricing-dropdown-premium2"));
            });

        '), ];

        $form['mo_pricing_dropdown_premium1'] = [
            '#type' => 'select',
            '#id'   =>'mo_2fa_pricing_dropdown_premium',
            '#title' => t("<div class='mo_2fa_pricing_dropdown_premium'>Yearly Subscription Fees</div>"),
            '#options' => array(
                '10' => t('Upto 5 users - $20 / year'),
                '20' => t('Upto 10 users - $40 / year'),
                '30' => t('Upto 20 users - $65 / year'),
                '40' => t('Upto 30 users - $90 / year'),
                '50' => t('Upto 40 users - $115 / year'),
                '60' => t('Upto 50 users - $140 / year'),
                '70' => t('Upto 60 users - $165 / year'),
                '80' => t('Upto 70 users - $190 / year'),
                '90' => t('Upto 80 users - $215 / year'),
                '100' => t('Upto 90 users - $240 / year'),
                '110' => t('Upto 100 users - $265 / year'),
                '120' => t('Upto 150 users - $295 / year'),
                '130' => t('Upto 200 users - $325 / year'),
                '140' => t('Upto 250 users - $355 / year'),
                '150' => t('Upto 300 users - $385 / year'),
                '160' => t('Upto 350 users - $415 / year'),
                '170' => t('Upto 400 users - $445 / year'),
                '180' => t('Upto 450 users - $475 / year'),
                '190' => t('Upto 500 users - $505 / year'),
                '200' => t('Upto 600 users - $540 / year'),
                '210' => t('Upto 700 users - $575 / year'),
                '220' => t('Upto 800 users - $610 / year'),
                '230' => t('Upto 900 users - $645 / year'),
                '240' => t('Upto 1000 users - $680 / year'),
                '250' => t('Upto 2000 users - $730 / year'),
                '260' => t('Upto 3000 users - $780 / year'),
                '270' => t('Upto 4000 users - $830 / year'),
                '290' => t('Upto 5000 users - $880 / year'),
                '320' => t('More Than 5000 users - Contact Us'),
            ),
            "#attributes" => array( 'style'=> 'width:80% !important; height:30px !important; margin-right: 9% !important;' ),
        ];

        $form['mo_pricing_dropdown_premium2'] = [
            '#type' => 'select',
            '#id'   =>'mo_2fa_pricing_dropdown_premium',
            '#title' => t("<div class='mo_2fa_pricing_dropdown_premium'>Yearly Subscription Fees</div>"),
            '#options' => array(
                '10' => t('Upto 5 users - $20 / year'),
                '20' => t('Upto 10 users - $40 / year'),
                '30' => t('Upto 20 users - $65 / year'),
                '40' => t('Upto 30 users - $90 / year'),
                '50' => t('Upto 40 users - $115 / year'),
                '60' => t('Upto 50 users - $140 / year'),
                '70' => t('Upto 60 users - $165 / year'),
                '80' => t('Upto 70 users - $190 / year'),
                '90' => t('Upto 80 users - $215 / year'),
                '100' => t('Upto 90 users - $240 / year'),
                '110' => t('Upto 100 users - $265 / year'),
                '120' => t('Upto 150 users - $295 / year'),
                '130' => t('Upto 200 users - $325 / year'),
                '140' => t('Upto 250 users - $355 / year'),
                '150' => t('Upto 300 users - $385 / year'),
                '160' => t('Upto 350 users - $415 / year'),
                '170' => t('Upto 400 users - $445 / year'),
                '180' => t('Upto 450 users - $475 / year'),
                '190' => t('Upto 500 users - $505 / year'),
                '200' => t('Upto 600 users - $540 / year'),
                '210' => t('Upto 700 users - $575 / year'),
                '220' => t('Upto 800 users - $610 / year'),
                '230' => t('Upto 900 users - $645 / year'),
                '240' => t('Upto 1000 users - $680 / year'),
                '250' => t('Upto 2000 users - $730 / year'),
                '260' => t('Upto 3000 users - $780 / year'),
                '270' => t('Upto 4000 users - $830 / year'),
                '290' => t('Upto 5000 users - $880 / year'),
                '320' => t('More Than 5000 users - Contact Us'),
            ),
            "#attributes" => array( 'style'=> 'width:80% !important; height:30px !important; margin-right: 9% !important;' ),
        ];

       $support_tab_url = MoAuthUtilities::get_mo_tab_url( 'SUPPORT' );
       $disclaimer = t('<div style="margin: 0% 4% 3% 1.5%; text-align: justify;"><h3>Steps to upgrade to premium module -</h3>
            <ol>
                <li>Click on the <strong>Click here to upgrade</strong> button, you will be redirected to miniOrange Login Console. Login with the account created with us. After that you will be redirected to payment page.</li>
                <li>Enter your card details and complete the payment. On successful payment completion, goto <a href=" ' . MoAuthUtilities::get_mo_tab_url( 'CUSTOMER_SETUP' ) . ' ">Register/Login</a> tab and click on <strong>Check License</strong> button.</li>
            </ol><br>

            <h4>You can mail us at <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a> or submit query form <a href="'. $support_tab_url .'" >support form</a> form in any of the following scenarios:</h4>
            <ul>
                <li>If you dont find the number of users in the dropdown which you are looking for.</li>
                <li>If you dont find the feature which you are looking for.</li>
                <li>And for any other queries.</li><br>
            </ul>

            <h3>Return Policy - </h3>
                 At miniOrange, we want to ensure you are 100% happy with your purchase. If the module you purchased is not working as advertised and you\'ve attempted to resolve any issues with our support team, which couldn\'t get resolved, we will refund the whole amount given that you have a raised a refund request within the first 10 days of the purchase.
                 Please email us at <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a> for any queries regarding the return policy.<br><br>

            <h3>* Few authentication methods need credits like SMS transactions, email transactions etc.</h3><br>

            <h3>** End to End 2FA Integration (additional charges may applied)</h3>
                 We will setup a Conference Call / Gotomeeting and do end to end configuration for you to setup Drupal 2FA module.
                 We provide services to do the configuration on your behalf.
                 If you have any doubts regarding the upgrade plans, you can mail us at <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a> or submit a query using the <a href="'. $support_tab_url .'">support form</a>.
         </div>');

       $form['header']['#markup'] =  $disclaimer;

       return $form;
    }
    public function submitForm(array &$form, FormStateInterface $form_state) {

    }
}
