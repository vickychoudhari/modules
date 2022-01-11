<?php
/**
 * @file
 * Contains \Drupal\custom_form\Form\CustomForm.
 */
namespace Drupal\custom_form\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CustomForm extends FormBase{

 /**
   * {@inheritdoc}
   */
  public function getFormId(){
      return 'employee_form';
  }
   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $genderOption = array(
        '0' => 'selectGender',
        'male' => 'Male',
        'female' =>'Female',
        'option' => 'others'
    );
    $form['candidate_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Candidate Name:'),
      '#required' => TRUE,
    );
    $form['candidate_mail'] = array(
      '#type' => 'email',
      '#title' => t('Email ID:'),
      '#required' => TRUE,
    );
    $form['candidate_number'] = array (
      '#type' => 'tel',
      '#title' => t('Mobile no'),
    );
    $form['candidate_dob'] = array (
      '#type' => 'date',
      '#title' => t('DOB'),
      '#required' => TRUE,
    );
    $form['candidate_gender'] = array (
      '#type' => 'select',
      '#title' =>'gender',
      '#options' => $genderOption
    );
    $form['candidate_confirmation'] = array (
      '#type' => 'radios',
      '#title' => ('Are you above 18 years old?'),
      '#options' => array(
        'Yes' =>t('Yes'),
        'No' =>t('No')
      ),
    );
    $form['candidate_copy'] = array(
      '#type' => 'checkbox',
      '#title' => t('Send me a copy of the application.'),
    );
    $form['actions']['#type'] = 'actions';
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
  public function submitForm(array &$form, FormStateInterface $form_state) {


     \Drupal::messenger()->addMessage(t("Student Registration Done!! Registered Values are:"));
    foreach ($form_state->getValues() as $key => $value) {
    \Drupal::messenger()->addMessage($key . ': ' . $value);
    }
   // echo "<pre>";
   // print_r($value);
   // die();
    
}
}