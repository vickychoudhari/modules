<?php
/**
 * @file
 * Contains Drupal\ajax\AjaxForm
 */

namespace Drupal\ajax\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class AjaxForm extends FormBase {

  public function getFormId() {
    return 'ajax_example_form';
  }
public function buildForm(array $form, FormStateInterface $form_state) {
   $form['user_email'] = array(
      '#type' => 'textfield',
      '#title' => 'User or Email',
      '#description' => 'Please enter in a user or email',
      '#prefix' => '<div id="user-email-result"></div>',
      '#ajax' => array(
         'callback' => '::checkUserEmailValidation’,
         'effect' => 'fade',
         'event' => 'change',
          'progress' => array(
             'type' => 'throbber',
             'message' => NULL,
          ),
      ),
       );
  );
}
}