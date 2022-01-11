<?php
namespace Drupal\miniorange_2fa\form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\UsersAPIHandler;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;

class configure_kba extends FormBase
{
    public function getFormId() {
        return 'miniorange_configure_kba';
    }
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array( 'miniorange_2fa/miniorange_2fa.admin', 'miniorange_2fa/miniorange_2fa.license' ),
            ),
        );

        $user              = User::load(\Drupal::currentUser()->id());
        $user_id           = $user->id();
        $utilities         = new MoAuthUtilities();
        $custom_attribute  = $utilities::get_users_custom_attribute($user_id);
        $user_email        = $custom_attribute[0]->miniorange_registered_email;

        $customer = new MiniorangeCustomerProfile();
        $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$GOOGLE_AUTHENTICATOR['code']);
        $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());

        $form['markup_top_2'] = array(
            '#markup' => '<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_2fa_container">'
        );

        /**
         * Create container to hold form elements.
         */
        $form['mo_configure_security_questions'] = array(
            '#type' => 'fieldset',
            '#title' => t('Configure Security Questions (KBA)'),
            '#attributes' => array( 'style' => 'padding:2% 2% 6%; margin-bottom:2%' ),
        );
        $form['mo_configure_security_questions']['markup_configure_kba_note'] = array(
            '#markup' => '<br><hr><br><div class="mo_2fa_highlight_background_note"><strong>'.t('You can customize the following things of the CONFIGURE SECURITY QUESTIONS (KBA) method:').'</strong><ul><li>'.t('Customize the set of questions. ( You can add your own set of questions )').'</li><li>'.t('Set number of questions to be asked while login/authentication'). '( <a href=" ' . MoAuthUtilities::get_mo_tab_url( 'SUPPORT' ) . ' ">'.t('Contact us').'</a>'.t(' for more details )').'</li><li>'.t('For customization goto').' <a href="' . MoAuthUtilities::get_mo_tab_url( 'LOGIN' ) . '">'.t('Login Settings').'</a> '.t('tab and navigate to').'<u>'.t('CUSTOMIZE KBA QUESTIONS').'</u>'.t( 'section.').'</li></ul></div>',
        );
        $form['mo_configure_security_questions']['mo2f_kbaquestion1'] = array(
            '#type' => 'select',
            '#title' => t('1. Question :'),
            '#attributes' => array( 'style' => 'width:70%; height:29px' ),
            '#options' => $utilities::mo_get_kba_questions('ONE' ),
            '#prefix' => '<br>',
        );

        $form['mo_configure_security_questions']['mo2f_kbaanswer1'] = array(
            '#type' => 'textfield',
            '#title' => t('Answer:'),
            '#attributes' => array( 'style' => 'width:70%', 'placeholder' => t('Enter your answer' ), ),
            '#required' => TRUE,
            '#suffix' => '</div><br>',
        );

        $form['mo_configure_security_questions']['mo2f_kbaquestion2'] = array(
            '#type' => 'select',
            '#attributes' => array( 'style' => 'width:70%; height:29px' ),
            '#title' => t('2. Question :'),
            '#options' => $utilities::mo_get_kba_questions('TWO' ),
        );

        $form['mo_configure_security_questions']['mo2f_kbaanswer2'] = array(
            '#type' => 'textfield',
            '#title' => t('Answer:'),
            '#attributes' => array( 'style' => 'width:70%', 'placeholder' => t('Enter your answer'), ),
            '#required' => TRUE,
            '#suffix' => '<br>',
        );

        $form['mo_configure_security_questions']['mo2f_kbaquestion3'] = array(
            '#type' => 'textfield',
            '#title' => t('3. Question:'),
            '#attributes' => array( 'style' => 'width:70%' , 'placeholder' => t('Enter your custom question here'), ),
            '#required' => TRUE,
        );

        $form['mo_configure_security_questions']['mo2f_kbaanswer3'] = array(
            '#type' => 'textfield',
            '#title' => t('Answer:'),
            '#attributes' => array( 'style' => 'width:70%', 'placeholder' => t('Enter your answer'), ),
            '#required' => TRUE,
            '#suffix' => '<br><br>',
        );

        $form['mo_configure_security_questions']['submit'] = array(
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => t('Configure KBA'),
        );

        $form['mo_configure_security_questions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#button_type' => 'danger',
            '#submit' => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
            '#limit_validation_errors'=>array(),
        );

        $utilities::miniOrange_advertise_network_security($form, $form_state);

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $form_state->setRebuild();
        $user             = User::load(\Drupal::currentUser()->id());
        $user_id          = $user->id();
        $utilities        = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);
        $user_email       = $custom_attribute[0]->miniorange_registered_email;

        $form_values = $form_state->getValues();

        $qa1 = array(
            "question" => $form_values['mo2f_kbaquestion1'],
            "answer"   => $form_values['mo2f_kbaanswer1'],
        );
        $qa2 = array(
            "question" => $form_values['mo2f_kbaquestion2'],
            "answer"   => $form_values['mo2f_kbaanswer2'],
        );
        $qa3 = array(
            "question" => $form_values['mo2f_kbaquestion3'],
            "answer"   => $form_values['mo2f_kbaanswer3'],
        );

        $kba = array( $qa1, $qa2, $qa3 );

        $customer         = new MiniorangeCustomerProfile();
        $miniorange_user  = new MiniorangeUser( $customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$KBA['code'] );
        $auth_api_handler = new AuthenticationAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
        $response         = $auth_api_handler->register( $miniorange_user, AuthenticationType::$KBA['code'], NULL, NULL, $kba );

        // Clear all the messages
        \Drupal::messenger()->deleteAll();
        // read API response
        if ( $response->status == 'SUCCESS' ) {
            $configured_methods = $utilities::mo_auth_get_configured_methods( $user_id );
            if ( !in_array( AuthenticationType::$KBA['code'], $configured_methods ) ) {
                array_push($configured_methods, AuthenticationType::$KBA['code']);
            }
            $config_methods   = implode(', ',$configured_methods);
            $user_api_handler = new UsersAPIHandler( $customer->getCustomerID(), $customer->getAPIKey() );
            $response = $user_api_handler->update( $miniorange_user );
            if ( $response->status == 'SUCCESS' ) {
                // Save User
                $user_id   = $user->id();
                $available = $utilities::check_for_userID( $user_id );
                $database  = \Drupal::database();
                if( $available == TRUE ) {
                    $database->update('UserAuthenticationType')->fields(['activated_auth_methods'=> AuthenticationType::$KBA['code']])->condition('uid', $user_id,'=')->execute();
                    $database->update('UserAuthenticationType')->fields(['configured_auth_methods'=> $config_methods])->condition('uid', $user_id,'=')->execute();
                }else {
                    echo t("error while saving the authentication method.");exit;
                }

                $message = t('KBA Authentication configured successfully.');
                MoAuthUtilities::show_error_or_success_message($message , 'status');
                return;
            }
        } elseif ( $response->status == 'FAILED' ) {
            $message = t('An error occurred while configuring KBA Authentication. Please try again.');
            MoAuthUtilities::show_error_or_success_message($message , 'error');
            return;
        }
        $message = t('An error occurred while processing your request. Please try again.');
        MoAuthUtilities::show_error_or_success_message($message , 'error');
        return;
    }
}