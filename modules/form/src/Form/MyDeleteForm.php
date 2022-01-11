<?php

namespace Drupal\form\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;


/**
 * Class MyDeleteForm
 * @package Drupal\form\Form
 */
class MyDeleteForm extends ConfirmFormBase
{
  public $id;

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'delete_form';
  }

  public function getQuestion()
  {
    return t('Delete data');
  }


  public function getCancelUrl()
  {
    return new Url('form.add_form');
  }

  public function getDescription()
  {
    return t('Do you want to delete data number %id ?', array('%id' => $this->id));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText()
  {
    return t('Delete it!');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText()
  {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL)
  {

    $this->id = $id;
    // die("121");
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $query = \Drupal::database();
    $query->delete('newtable')
      ->condition('id', $this->id)
      ->execute();
    \Drupal::messenger()->addStatus('Succesfully deleted.');
    $form_state->setRedirect('form.add_form');
  }
}