<?php

namespace Drupal\miniorange_2fa\Form;

/**
 * @file
 * Email verification functions.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_2fa\MiniorangeUser;
use Drupal\miniorange_2fa\MoAuthUtilities;
use Drupal\miniorange_2fa\AuthenticationType;
use Drupal\miniorange_2fa\AuthenticationAPIHandler;
use Drupal\miniorange_2fa\MiniorangeCustomerProfile;

/**
 * Menu callback for email verification.
 */
class test_kba_authentication extends FormBase
{
    public function getFormId() {
        return 'miniorange_kba_autentication';
    }
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['markup_top_2'] = array(
            '#markup' => '<div class="mo_2fa_table_layout_1"><div class="mo_2fa_table_layout mo_2fa_container">'
        );
        $form['markup_library'] = array(
            '#attached' => array(
                'library' => ['miniorange_2fa/miniorange_2fa.admin', 'miniorange_2fa/miniorange_2fa.license'],
        ),);

        $user = User::load(\Drupal::currentUser()->id());
        $user_id = $user->id();
        $utilities = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);

        $user_email = $custom_attribute[0]->miniorange_registered_email;;
        $input = $form_state->getUserInput();
        $questions = NULL;
        $count = 0;
        $txId = '';

        if ( array_key_exists('txId', $input ) === FALSE ) {
            $customer = new MiniorangeCustomerProfile();
            $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$KBA['code']);
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->challenge($miniorange_user);
            if ( $response->status == 'SUCCESS' ) {
                $questions = $response->questions;
                $count = count( $response->questions );
                $txId = $response->txId;
            } else {
                $message = t('An error occurred while processing your request. Please try again.' );
                $utilities::show_error_or_success_message($message , 'error' );
            }
        } else {
            $count = $input['question_count'];
            $questions = array();
            for ( $i = 1; $i <= $count; $i++ ) {
                $ques = isset( $input['mo2f_kbaquestion' . $i] ) ? $input['mo2f_kbaquestion' . $i] : '';
                $ans  = isset( $input['mo2f_kbaanswer' . $i] ) ? $input['mo2f_kbaanswer' . $i] : '';
                $qa   = Array();
                $qa   = Array( 'question'=>$ques,'answer'=>$ans );
                array_push($questions, (object)$qa );
            }
        }

        /**
         * Create container to hold form elements.
         */
        $form['mo_test_security_questions'] = array(
            '#type' => 'fieldset',
            '#title' => t('Test Security Questions (KBA)'),
            '#attributes' => array( 'style' => 'padding:2% 2% 6%; margin-bottom:7%' ),
        );

        $form['mo_test_security_questions']['#markup'] = t('<br><hr><br><div class="mo_2fa_highlight_background_note"><strong>'.t('Note:').'</strong> '.t('Please answer the following questions to complete the test.').'</div><br>');

        $i = 0;
        foreach ( $questions as $ques ) {
            $i++;
            $form['mo_test_security_questions']['mo2f_kbaanswer'.$i] = array(
                '#type' => 'textfield',
                '#title' => t( $ques->question ),
                '#size' => 49,
                '#suffix' => '<br>',
                '#attributes' => array(
                    'placeholder' => t('Enter your answer here'),
                    'style' => 'margin-top:1%; width:70%'
                ),
                '#required' => TRUE,
            );
            $form['mo_test_security_questions']['mo2f_kbaquestion'.$i] = array(
                '#type' => 'hidden',
                '#value' => $ques->question,
            );
        }

        $form['mo_test_security_questions']['txId'] = array(
            '#type' => 'hidden',
            '#value' => $txId,
        );
        $form['mo_test_security_questions']['question_count'] = array(
            '#type' => 'hidden',
            '#value' => $count,
        );

        $form['mo_test_security_questions']['actions']['submit'] = array(
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => t('Verify'),
            '#prefix' => '<br>',
        );
        $form['mo_test_security_questions']['actions']['cancel'] = array(
            '#type' => 'submit',
            '#value' => t('Cancel Test'),
            '#button_type' => 'danger',
            '#submit' => array('\Drupal\miniorange_2fa\MoAuthUtilities::mo_handle_form_cancel'),
            '#limit_validation_errors'=>array(),
        );

        $form['main_layout_div_end'] = array(
            '#markup' => '</div>',
        );

        $utilities::miniOrange_advertise_network_security( $form, $form_state);

        return $form;
    }

    /**
     * Form submit handler for email verify.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $form_state->setRebuild();
        $input = $form_state->getUserInput();
        $txId  = $input['txId'];
        $user  = User::load(\Drupal::currentUser()->id());
        $user_id = $user->id();
        $utilities = new MoAuthUtilities();
        $custom_attribute = $utilities::get_users_custom_attribute($user_id);

        $user_email = $custom_attribute[0]->miniorange_registered_email;
        $count = $input['question_count'];
        $kba = array();
        for ( $i = 1; $i <= $count; $i++ ) {
            $ques = $input['mo2f_kbaquestion' . $i];
            $ans  = $input['mo2f_kbaanswer' . $i];
            $qa = array (
                "question" => $ques,
                "answer"   => $ans,
            );
            array_push($kba, $qa );
        }
        if ( count( $kba ) > 0 ) {
            $customer = new MiniorangeCustomerProfile();
            $miniorange_user = new MiniorangeUser($customer->getCustomerID(), $user_email, NULL, NULL, AuthenticationType::$KBA['code']);
            $auth_api_handler = new AuthenticationAPIHandler($customer->getCustomerID(), $customer->getAPIKey());
            $response = $auth_api_handler->validate($miniorange_user, $txId, NULL, $kba);
            // Clear all the messages
            \Drupal::messenger()->deleteAll();
            // read API response
            if ( $response->status == 'SUCCESS' ) {
                $message = t('You have successfully completed the test.');
                $utilities::show_error_or_success_message($message , 'status');
                return;
            } elseif ( $response->status == 'FAILED' ) {
                \Drupal::messenger()->addMessage(t('The answers you have entered are incorrect. Please try again.'), 'error');
                return;
            }
        }
        $message = t('An error occurred while processing your request. Please try again.');
        $utilities::show_error_or_success_message($message , 'error');
    }
}