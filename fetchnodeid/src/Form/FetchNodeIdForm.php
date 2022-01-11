<?php
/**
 * @file
 * Contains \Drupal\fetchnodeid\Form\FetchNodeIdForm.
 */
namespace Drupal\fetchnodeid\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
class FetchNodeIdForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fetchnodeid_form';
  }
   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'article');
    $entity_ids = $query->execute();
    $form['node_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Node id:'),
      '#required' => TRUE,
    );
    // $form['actions']['#type'] = 'actions';
     $form['actions']['submit'] = array(
      '#type' => 'submit',
       '#value' => $this->t('Save'),
       '#button_type' => 'primary',
     );
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if (strlen($form_state->getValue('candidate_number')) < 10) {
    //   $form_state->setErrorByName('candidate_number', $this->t('Mobile number is too short.'));
    // }
  }
 /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));
    $nid_value = $form_state->getValue('node_id');
    $str_arr = explode (',', $nid_value);
    foreach ($str_arr as $key => $value) {
      $node = \Drupal::EntityTypeManager()->getStorage('node')->load($value);
    }
      // print_r($node);
      // show message and redirect to list page
      \Drupal::messenger()->addStatus('Succesfully saved');
      $url = new Url('fetchnodeid.contents');
      $response = new RedirectResponse($url->toString());
      $response->send();
    }
}