<?php

namespace Drupal\az_blob_fs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'az_blob_fs.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('az_blob_fs.settings');

    $form['#prefix'] = '<div id="azblob-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['az_blob_account_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account Name'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('az_blob_account_name'),
    ];
    $form['az_blob_account_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account Key'),
      '#description' => $this->t('The Account Key is hidden in this field for security reason; If you need to change it, just add it again.'),
      '#maxlength' => 255,
      '#size' => 64,
    ];
    $form['az_blob_container_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Azure Blob Container name'),
      '#description' => $this->t('Create a blob container on from your storage account with public permissions for the container.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('az_blob_container_name'),
    ];
    $form['az_blob_local_ip'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Local IP'),
      '#description' => $this->t('The local IP for your Azure storage emulator.'),
      '#maxlength' => 15,
      '#size' => 15,
      '#default_value' => $config->get('az_blob_local_ip'),
      '#states' => [
        'visible' => [
          ':input[name="az_blob_local_emulator"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['az_blob_local_port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Local port'),
      '#description' => $this->t('The local port for your Azure storage emulator.'),
      '#maxlength' => 10,
      '#size' => 10,
      '#default_value' => $config->get('az_blob_local_port'),
      '#states' => [
        'visible' => [
          ':input[name="az_blob_local_emulator"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['az_blob_local_emulator'] = [
      '#type' => 'checkbox',
      '#title' => t('Use a local storage emulator.'),
      '#default_value' => $config->get('az_blob_local_emulator'),
      '#ajax' => [
        'callback' => [$this, 'emulatorValues'],
        'event' => 'click',
        'wrapper' => 'azblob-form-wrapper',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax callback for form checkbox. Complete default form values on click.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form values.
   *
   * @return array
   *   The form with default values filled.
   */
  public function emulatorValues(array &$form, FormStateInterface $form_state) {
    $form['az_blob_account_name']['#value'] = 'devstoreaccount1';
    $form['az_blob_account_key']['#value'] = 'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==';
    $form['az_blob_local_ip']['#value'] = '127.0.0.1';
    $form['az_blob_local_port']['#value'] = '10000';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $az_blob_fs_settings = $this->config('az_blob_fs.settings');
    $account_key = $form_state->getValue('az_blob_account_key');
    $az_blob_fs_settings
      ->set('az_blob_account_name', $form_state->getValue('az_blob_account_name'))
      ->set('az_blob_container_name', $form_state->getValue('az_blob_container_name'))
      ->set('az_blob_local_ip', $form_state->getValue('az_blob_local_ip'))
      ->set('az_blob_local_port', $form_state->getValue('az_blob_local_port'))
      ->set('az_blob_local_emulator', $form_state->getValue('az_blob_local_emulator'));

    if ($account_key != '') {
      $az_blob_fs_settings
        ->set('az_blob_account_key', $form_state->getValue('az_blob_account_key'));
    }

    $az_blob_fs_settings->save();
  }

}
