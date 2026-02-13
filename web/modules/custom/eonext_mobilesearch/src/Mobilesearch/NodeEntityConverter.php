<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\eonext_mobilesearch\Form\MobilesearchSettingsForm;
use Drupal\eonext_mobilesearch\Mobilesearch\DTO\FieldDto;
use Drupal\eonext_mobilesearch\Mobilesearch\DTO\NodeEntityDto;
use Drupal\eonext_mobilesearch\Mobilesearch\DTO\TaxonomyDto;
use Drupal\node\NodeInterface;
use Safe\DateTime;
use function Safe\file_get_contents;

/**
 * Converts a node entity into a serializable object.
 */
class NodeEntityConverter extends AbstractEntityConverter {

  /**
   * The agency ID for mobilesearch communication.
   *
   * @var string
   */
  protected string $agencyId;

  /**
   * Converter constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityFieldManagerInterface $entityFieldManager,
    protected ConfigFactoryInterface $configFactory,
  ) {
    $this->agencyId = $this->configFactory->get(MobilesearchSettingsForm::CONFIG_ID)->get('agency');
  }

  /**
   * {@inheritDoc}
   */
  public function convert(EntityInterface $entity): NodeEntityDto {
    assert($entity instanceof NodeInterface);
    $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions(
      $entity->getEntityTypeId(),
      $entity->getType()
    );

    $fields = [];
    $taxonomy = [];
    foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
      if (!$entity->hasField($fieldName)) {
        // @todo Log this.
        continue;
      }

      switch ($fieldDefinition->getType()) {
        case 'link':
          $this->resolveUrl($entity, $fieldName, $fieldDefinition, $fields);
          break;

        case 'duration':
          $this->resolveLength($entity, $fieldName, $fieldDefinition, $fields);
          break;

        case 'created':
        case 'changed':
          $this->resolveDate($entity, $fieldName, $fieldDefinition, $fields);
          break;

        case 'image':
          $this->resolveImage($entity, $fieldName, $fieldDefinition, $fields);
          break;

        case 'entity_reference':
          $this->resolveReferences($entity, $fieldName, $fieldDefinition, $fields);
          $this->resolveTaxonomyTerms($entity, $fieldName, $fieldDefinition, $taxonomy);
          break;

        case 'path':
          $this->resolvePathAlias($entity, $fieldName, $fieldDefinition, $fields);
          break;

        case 'daterange':
          $this->resolveDates($entity, $fieldName, $fieldDefinition, $fields);
          break;

        case 'boolean':
        case 'integer':
        case 'string':
        case 'string_long':
        case 'text':
        case 'text_with_summary':
          $this->resolveField($entity, $fieldName, $fieldDefinition, $fields);
        default:
      }
    }

