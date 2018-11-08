<?php

namespace Drupal\hypothesis\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Configure Hypothesis settings for this site.
 */
class HypothesisAdminSettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|null
   */
  protected $entityTypeManager;

  /**
   * Constructs a HypothesisSettingsForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hypothesis_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'hypothesis.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $hypothesis_settings = $this->config('hypothesis.settings');

    // Setup default behavior setting options.
    $default_options = [
      'highlights' => t('Highlights on by default'),
      'sidebar' => t('Sidebar open by default'),
    ];
    $default_behavior_settings = $hypothesis_settings->get('behavior');
    $default_behavior = !empty($default_behavior_settings) ? $default_behavior_settings : [];

    // Default Behaviors.
    $form['defaults'] = [
      '#type' => 'details',
      '#title' => t('Default Settings'),
      '#open' => TRUE,
    ];

    $form['defaults']['behavior'] = [
      '#type' => 'checkboxes',
      '#title' => 'Customize Hypothesis defaults and behavior below',
      '#options' => $default_options,
      '#default_value' => $default_behavior
    ];

    $form['visibility'] = [
      '#type' => 'vertical_tabs',
      '#title' => t('Visibility Settings'),
      '#open' => TRUE,
    ];

    // Pages.
    $form['path'] = [
      '#type' => 'details',
      '#title' => 'Pages',
      '#open' => TRUE,
      '#group' => 'visibility',
      '#tree' => TRUE
    ];

    $options = [t('All pages except those listed'), t('Only the listed pages')];
    $description = t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array('%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>'));
    $title = t('Pages');

    $visibility_settings = $hypothesis_settings->get('path');
    $default_visibility = !empty($visibility_settings['hypothesis_visibility']) ? $visibility_settings['hypothesis_visibility'] : 0;
  
    $form['path']['hypothesis_visibility'] = array(
      '#type' => 'radios',
      '#title' => t('Show hypothesis on specific pages'),
      '#options' => $options,
      '#default_value' => $default_visibility,
    );

    $pages_settings = $hypothesis_settings->get('path');
    $default_pages = !empty($pages_settings['hypothesis_pages']) ? $pages_settings['hypothesis_pages'] : '';

    $form['path']['hypothesis_pages'] = array(
      '#type' => 'textarea',
      '#title' => '<span class="element-invisible">' . $title . '</span>',
      '#default_value' => $default_pages,
      '#description' => $description,
    );

    // Content types.
    $default_types_settings = $hypothesis_settings->get('content_types');
    $default_types = !empty($default_types_settings['types']) ? $default_types_settings['types'] : [];

    $types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    
    $content_type_options = [];
    foreach ($types as $type => $config) {
      $content_type_options[$type] = $type;
    }

    $form['content_types'] = [
      '#type' => 'details',
      '#title' => 'Content Types',
      '#open' => TRUE,
      '#group' => 'visibility',
      '#tree' => TRUE
    ];

    $form['content_types']['types'] = [
      '#type' => 'checkboxes',
      '#title' => 'Enabled content types',
      '#options' => $content_type_options,
      '#default_value' => $default_types,
      '#description' => t('Show hypothesis only on pages that display content of the given type(s). If you select no types, there will be no type-specific limitation.'),
    ];

    // Roles.
    $user_roles_settings = $hypothesis_settings->get('user_roles');
    $default_roles = !empty($user_roles_settings['roles']) ? $user_roles_settings['roles'] : [];

    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();

    $role_options = [];
    foreach ($roles as $role => $config) {
      $role_options[$role] = $role;
    }

    $form['user_roles'] = [
      '#type' => 'details',
      '#title' => 'Roles',
      '#open' => TRUE,
      '#group' => 'visibility',
      '#tree' => TRUE
    ];

    $form['user_roles']['roles'] = [
      '#type' => 'checkboxes',
      '#title' => 'Enabled Roles',
      '#options' => $role_options,
      '#default_value' => $default_roles,
      '#description' => t('Show hypothesis only for the selected role(s). If you select no roles, hypothesis will be visible to all users.'),
    ];
   
    $form['#attached']['library'][] = 'hypothesis/hypothesis.admin';

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Setup content_types for saving.
    $content_types_settings = $form_state->getValue('content_types');
    $content_types = [];
    if (!empty($content_types_settings) && !empty(array_filter($content_types_settings))) {
      $content_types = array_filter($content_types_settings);
    }

    // Setup roles for saving.
    $role_settings = $form_state->getValue('user_roles');
    $roles = [];
    if (!empty($role_settings) && !empty(array_filter($role_settings))) {
      $roles = array_filter($role_settings);
    }

    $this->config('hypothesis.settings')
      ->set('behavior', $form_state->getValue('behavior'))
      ->set('content_types', $content_types)
      ->set('user_roles', $roles)
      ->set('path', $form_state->getValue('path'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
