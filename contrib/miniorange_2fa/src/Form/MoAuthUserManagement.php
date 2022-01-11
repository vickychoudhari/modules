<?php
/**
 * @file
 * Contains support form for miniOrange 2FA Login Module.
 */
namespace Drupal\miniorange_2fa\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_2fa\MoAuthUtilities;

 /* Showing LoginSetting form info. */

class MoAuthUserManagement extends FormBase {
    public function getFormId() {
        return 'miniorange_2fa_user_management';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        global $base_url ;
        $utilities = new MoAuthUtilities();

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
        $form['mo_user_management'] = array(
            '#type' => 'fieldset',
            '#title' => t('User Management'),
            '#attributes' => array( 'style' => 'padding:0% 2% 17%' )
        );

        $form['mo_user_management']['mo_auth_username_to_reset_2fa'] = array(
            '#type' => 'textfield',
            '#title' => t("Enter Username"),
            '#attributes' => array('placeholder' => t('Enter Username')),
            '#description' => t("<strong>Note: </strong>Enter the username for which you want to reset the 2FA."),
            '#disabled' => $disabled,
            '#prefix' => '<br><br><hr><br><div class="mo_2fa_highlight_background_note"><strong>'.t('Note:').' </strong>'.t('If you want to reset the 2FA for any user, you can do it from this section.').' <strong>'.t('If you reset the 2FA for any user, then that user has to go through the inline registration process to setup the 2FA again.').'</strong></div>',
            '#suffix' => '<br>',
        );

        $form['mo_user_management']['Submit_UserManagement_form'] = array(
            '#type'        => 'submit',
            '#button_type' => 'primary',
            '#value'       => t('Reset 2FA'),
            '#disabled'    => $disabled,
            '#suffix'      => '<br><br><br></div>'
        );

        $utilities::miniOrange_advertise_network_security( $form, $form_state);

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $form_values = $form_state->getValues();
        $username = $form_values['mo_auth_username_to_reset_2fa'];

        $user = user_load_by_name( $username );

        if ( $user === FALSE ) {
            \Drupal::messenger()->addError(t("User (<strong>" . $username . "</strong>) not found." ));
            return;
        }

        $query = \Drupal::database()->delete('UserAuthenticationType');
        $query->condition('uid', $user->id(), '=');
        $query->execute();

        \Drupal::messenger()->addStatus(t("You have reset the 2FA for <strong>%username</strong> successfully.",array('%username'=>$username)));
        return;
    }
}
