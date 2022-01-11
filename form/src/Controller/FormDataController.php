<?php

namespace Drupal\form\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\form\Form\MyForm;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;


/**
 * Class FormDataController
 * @package Drupal\form\Controller
 */
class FormDataController extends ControllerBase
{

  /**
   * @return array
   */
  public function show($id)
  {
    $conn = Database::getConnection();

    $query = $conn->select('newtable', 'n')
      ->condition('id', $id)
      ->fields('n');
    $data = $query->execute()->fetchAssoc();
    $full_name = $data['first_name'] . ' ' . $data['last_name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $select = $data['select'];
    $message = $data['message'];
 
    


    return [
      '#type' => 'markup',
      '#markup' => "<h1>THE STUDENT FULLNAME IS : $full_name</h1><br>
                    <p>THE EMAIL ADDRESS: $email .</p>
                    <p>THE PHONE NUMBER IS : $phone .</p>
                    <p>THE SELECT ELEMENT IS : $select .</p>
                    <p>THE MESSAGE IS : $message </p>"
    ];
  }

}