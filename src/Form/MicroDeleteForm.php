<?php

/**
 * @file
 * Contains \Drupal\micro\Form\MicroDeleteForm.
 */

namespace Drupal\micro\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a micro.
 */
class MicroDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a MicroDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   */
  public function __construct(EntityManagerInterface $entity_manager, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_manager);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete %title?', array('%title' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    // @todo Convert to getCancelRoute() after http://drupal.org/micro/1987778.
   // $uri = $this->entity->uri();
   // $actions['cancel']['#href'] = $this->urlGenerator->generateFromPath($uri['path'], $uri['options']);

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    watchdog('content', '@type: deleted %title.', array('@type' => $this->entity->bundle(), '%title' => $this->entity->label()));
    $micro_type_storage = $this->entityManager->getStorage('micro_type');
    $micro_type = $micro_type_storage->load($this->entity->bundle())->label();
    drupal_set_message(t('@type %title has been deleted.', array('@type' => $micro_type, '%title' => $this->entity->label())));
    Cache::invalidateTags(array('content' => TRUE));
    $form_state->setRedirect('<front>');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('micro.type_list');
  }
}
