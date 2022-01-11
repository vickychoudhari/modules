<?php

namespace Drupal\article\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Provides a 'article' block.
 *
 * @Block(
 *   id = "article_block",
 *   admin_label = @Translation("Article block"),
 *   category = @Translation("Custom article block example")
 * )
 */
class ArticleBlock extends BlockBase {

	
  /**
   * {@inheritdoc}
   */
  public function build() {

$connection = \Drupal::database();
          $qry = $connection->select('node_field_data' , 'nfd');
          $qry->join('node__field_new_images', 'nfni', 'nfd.nid = nfni.entity_id');
          $qry->join('file_managed', 'fm','nfd.nid = fm.fid');
          $qry->fields('nfni', ['field_new_images_alt', 'entity_id', 'field_new_images_target_id'])
                 ->fields('nfd', ['nid', 'title', 'type'])
                 ->fields('fm', ['fid', 'uri'])
                 ->condition('nfd.type', 'images_styles');
                 
          $result = $qry->execute()->fetchAll();
                      

                       // $qry = $connection->select('node' , 'n');
                       // $result = $qry->execute()->fetchAll();
                       // echo"<pre>;
                       // print_r($result);
                       // die();



                 // echo "<pre>";
                 // print_r($result);
                 // die();
                    $list_of_images = [];
                 foreach ($result as $key => $value) {
                    $list_of_images[$key]['title'] = $value->title;
                    $list_of_images[$key]['uri'] = $value->uri;
                    // $list_of_images[$key]['fid'] = $value->fid;
                    // $list_of_images[$key]['type'] = $value->type;
                    }
               
                // echo "<pre>";
                // print_r($menu_link);
                // die();
                return [
                '#theme' => 'article_tpl',
                '#result_node' => $list_of_images,
                '#cache' => ['max-age' => 0,
                ],
    ];          
   }
  }
