<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch\DTO;

/**
 * Interface for mobilesearch entities payload.
 */
interface MobilesearchEntityInterface extends \JsonSerializable {

  /**
   * Gets entity id.
   *
   * @return int
   *   Entity id.
   */
  public function getId(): int;

  /**
   * Gets route where push should occur.
   *
   * @return string
   *   Route suffix.
   */
  public function getRoute(): string;

  /**
   * Gets entity name eager to push.
   *
   * @return string
   *   Entity name.
   */
  public function getEntityName(): string;

  /**
   * Gets node id.
   *
   * @return string
   *   Node id.
   */
  public function getNid(): string;

  /**
   * Sets node id.
   *
   * @param string $nid
   *   Node id.
   *
   * @return static
   *   DTO object.
   */
  public function setNid(string $nid): static;

  /**
   * Gets the node content type.
   *
   * @return string
   *   The content type.
   */
  public function getType(): string;

  /**
   * Sets the node content type.
   *
   * @param string $type
   *   The content type.
   */
  public function setType(string $type): void;

  /**
   * Gets node fields.
   *
   * @return array<int|string, FieldDto>
   *   Node fields DTO array.
   */
  public function getFields(): array;

  /**
   * Sets node fields.
   *
   * @param array<int|string, FieldDto> $fields
   *   Node fields DTO array.
   *
   * @return static
   *   DTO object.
   */
  public function setFields(array $fields): static;

  /**
   * Gets the taxonomy terms.
   *
   * @return array<int|string, TaxonomyDto>
   *   Taxonomy DTO array.
   */
  public function getTaxonomy(): array;

  /**
   * Sets the taxonomy terms.
   *
   * @param array<int|string, TaxonomyDto> $taxonomy
   *   Taxonomy DTO array.
   *
   * @return static
   *   DTO object.
   */
  public function setTaxonomy(array $taxonomy): static;

}
