<?php
/**
 * @file
 * Definition of Drupal\search\Plugin\SearchPluginBase
 */
namespace Drupal\search\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginBase;

/**
 * Base class for plugins wishing to support search.
 */
abstract class SearchPluginBase extends ContainerFactoryPluginBase implements SearchInterface {

  /**
   * The keywords to use in a search.
   *
   * @var string
   */
  protected $keywords;

  /**
   * Array of parameters from the query string from the request.
   *
   * @var array
   */
  protected $searchParams;

  /**
   * Array of attributies - usually from the request object.
   *
   * @var array
   */
  protected $searchAttributes;

  /**
   * {@inheritdoc}
   */
  public function setSearch($keywords, array $params, array $attributes) {
    $this->keywords = (string) $keywords;
    $this->searchParams = $params;
    $this->searchAttributes = $attributes;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchKeywords() {
    return $this->keywords;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchParams() {
    return $this->searchParams;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchAttributes() {
    return $this->searchAttributes;
  }

  /**
   * {@inheritdoc}
   */
  public function isSearchExecutable() {
    // Default implmnetation suitable for plugins that only use keywords.
    return !empty($this->keywords);
  }

  /**
   * {@inheritdoc}
   */
  public function buildResults() {
    $results = $this->execute();
    return array(
      '#theme' => 'search_results',
      '#results' => $results,
      '#module' => $this->pluginDefinition['module'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateIndex() {
    // Empty default implementation.
  }

  /**
   * {@inheritdoc}
   */
  public function resetIndex() {
    // Empty default implementation.
  }

  /**
   * {@inheritdoc}
   */
  public function indexStatus() {
    // No-op default implementation
    return array('remaining' => 0, 'total' => 0);
  }

  /**
   * {@inheritdoc}
   */
  public function addToAdminForm(array &$form, array &$form_state) {
    // Empty default implementation.
  }

  /**
   * {@inheritdoc}
   */
  public function submitAdminForm(array &$form, array &$form_state) {
    // Empty default implementation.
  }
}
