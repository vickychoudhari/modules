<?php
/**
 * @file
 * Contains support form for miniOrange 2FA Login Module.
 */
namespace Drupal\miniorange_2fa\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;
use Drupal\user\Entity\User;

/**
 *  Showing Support form info.
 */
class MoAuthSupport extends FormBase
{
    public function getFormId() {
        return 'miniorange_2fa_support';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_2fa/miniorange_2fa.license",
                    "miniorange_2fa/miniorange_2fa.admin",
                )
            ),
        );

        $email       = ''; $phone = '';
        $user        = User::load(\Drupal::currentUser()->id());
        $user_id     = $user->id();
        $phoneNumber = MoAuthUtilities::getUserPhoneNumber($user_id);
        if ( MoAuthUtilities::isCustomerRegistered() ) {
            $customer = new MiniorangeCustomerProfile();
            $email    = $customer->getRegisteredEmail();
            $phone    = $phoneNumber;
        }
        $form['markup_1'] = array(
            '#markup' => t('<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_2fa_container">'),
        );

        $form['mo_auth_vertical_tabs'] = array(
            '#type' => 'vertical_tabs',
            '#default_tab' => 'edit-publication',
        );

        /**
         * Support form
         */
        $form['miniorange_support'] = array (
            '#type' => 'details',
            '#title' => t('Support'),
            '#group' => 'mo_auth_vertical_tabs',
        );
        $form['miniorange_support']['markup_1'] = array(
            '#markup' => t('<h3>Support</h3><hr><p class="mo_2fa_highlight_background_note">Need any help? We can help you with configuring miniOrange 2FA module on your site. Just send us a query and we will get back to you soon.</p>'),
        );
        $form['miniorange_support']['mo_auth_support_email_address'] = array(
            '#type' => 'email',
            '#title' => t('Email'). '<span style="color: red">*</span>',
            '#default_value' => $email,
            '#attributes' => array('placeholder' => t('Enter your email'), 'style' => 'width:99%'),
        );
        $form['miniorange_support']['mo_auth_support_phone_number'] = array(
            '#type' => 'textfield',
            '#title' => t('Phone'),
            '#default_value' => $phone,
            '#id'    =>'query_phone',
            '#description'=> '<strong>'.t('Note:').'</strong>'. t('Enter phone number with country code Eg. +00xxxxxxxxxx'),
            '#attributes' => array('placeholder' => t('Enter phone number with country code Eg. +00xxxxxxxxxx'), 'style' => 'width:99%;','class' => array('query_phone',)),
        );
        $form['miniorange_support']['mo_auth_support_query'] = array(
            '#type' => 'textarea',
            '#id'   =>"mo_auth_new_2fa_text_area",
            '#title' => t('Query').' <span style="color: red">*</span>',
            '#attributes' => array('placeholder' => t('Describe your query here!'), 'style' => 'width:99%'),
            '#suffix' => '<br>',
        );
        $form['miniorange_support']['actions']['submit'] = array(
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => t('Submit query'),
        );
        $form['miniorange_support']['markup_support_note'] = array(
            '#markup' => '<div><br/><br/>'.t('If you want custom features in the module, just drop an email to').' <a href="mailto:info@xecurify.com">info@xecurify.com</a>'.t(' or ').' <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></div><br><hr><br>',
        );


        /**
         * Request demo form
         */
        $form['miniorange_demo_request'] = array (
            '#type' => 'details',
            '#title' => t('Request demo'),
            '#group' => 'mo_auth_vertical_tabs',
        );
        $form['miniorange_demo_request']['markup_demo_request'] = array(
            '#markup' => '<h3>'.t('Request Demo of Premium features').'</h3><hr><p class="mo_2fa_highlight_background_note">'.t('Want to know about how the licensed module works? Let us know and we will arrange a demo for you.').'</p>',
        );
        $form['miniorange_demo_request']['mo_auth_demo_email_address'] = array(
            '#type' => 'email',
            '#title' => t('Email').' <span style="color: red">*</span>',
            '#default_value' => $email,
            '#attributes' => array('placeholder' => t('Enter your email'), 'style' => 'width:99%'),
        );
        $form['miniorange_demo_request']['mo_auth_demo_plan'] = array(
            '#type' => 'select',
            '#title' => t('Plan'),
            '#options' => array(
                'Drupal 8 2FA Premium' => t('Drupal 8 2FA Premium'),
                'Drupal 8 2FA Premium + Website Security Premium' => t('Drupal 8 2FA Premium + Website Security Premium'),
                'Not Sure' => t('Not Sure, Need help for selecting plan.'),
            ),
            '#attributes' => array('style' => 'width:99%;height:30px'),
        );
        $form['miniorange_demo_request']['mo_auth_demo_description'] = array(
            '#type' => 'textarea',
            '#title' => t('Description').' <span style="color: red">*</span>',
            '#attributes' => array('placeholder' => t('Describe your use case here!'), 'style' => 'width:99%'),
            '#suffix' => '<br>',
        );
        $form['miniorange_demo_request']['actions']['submit'] = array(
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => t('Send Request'),
        );
        $form['miniorange_demo_request']['markup_demo_note'] = array(
            '#markup' => '<div><br/><br/>'.t('If you are not sure with which plan you should go with, get in touch with us on').' <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a>'. t(' and we will assist you with the suitable plan.').'</div><br><hr><br>',
        );


        /**
         * New 2fa feature request form
         */
        $form['miniorange_feature_request'] = array (
            '#type' => 'details',
            '#title' => t('New Feature Request'),
            '#group' => 'mo_auth_vertical_tabs',
        );
        $form['miniorange_feature_request']['markup_1'] = array(
            '#markup' => t('<h3>New 2FA feature/method request</h3><hr><p class="mo_2fa_highlight_background_note">Need new 2FA method? Just send us a requirement so we can help you.'),
        );
        $form['miniorange_feature_request']['mo_auth_feature_email_address'] = array(
            '#type' => 'email',
            '#title' => t('Email').' <span style="color: red">*</span>',
            '#default_value' => $email,
            '#attributes' => array('placeholder' => t('Enter your email'), 'style' => 'width:99%'),
        );
        $form['miniorange_feature_request']['mo_auth_feature_phone_number'] = array(
            '#type' => 'textfield',
            '#title' => t('Phone'),
            '#default_value' => $phone,
            '#id'    =>'query_phone',
            '#description'=> '<strong>'.t('Note:').'</strong>'.t('Enter phone number with country code Eg. +00xxxxxxxxxx'),
            '#attributes' => array('placeholder' => t('Enter phone number with country code Eg. +00xxxxxxxxxx'), 'style' => 'width:99%','class' => array('query_phone',) ),
        );
        $form['miniorange_feature_request']['mo_auth_feature_query'] = array(
            '#type' => 'textarea',
            '#id'   =>"mo_auth_new_2fa_text_area",
            '#title' => t('Description').' <span style="color: red">*</span>',
            '#attributes' => array('placeholder' => t('Describe your requirement here!'), 'style' => 'width:99%'),
            '#suffix' => '<br>',
        );
        $form['miniorange_feature_request']['actions']['submit'] = array(
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => t('Submit'),
        );
        $form['miniorange_feature_request']['markup_feature_request_note'] = array(
            '#markup' => '<div><br/><br/>'.t('For any other queries, reach out to us using the support form or just drop an email to '). '<a href="mailto:info@xecurify.com">info@xecurify.com</a>'.t(' or').'  <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></div><br><hr><br>',
        );
        $form['markup_support_1'] = array(
            '#markup' =>'<br><br><br><br></div><div class="mo_2fa_table_layout mo_auth_container_2" id="ma_saml_support_query">
                <h3><b>'. t('Know more about module:').'</b></h3><div>'.t('Need any help? We can help you with configuring miniOrange 2FA module on your site. Just send us a query and we will get back to you soon.').'<br /></div><br>',
        );

        $form['markup_support_2'] = array(
            '#markup' => '<div>'.t('Click').' <a target="_blank" href="https://plugins.miniorange.com/drupal-two-factor-authentication-2fa">'.t('here').'</a> '.t('to know more about Drupal 8 2FA module.').'<br>
                          '.t('Click').' <a target="_blank" href="https://plugins.miniorange.com/drupal-2fa-setup-guides">'.t('here').'</a> '.t('to see setup guides for various 2fa methods in this module.').'<br><br>
                          '.t('Click').' <a target="_blank" href="https://plugins.miniorange.com/drupal">'.t('here').'</a> '.t('to see all other security related products which we provide for drupal.').'<br><br><br>
                          
                          <div class="mo2f_text_center"><b></b>
                          <a class="mo_saml_btn mo_saml_btn-primary-faq mo_saml_btn-large mo_auth_button_left" href="https://faq.miniorange.com/kb/drupal/two-factor-authentication-drupal/" target="_blank">FAQs</a>
                          <b></b><a class="mo_saml_btn mo_saml_btn-primary-faq mo_saml_btn-large mo_auth_button_right" href="https://forum.miniorange.com/" target="_blank">'.t('Ask questions on forum').'</a></div></div><br>
                          
                          </div><br><br>',
        );

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $form_values = $form_state->getValues();
        if( $form_values['mo_auth_vertical_tabs__active_tab'] === 'edit-miniorange-support' ) {
            if( !\Drupal::service('email.validator')->isValid( $form_values['mo_auth_support_email_address'] ) ) {
                $form_state->setErrorByName('mo_auth_support_email_address', $this->t('The email address is not valid.'));
            }
            if( empty( $form_values['mo_auth_support_query'] ) ) {
                $form_state->setErrorByName('mo_auth_support_query', $this->t('The <b>Query</b> fields is mandatory'));
            }
        } elseif( $form_values['mo_auth_vertical_tabs__active_tab'] === 'edit-miniorange-demo-request' ) {
            if( !\Drupal::service('email.validator')->isValid( $form_values['mo_auth_demo_email_address'] ) ) {
                $form_state->setErrorByName('mo_auth_demo_email_address', $this->t('The email address is not valid.'));
            }
            if( empty( $form_values['mo_auth_demo_description'] ) ) {
                $form_state->setErrorByName('mo_auth_demo_description', $this->t('The <b>Description</b> fields is mandatory'));
            }
        } elseif( $form_values['mo_auth_vertical_tabs__active_tab'] === 'edit-miniorange-feature-request' ) {
            if( !\Drupal::service('email.validator')->isValid( $form_values['mo_auth_feature_email_address'] ) ) {
                $form_state->setErrorByName('mo_auth_feature_email_address', $this->t('The email address is not valid.'));
            }
            if( empty( $form_values['mo_auth_feature_query'] ) ) {
                $form_state->setErrorByName('mo_auth_feature_query', $this->t('The <b>Description</b> fields is mandatory'));
            }
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $form_values = $form_state->getValues();
        $email = ''; $phone = ''; $query = ''; $query_type = '';
        if( $form_values['mo_auth_vertical_tabs__active_tab'] === 'edit-miniorange-support' ) {
            $email = $form_values['mo_auth_support_email_address'];
            $phone = $form_values['mo_auth_support_phone_number'];
            $query = $form_values['mo_auth_support_query'];
            $query_type = 'Support';

        }elseif ( $form_values['mo_auth_vertical_tabs__active_tab'] === 'edit-miniorange-demo-request' ) {
            $email = $form_values['mo_auth_demo_email_address'];
            $phone = $form_values['mo_auth_demo_plan'];
            $query = $form_values['mo_auth_demo_description'];
            $query_type = 'Demo Request';

        }elseif ( $form_values['mo_auth_vertical_tabs__active_tab'] === 'edit-miniorange-feature-request' ) {
            $email = $form_values['mo_auth_feature_email_address'];
            $phone = $form_values['mo_auth_feature_phone_number'];
            $query = $form_values['mo_auth_feature_query'];
            $query_type = 'New Feature Request';
        }
        MoAuthUtilities::send_support_query( $email, $phone, $query, $query_type );
    }
}