<?php

declare(strict_types=1);

namespace Drupal\eonext_staff;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a library staff entity type.
 */
interface LibraryStaffInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Sets the user ID for this staff entity.
   *
   * @param int $userId
   *   The user ID to set.
   */
  public function setUserId(int $userId): static;

}
