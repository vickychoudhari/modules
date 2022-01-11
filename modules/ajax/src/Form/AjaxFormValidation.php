
<?php
/**
 * @file
 * Contains Drupal\ajax_example\AjaxFormValidation
 */

namespace Drupal\ajax\Form;

use Drupal\Core\Ajax\AjaxResponse;

public function checkUserEmailValidation(array $form, FormStateInterface $form_state) {
   $ajax_response = new AjaxResponse();
 
  // Check if User or email exists or not
   if (user_load_by_name($form_state->getValue(user_email)) || user_load_by_mail($form_state->getValue(user_email))) {
     $text = ‘User or Email is exists';
   } else {
     $text = ’User or Email does not exists';
   }
   $ajax_response->addCommand(new HtmlCommand('#user-email-result', $text));
   return $ajax_response;
   }
}
