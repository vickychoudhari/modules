<?php
namespace Drupal\fetchnodeid\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_print\Plugin\ExportTypeManagerInterface;
use Drupal\entity_print\PrintBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
/**
 * Controller to display an entity in a particular printable format.
 */
class FetchNodeIdController extends ControllerBase {
  /**
   * Print an entity to the selected format.
   *
   * @param string $export_type
   *   The export type.
   * @param string $entity_type
   *   The entity type.
   * @param int $entity_id
   *   The entity id.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response object on error otherwise the Print is sent.
   */
  public function viewPrint($entity_type) {
    // Create the Print engine plugin.
    $config = $this->config('entity_print.settings');
    // $entity = $this->configManager->getEntityManager()->getStorage($entity_id);
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
    $print_engine = $this->pluginManager->createSelectedInstance($export_type);
    return (new StreamedResponse(function () use ($entity, $print_engine, $config) {
      // The Print is sent straight to the browser.
      $this->printBuilder->deliverPrintable([$entity], $print_engine, $config->get('force_download'), $config->get('default_css'));
    }))->send();
  }
}