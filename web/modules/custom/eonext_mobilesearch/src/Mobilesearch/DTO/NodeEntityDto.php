<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch\DTO;

/**
 * Node entity serializable payload object.
 */
class NodeEntityDto implements MobilesearchEntityInterface {

  /**
   * DTO constructor.
   *
   * @param string $nid
   *   Node ID.
   * @param string $agency
   *   Agency ID.
   * @param string $type
   *   Content type.
   * @param array<int|string, FieldDto> $fields
   *   Node fields.
   * @param array<int|string, TaxonomyDto> $taxonomy
   *   Taxonomy terms.
   */
  public function __construct(
    protected string $nid,
    protected string $agency,
    protected string $type,
    protected array $fields = [],
    protected array $taxonomy = [],
  ) {}

  /**
   * Gets node id.
   *
   * @return string
   *   Node id.
   */
  public function getNid(): string {
    return $this->nid;
  }

  /**
   * Sets node id.
   *
   * @param string $nid
   *   Node id.
   *
   * @return static
   *   DTO object.
   */
  public function setNid(string $nid): static {
    $this->nid = $nid;

    return $this;
  }

  /**
   * Gets node agency.
   *
   * @return string
   *   Sets node agency.
   */
  public function getAgency(): string {
    return $this->agency;
  }

  /**
   * Sets node agency.
   *
   * @param string $agency
   *   Node agency.
   *
   * @return self
   *   DTO object.
   */
  public function setAgency(string $agency): self {
    $this->agency = $agency;

    return $this;
  }

  /**
   * Gets the node content type.
   *
   * @return string
   *   The content type.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Sets the node content type.
   *
   * @param string $type
   *   The content type.
   */
  public function setType(string $type): void {
    $this->type = $type;
  }

  /**
   * Gets node fields.
   *
   * @return array<int|string, FieldDto>
   *   Node fields DTO array.
   */
  public function getFields(): array {
    return $this->fields;
  }

  /**
   * Sets node fields.
   *
   * @param array<int|string, FieldDto> $fields
   *   Node fields DTO array.
   *
   * @return static
   *   DTO object.
   */
  public function setFields(array $fields): static {
    $this->fields = $fields;

    return $this;
  }

  /**
   * Gets the taxonomy terms.
   *
   * @return array<int|string, TaxonomyDto>
   *   Taxonomy DTO array.
   */
  public function getTaxonomy(): array {
    return $this->taxonomy;
  }

  /**
   * Sets the taxonomy terms.
   *
   * @param array<int|string, TaxonomyDto> $taxonomy
   *   Taxonomy DTO array.
   *
   * @return static
   *   DTO object.
   */
  public function setTaxonomy(array $taxonomy): static {
    $this->taxonomy = $taxonomy;

    return $this;
  }

  /**
   * {@inheritDoc}
   *
   * @return array<string, mixed>
   *   The serialized node entity data.
   */
  public function jsonSerialize(): array {
    return [
      'nid' => $this->nid,
      'agency' => $this->agency,
      'type' => $this->type,
      'fields' => $this->fields,
      'taxonomy' => $this->taxonomy,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getId(): int {
    return (int) $this->getNid();
  }

  /**
   * {@inheritDoc}
   */
  public function getRoute(): string {
    return 'content';
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityName(): string {
    return 'node';
  }

}
