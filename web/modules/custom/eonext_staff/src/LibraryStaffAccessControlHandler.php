<?php

declare(strict_types=1);

namespace Drupal\eonext_staff;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the library staff entity type.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @see https://www.drupal.org/project/coder/issues/3185082
 */
final class LibraryStaffAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    $adminPermission = $this->entityType->getAdminPermission();
    if (is_string($adminPermission) && $account->hasPermission($adminPermission)) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return match($operation) {
      'view' => AccessResult::allowedIfHasPermission($account, 'view eonext_library_staff'),
      'update' => AccessResult::allowedIfHasPermission($account, 'edit eonext_library_staff'),
      'delete' => AccessResult::allowedIfHasPermission($account, 'delete eonext_library_staff'),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   * @param array<string, mixed> $context
   *   An associative array of additional context values.
   * @param string|null $entity_bundle
   *   The entity bundle name, or NULL.
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, ['edit eonext_library_staff', 'administer eonext_library_staff'], 'OR');
  }

}
