<?php
/**
 * @file
 * Contains \Drupal\mailsend\Form\FormQueue.
 */

namespace Drupal\mailsend\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contribute form.
 */
class FormQueue extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
     return 'queue_forms';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'edit-email'),
      '#attributes' => array('class' => array('mu-readmore-btn')),
      '#title' => t('Email'),
      '#required' => TRUE,
    );
    $form['subject'] = array(
      '#type' => 'textfield',
      '#title' => t('Subject'),
      '#required' => TRUE,
    );
    $form['message'] = array(
      '#type' => 'textarea',
      '#title' => t('Message'),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#attributes' => array('id' => 'edit-button-submit'),
      '#value' => t('Submit'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate email
    if (!valid_email_address($form_state->getValues()['email'])) {
       $form_state->setErrorByName('Email', $this->t('Email address is not a valid one.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $data['email'] = $form_state->getValues()['email'];
    $data['subject'] = $form_state->getValues()['subject'];
    $data['message'] = $form_state->getValues()['message'];
    $queue = \Drupal::queue('email_queue');
    $queue->createQueue();
    $queue->createItem($data);
    // echo "<pre>";
    // print_r($data);
    // die();
  }
}
?>