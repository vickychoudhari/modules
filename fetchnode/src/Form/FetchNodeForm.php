<?php
/**
 * @file
 * Contains \Drupal\fetchnode\Form\FetchNodeForm.
 */
namespace Drupal\fetchnode\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Connection;
// use Drupal\Core\Url;
class FetchNodeForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fetchnode_form';
  }
   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $query = \Drupal::entityQuery('node');
    $query->condition('status',1);
    $query->condition('type', 'article');
    $entity_ids = $query->execute();
    // echo "<pre>";
    // print_r($entity_ids);
    // die();
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
//     $nid_value = $form_state->getValue('node_id');
//     $str_arr = explode (',', $nid_value);
//     // echo "<pre>";
//     // print_r($str_arr);
//     // die();
   
//     foreach ($str_arr as $key => $value) {
//       // print_r($value);
//       // die;
//       $node = \Drupal::EntityTypeManager()->getStorage('node')->load($value);
//       $list =[];
//       $list[$key]['nid']= $value;
//       // $list[$key]['title'] =$node->get('title');

//       // $list[$key]['title'] = array($node->title);
//       echo "<pre>";
//       print_r($list);
//       die();
//     }
//     // die;
// }
$query = \Drupal::entityQuery('node');
    $query->condition('status',1);
    $query->condition('type', 'article');
    $entity_ids = $query->execute();
    // echo "<pre>";
    // print_r($entity_ids);
    // die();
$current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($current_user->id()) {
      $url = Url::fromUserInput('/'.'user/' .$uid)->toString();
      $response = new RedirectResponse($url);
      $response->send(); 
  }
}
}