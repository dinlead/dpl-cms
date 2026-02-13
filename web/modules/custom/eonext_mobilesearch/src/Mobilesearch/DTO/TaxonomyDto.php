<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch\DTO;

/**
 * Single taxonomy field DTO class.
 */
class TaxonomyDto implements \JsonSerializable {

  /**
   * DTO constructor.
   *
   * @param string $name
   *   Taxonomy name.
   * @param array<int, string> $terms
   *   Taxonomy terms.
   */
  public function __construct(
    protected string $name,
    protected array $terms,
  ) {}

  /**
   * Gets taxonomy name.
   *
   * @return string
   *   Taxonomy name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Sets taxonomy field name.
   *
   * @param string $name
   *   Taxonomy name.
   *
   * @return self
   *   DTO object.
   */
  public function setName(string $name): self {
    $this->name = $name;
    return $this;
  }

  /**
   * Gets taxonomy field values.
   *
   * @return array<int, string>
   *   Taxonomy values (terms).
   */
  public function getValue(): array {
    return $this->terms;
  }

  /**
   * Sets taxonomy values.
   *
   * @param array<int, string> $value
   *   Taxonomy values (terms)
   *
   * @return self
   *   DTO object.
   */
  public function setValue(array $value): self {
    $this->terms = $value;
    return $this;
  }

  /**
   * {@inheritDoc}
   *
   * @return array<string, mixed>
   *   The serialized taxonomy data.
   */
  public function jsonSerialize(): array {
    return [
      'name' => $this->name,
      'terms' => $this->terms,
    ];
  }

}
