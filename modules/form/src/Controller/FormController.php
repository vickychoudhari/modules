<?php

namespace Drupal\form\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\form\Form\MyForm;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class FormController
 * @package Drupal\form\Controller
 */
class FormController extends ControllerBase
{ 

  public function index() {
   

   $addForm = new MyForm();
    $form = \Drupal::formBuilder()->getForm($addForm);

    
    //create table header
    $header_table = array(
      'id' => t('ID'),
      'first_name' => t('first name'),
      'last_name' => t('last name'),
      'email' => t('Email'),
      'phone' => t('phone'),
      'view' => t('View'),
      'delete' => t('Delete'),
      'edit' => t('Edit'),
    );
    // get data from database
    $query = \Drupal::database()->select('newtable', 'n');
    $query->fields('n', ['id', 'first_name', 'last_name', 'email', 'phone']);
    $results = $query->execute()->fetchAll();

    $rows = array();
    foreach ($results as $data) {
      $url_delete = Url::fromRoute('form.delete_form', ['id' => $data->id], []);
      $url_edit = Url::fromRoute('form.add_form', ['id' => $data->id], []);
      $url_view = Url::fromRoute('form.show_data', ['id' => $data->id], []);
      
      $linkDelete = Link::fromTextAndUrl('Delete', $url_delete);
      $linkEdit = Link::fromTextAndUrl('Edit', $url_edit);
      $linkView = Link::fromTextAndUrl('View', $url_view);

    $rows[] =array(
      'id' => $data->id,
      'first_name' => $data->first_name,
      'last_name' => $data->last_name,
      'email' => $data->email,
      'phone' => $data->phone,
      'view' => $linkView,
      'delete' => $linkDelete,
      'edit' =>  $linkEdit,
    );
   }

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => t('No data found'),
    ];

    return $form;
  }
  // public function delete($cid){
  //     $res = db_query('id => $cid');
  //   //  drupal_set_message('Success delete');
  //    return $this->redirect('form.add_form');
  
  // }
}