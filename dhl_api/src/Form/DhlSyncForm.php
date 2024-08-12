<?php

namespace Drupal\dhl_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Render\Markup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Configure DHL API sync form.
 *
 * @package Drupal\dhl_api\Form
 */
class DhlSyncForm extends FormBase
{
    public const DHL_API = 'dhl_api.settings';

  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return self::DHL_API;
    }

  /**
   * {@inheritdoc}
   */
    protected function getEditableConfigNames()
    {
        return [self::DHL_API];
    }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form['eo_settings'] = [
        '#type' => 'details',
        '#tree' => true,
        '#title' => $this->t('Settings'),
        '#open' => true,
        ];

        $form['eo_settings']['country'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Enter Country Name'),
        '#maxlength' => 255,
        '#required' => true,
        ];

        $form['eo_settings']['city'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Enter City name'),
        '#maxlength' => 255,
        '#required' => true,
        ];

        $form['eo_settings']['pin'] = [
        '#type' => 'number',
        '#title' => $this->t('Enter Pin Code'),
        '#maxlength' => 255,
        '#required' => false,
        '#min' => 0,
        '#max' => 999999,
        ];

        //submit button.
        $form['actions']['submit'] = [
         '#type' => 'submit',
         '#value' => $this->t('Send Request'),
         '#button_type' => 'primary',
        ];

        $store = \Drupal::service('tempstore.private')->get('dhl_api');
        $data = $store->get('yml_render');
        $markup = null;
        if (!empty($data)) {
            $form['avalilable location'] = [
            '#type' => 'details',
            '#tree' => true,
            '#title' => $this->t('API Output'),
            '#open' => true,
            ];

            foreach ($data as $key => $value) {
                $form['avalilable location'][$key]['code'] = [
                '#type' => 'textarea',
                '#weight' => $key,
                '#size' => 100,
                '#value' => $value,
                ];
            }
        }

        return $form;
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $eo_settings = $form_state->getValue('eo_settings');
        $query_string = "countryCode={$eo_settings['country']}
        &addressLocality={$eo_settings['city']}
        &postalCode={$eo_settings['pin']}";
        if ($query_string !== "") {
            if ($knpi_exact_online_webshop_token_service = \Drupal::service('dhl_api.eo_client')) {
                $knpi_exact_online_webshop_token_service->countryLookupBycode($query_string);
                $form['markup'] = ["#markup" => "DONE"];
            }
        }
    }
}
