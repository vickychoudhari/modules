<?php

namespace Drupal\storage\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Storage type entities.
 */
interface StorageTypeInterface extends ConfigEntityInterface {

  /**
   * Get the default status for Storage items of this type.
   *
   * @return bool
   *   The default status value.
   */
  public function getStatus();

  /**
   * Set the default status value.
   *
   * @param bool $status
   *   The default status value.
   */
  public function setStatus($status);

  /**
   * Get the name pattern for creating the name of the Storage entity.
   *
   * @return string
   *   The name pattern.
   */
  public function getNamePattern();

  /**
   * Set the name pattern for creating the name of the Storage entity.
   *
   * @param string $pattern
   *   The pattern to set.
   */
  public function setNamePattern($pattern);

}
