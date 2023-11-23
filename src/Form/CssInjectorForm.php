<?php

namespace Drupal\css_injector\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CssInjectorForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array
  {
    return ['css_injector.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'css_injector_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $module_handler = \Drupal::service('module_handler');
    $css_path = $module_handler->getModule('css_injector')->getPath() . '/css/custom.css';

    // Check if the file exists and is writable
    $file_exists = file_exists($css_path);
    $is_writable = is_writable($css_path);
    $can_write = $file_exists && $is_writable;

    // Display a status message about file permissions
    if ($can_write) {
      $form['permissions_info'] = [
        '#markup' => $this->t('<p>The CSS file is writable.</p>'),
      ];
    } else {
      $form['permissions_info'] = [
        '#markup' => $this->t('<p>The CSS file is not writable. Please check file permissions.</p>'),
        '#prefix' => '<div class="messages messages--error">',
        '#suffix' => '</div>',
      ];
    }

    // Read existing CSS from file if it's readable
    $css_code = ($can_write && $file_exists) ? file_get_contents($css_path) : '';

    $form['css_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSS Code'),
      '#default_value' => $css_code,
      '#description' => $this->t('Enter the CSS code to inject into every page.'),
      '#rows' => 15,
      '#disabled' => !$can_write,
    ];

    // ... rest of your form definition ...

    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $module_handler = \Drupal::service('module_handler');
    $css_path = $module_handler->getModule('css_injector')->getPath() . '/css/custom.css';
    $css_code = $form_state->getValue('css_code');

    // Debugging
    Drupal::logger('css_injector')->notice('Saving CSS to: ' . $css_path);

    $result = file_put_contents($css_path, $css_code);
    if ($result === FALSE) {
      Drupal::logger('css_injector')->error('Failed to write CSS to file.');
    } else {
      Drupal::logger('css_injector')->notice('CSS saved successfully.');
    }

    parent::submitForm($form, $form_state);
  }

}
