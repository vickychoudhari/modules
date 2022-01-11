<?php
/**
 * @file
 * Contains \Drupal\form\Form\MyForm.
 */
namespace Drupal\form\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;

class MyForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myform_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
   //create a connection 

  	$conn = Database::getConnection();
    $data = array();
    if (isset($_GET['id'])) {
      $query = $conn->select('newtable', 'n')
        ->condition('id', $_GET['id'])
        ->fields('n');
      $data = $query->execute()->fetchAssoc();
      // echo "<pre>";
      // print_r($data);
      // die("222");
      // } 


     $form['first_name'] = [
      '#id' => 'one',
      '#type' => 'textfield',
      '#title' => $this->t('first name'),
      '#required' => true,
      '#size' => 60,
      '#default_value' => (isset($data['first_name'])) ? $data['first_name'] : '',
      '#maxlength' => 128,
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['last_name'] = [
      '#id' => 'two',
      '#type' => 'textfield',
      '#title' => $this->t('last name'),
      '#required' => true,
      '#size' => 60,
      '#default_value' => (isset($data['last_name'])) ? $data['last_name'] : '',
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('email'),
      '#required' => true,
      '#default_value' => (isset($data['email'])) ? $data['email'] : '',
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('phone'),
      '#required' => true,
      '#default_value' => (isset($data['phone'])) ? $data['phone'] : '',
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['select'] = [
      '#type' => 'select',
      '#title' => $this
        ->t('Select element'),
      '#options' => [
        '1' => $this
          ->t('One'),
        '2' => [
          '2.1' => $this
            ->t('Two point one'),
          '2.2' => $this
            ->t('Two point two'),
        ],
        '3' => $this
          ->t('Three'),
      ],
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12'],
      '#default_value' => (isset($data['select'])) ? $data['select'] : '',
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('message'),
      '#required' => true,
      '#default_value' => (isset($data['message'])) ? $data['message'] : '',
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('save'),
      '#buttom_type' => 'primary'
    ]; 
    // $form['#attached']['library'][] = 'mymodule/lib';
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  	$data = array( 
  	  'first_name' => $form_state->getValue('first_name'),
  	  'last_name' => $form_state->getValue('last_name'),
  	  'email' => $form_state->getValue('email'),
  	  // 'picture' => $form_state->getValue('picture'),
  	  'phone' => $form_state->getValue('phone'),
  	  'select' => $form_state->getValue('select'),
  	  'message' => $form_state->getValue('message'),
  	);
    $input =$form_state->getUserInput();
    // echo "<pre>";
    // print_r($input);
    // die();


  	 if (isset($_GET['id'])) {
      // update data in database
      \Drupal::database()->update('newtable')->fields($data)->condition('id', $_GET['id'])->execute();
    } else {
      // insert data to database
      \Drupal::database()->insert('newtable')->fields($data)->execute();
    }

        // show message and redirect to list page
    \Drupal::messenger()->addStatus('Succesfully saved');
    $url = new Url('form.add_form');
    $response = new RedirectResponse($url->toString());
    $response->send();
   // $form = $this->formBuilder->getForm('Drupal\form\Controller\FormController::index');
}
}


   