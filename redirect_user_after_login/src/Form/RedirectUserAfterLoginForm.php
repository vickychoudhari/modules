<?php

namespace Drupal\redirect_user_after_login\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class RedirectUserAfterLoginForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'redirect_user_after_login_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        // Form constructor.
        $form = parent::buildForm($form, $form_state);
        // Default settings.
        $config = $this->config('redirect_user_after_login.settings');

        $form['url_after_login'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Url Redirection'),
            '#required' => TRUE,
            '#default_value' => $config->get('redirect_user_after_login.path'),
            '#description' => $this->t('Add a valid url or &ltfront> for front page'),
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        $path = $form_state->getValue('url_after_login');
        $is_valid = \Drupal::service('path.validator')->isValid($path);
        if ($is_valid == NULL) {
            $form_state->setErrorByName('url_after_login', $this->t('Path not found.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('redirect_user_after_login.settings');
        $config->set('redirect_user_after_login.path', $form_state->getValue('url_after_login'));
        $config->save();
        return parent::submitForm($form, $form_state);
    }

    /**
     * Gets the configuration names that will be editable.
     *
     * @return array
     *   An array of configuration object names that are editable if called in
     *   conjunction with the trait's config() method.
     */
    protected function getEditableConfigNames()
    {
        return [
            'redirect_user_after_login.settings',
        ];
    }
}