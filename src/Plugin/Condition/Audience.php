<?php

namespace Drupal\audience_visiblity\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'User Audience' condition.
 *
 * @Condition(
 *   id = "user_audience",
 *   label = @Translation("User Audience"),
 *   description = @Translation("User Audience"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 */
class Audience extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['audience'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Audience'),
      //'#multiple' => true,
      '#options' => $this->getAudiences(),
      '#description' => $this->t('Check the box for any Audience that should have this Block visible on their Learning Portal pages'),
      '#default_value' => isset($this->configuration['audience']) ? $this->configuration['audience'] : '',
    );

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'audience' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['audience'] = array_filter($form_state->getValue('audience'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    // Use the audience labels. They will be sanitized below.
    $audiences = array_intersect_key($this->getAudiences(), $this->configuration['audience']);
    if (count($audiences) > 1) {
      $audiences  = implode(', ', $audiences);
    }
    else {
      $audiences  = reset($audiences);
    }
    if (!empty($this->configuration['negate'])) {
      return $this->t('The user is not a member of @audiences', ['@audiences' => $audiences]);
    }
    else {
      return $this->t('The user is a member of @audiences', ['@audiences' =>$audiences]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['audience']) && !$this->isNegated()) {
      return TRUE;
    }

    $block_audience = array_values(array_filter($this->configuration['audience']));
    $user = $this->getContextValue('user');
    $user_audiences = $user->get('field_audience')->getValue();
    foreach ($user_audiences as $key => $value) {
      $user_audience[] =  $value['target_id'];
    }
    return (bool) array_intersect($user_audience, $block_audience);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Optimize cache context, if a user cache context is provided, only use
    // user.roles, since that's the only part this condition cares about.
    $contexts = [];
    foreach (parent::getCacheContexts() as $context) {
      $contexts[] = $context == 'user' ? 'user' : $context;
    }
    return $contexts;
  }

  public function getAudiences() {
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "audience");
    $tids = $query->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);

    foreach ($terms as $term) {
      $options[$term->tid->value] = $term->name->value;
    }
    return $options;
  }
}