    return new NodeEntityDto(
      (string) $entity->id(),
      $this->agencyId ?? '000000',
      $entity->getType(),
      $fields,
      $taxonomy
    );
  }

  /**
   * Resolve simple field values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $fields
   *   Target collection where resolved values are inserted.
   */
  protected function resolveField(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$fields): void {
    $mainProperty = $fieldDefinition->getFieldStorageDefinition()->getMainPropertyName();

    $fields[$fieldName] = new FieldDto(
      (string) $fieldDefinition->getLabel(),
      $node->get($fieldName)->{$mainProperty} ?? ''
    );
  }

  /**
   * Resolve 'entity_reference' term field values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $taxonomy
   *   Target collection where resolved values are inserted.
   */
  protected function resolveTaxonomyTerms(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$taxonomy): void {
    $targetType = $fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');

    if ('taxonomy_term' !== $targetType) {
      return;
    }

    $terms = [];
    foreach ($node->get($fieldName) as $item) {
      // @phpstan-ignore-next-line
      if ($item->entity) {
        $terms[] = $item->entity->label();
      }
    }

    $taxonomy[$fieldName] = new TaxonomyDto(
      (string) $fieldDefinition->getLabel(),
      $terms
    );
  }

  /**
   * Resolve 'image' type field values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $fields
   *   Target collection where resolved values are inserted.
   */
  protected function resolveImage(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$fields): void {
    $image_data = [];
    $image_mime = [];
    foreach ($node->get($fieldName) as $item) {
      /** @var \Drupal\file\Entity\File|null $image */
      // @phpstan-ignore-next-line
      $image = $item->entity;
      if (!$image || !is_readable($image->get('uri')->value)) {
        // @todo Log this.
        continue;
      }
      $image_data[] = base64_encode(file_get_contents($image->get('uri')->value));
      $image_mime[] = $image->get('filemime')->value;
    }

    $fields[$fieldName] = new FieldDto(
      (string) $fieldDefinition->getLabel(),
      $image_data,
      $image_mime
    );
  }

  /**
   * Resolve 'changed|created' (timestamp) type field values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $fields
   *   Target collection where resolved values are inserted.
   */
  protected function resolveDate(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$fields): void {
    $mainProperty = $fieldDefinition->getFieldStorageDefinition()->getMainPropertyName();
    $value = $node->get($fieldName)->{$mainProperty} ?? '';

    if ($value) {
      $value = (new DateTime())->setTimestamp($value)->format(DATE_ATOM);
      $fields[$fieldName] = new FieldDto(
        (string) $fieldDefinition->getLabel(),
        $value
      );
    }
  }

  /**
   * Resolve 'duration' type field values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $fields
   *   Target collection where resolved values are inserted.
   */
  protected function resolveLength(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$fields): void {
    $length = $node->get($fieldName)->seconds ?? NULL;

    if (NULL !== $length) {
      $fields[$fieldName] = new FieldDto(
        (string) $fieldDefinition->getLabel(),
        (int) ($length / 60)
      );
    }
  }

  /**
   * Resolve 'url' type field values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $fields
   *   Target collection where resolved values are inserted.
   */
  protected function resolveUrl(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$fields): void {
    $uri = $node->get($fieldName)->uri ?? '';
    $title = $node->get($fieldName)->title ?? '';

    $fields[$fieldName . '_uri'] = new FieldDto(
      (string) $fieldDefinition->getLabel(),
      $uri
    );

    $fields[$fieldName . '_title'] = new FieldDto(
      (string) $fieldDefinition->getLabel(),
      $title
    );
  }

  /**
   * Resolve 'entity_reference' type field values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $fields
   *   Target collection where resolved values are inserted.
   */
  protected function resolveReferences(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$fields): void {
    $targetType = $fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    $mainProperty = $fieldDefinition->getFieldStorageDefinition()->getMainPropertyName();
    $targetStorage = $this->entityTypeManager->getStorage($targetType);

    $references = [];
    $attr = [];
    foreach ($node->get($fieldName) as $item) {
      $targetEntity = $targetStorage->load($item->{$mainProperty});
      if (!$targetEntity) {
        continue;
      }

      $isImage = $targetEntity instanceof ContentEntityBase
        && $targetEntity->hasField('field_media_image')
        && $targetEntity->get('field_media_image')->getFieldDefinition()->getTargetBundle() === 'image';
      if ($isImage) {
        $prop = $targetEntity->get('field_media_image')->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
        $handler = $targetEntity->get('field_media_image')->getFieldDefinition()->getSetting('target_type');
        $fileStorage = $this->entityTypeManager->getStorage($handler);
        /** @var \Drupal\file\Entity\File $imageEntity */
        $imageEntity = $fileStorage->load($targetEntity->get('field_media_image')->{$prop});
        $fileUri = $imageEntity->getFileUri();
        if ($fileUri !== NULL) {
          $references[] = base64_encode(file_get_contents($fileUri));
          $attr[] = $imageEntity->getMimeType();
        }
      }
      else {
        $references[] = $targetEntity->label();
      }
    }

    $fields[$fieldName] = new FieldDto(
      (string) $fieldDefinition->getLabel(),
      $references,
      $attr
    );
  }

  /**
   * Resolve 'path' type field values.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $target
   *   Target collection where resolved values are inserted.
   */
  protected function resolvePathAlias(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$target): void {
    $mainProperty = $fieldDefinition->getFieldStorageDefinition()->getMainPropertyName();

    $target[$fieldName] = new FieldDto(
      (string) $fieldDefinition->getLabel(),
      $node->get($fieldName)->{$mainProperty},
      []
    );
  }

  /**
   * Resolve 'daterange' type field values.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $node
   *   Node object.
   * @param string $fieldName
   *   Field name, whose value(s) to resolve.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition.
   * @param array<string, mixed> $target
   *   Target collection where resolved values are inserted.
   */
  protected function resolveDates(FieldableEntityInterface $node, string $fieldName, FieldDefinitionInterface $fieldDefinition, array &$target): void {
    $target[$fieldName] = new FieldDto(
      (string) $fieldDefinition->getLabel(),
      [
        // @phpstan-ignore-next-line
        'from' => $node->get($fieldName)->start_date->format('c'),
        // @phpstan-ignore-next-line
        'to' => $node->get($fieldName)->end_date->format('c'),
      ],
      [
        'all_day' => FALSE,
      ]
    );
  }

}
