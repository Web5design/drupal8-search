<?php

/**
 * @file
 * Contains \Drupal\system\Form\DateFormatLocalizeResetForm.
 */

namespace Drupal\system\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\ControllerInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds a form for enabling a module.
 */
class DateFormatLocalizeResetForm extends ConfirmFormBase implements ControllerInterface {

  /**
   * The language to be reset.
   *
   * @var \Drupal\Core\Language\Language;
   */
  protected $language;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a DateFormatLocalizeResetForm object.
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'system_date_format_localize_reset_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getQuestion() {
    return t('Are you sure you want to reset the date formats for %language to the global defaults?', array(
      '%language' => $this->language->name,
    ));
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfirmText() {
    return t('Reset');
  }

  /**
   * {@inheritdoc}
   */
  protected function getCancelPath() {
    return 'admin/config/regional/date-time/locale';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDescription() {
    return t('Resetting will remove all localized date formats for this language. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   *
   * @param string $langcode
   *   The language code.
   *
   */
  public function buildForm(array $form, array &$form_state, $langcode = NULL) {
    $this->language = language_load($langcode);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->configFactory->get('locale.config.' . $this->language->langcode . '.system.date')->delete();

    $form_state['redirect'] = 'admin/config/regional/date-time/locale';
  }

}
