<?php

namespace Drupal\charts_highcharts_drilldown\Plugin\views\style;

use Drupal\charts\Plugin\views\style\ChartsPluginStyleChart;
use Drupal\Component\Utility\Html;
use Drupal\core\form\FormStateInterface;

/**
 * Style plugin to render view as a chart.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "chart_highcharts_drilldown",
 *   title = @Translation("Chart highcharts drilldown"),
 *   help = @Translation("Render a chart with drilldown."),
 *   theme = "views_view_chart_highcharts_drilldown",
 *   display_types = { "normal" }
 * )
 */
class ChartsDrilldownPluginStyleChart extends ChartsPluginStyleChart {

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $data_options = $this->displayHandler->getFieldLabels();
    // reduce chart type options
    unset($form['grouping']);
    $form['fields_series_field'] = [
      '#type' => 'radios',
      '#title' => $this->t('Series Field'),
      '#options' => $data_options,
      '#weight' => -6,
      '#default_value' => $this->options['fields_series_field'],
      '#description' => $this->t('Select the series field.'),
    ];
    $form['fields_drilldown_series_field'] = [
      '#type' => 'radios',
      '#title' => $this->t('Drilldown Field'),
      '#options' => $data_options,
      '#weight' => -5,
      '#default_value' => $this->options['fields_drilldown_series_field'],
      '#description' => $this->t('Select the drilldown field.'),
    ];
    $form['fields_data_field'] = [
      '#type' => 'radios',
      '#title' => $this->t('Data Field'),
      '#options' => $data_options,
      '#weight' => -4,
      '#default_value' => $this->options['fields_data_field'],
      '#description' => $this->t('Select the data field (this data will be aggregated for the parent chart).'),
    ];
    $form['fields_operator'] = [
      '#type' => 'radios',
      '#title' => $this->t('Operator Field'),
      '#options' => ['sum' => 'SUM', 'average' => 'AVERAGE'],
      '#weight' => -3,
      '#default_value' => $this->options['fields_operator'],
      '#description' => $this->t('Select the operator for the aggregation method.'),
    ];
    $form_state->set('default_options', $this->options);

  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $chart = parent::render();

    $form_options = $this->options;
    $chart_settings = $this->options['chart_settings'];
    // If the chart is bar, column or pie only.
    if ($chart_settings['type'] == 'bar' || $chart_settings['type'] == 'column' || $chart_settings['type'] == 'pie') {
      if ($form_options['fields_series_field'] != NULL && $form_options['fields_drilldown_series_field'] != NULL &&
        $form_options['fields_data_field'] != NULL && $form_options['fields_operator'] != NULL) {
        $parent_field = $form_options['fields_series_field'];
        $drilldown_field = $form_options['fields_drilldown_series_field'];
        $data_field = $form_options['fields_data_field'];
        $operator = $form_options['fields_operator'];
        $selected_data_fields = is_array($chart_settings['fields']['data_providers']) ? $this->getSelectedDataFields($chart_settings['fields']['data_providers']) : NULL;
        $selected_data_fields = array_keys($selected_data_fields);
        $chart['#id'] = Html::getId($this->view->id() . '_' . $this->view->current_display);

        $this->renderFields($this->view->result);
        $renders = $this->rendered_fields;

        $parents = [];
        $global_data = [];
        $series = [];
        $drilldown = [];

        if (in_array($data_field, $selected_data_fields)) {
          // Build global array: Parent, child and values.
          foreach ($renders as $row_number => $row) {
            $data_row = [];
            $data_row[] = $this->processNumberValueFromField($row_number, $parent_field);
            $data_row[] = $this->processNumberValueFromField($row_number, $drilldown_field);
            $data_row[] = $this->processNumberValueFromField($row_number, $data_field);
            $global_data[] = $data_row;
          }
          // Get a list of parent names.
          foreach ($global_data as $row) {
            $parents[] = $row[0];
          }
          // Build drilldown series
          $drilldown['name'] = $this->view->field[$parent_field]->options['label'];
          foreach ($parents as $parent) {
            $child = ['name' => $parent, 'id' => $parent];
            foreach ($global_data as $row) {
              if ($parent == $row[0]) {
                $child['data'][] = [$row[1], $row[2] + 0];
              }
            }
            $drilldown['series'][] = $child;
          }
          // Build Parent series
          $k = 0;
          $parents = array_unique($parents);
          if ($operator == 'sum') {
            foreach ($parents as $parent) {
              $val = 0;
              foreach ($global_data as $row) {
                if ($parent == $row[0]) {
                  $val = $val + $row[2];
                }
              }
              $val = $val + 0;
              $k = $this->getColorIndex($k);
              $child_2 = [
                'name' => $parent,
                'y' => $val,
                'drilldown' => $parent,
                'color' => $chart_settings['display']['colors'][$k],
              ];
              $series_object['data'][] = $child_2;
              $k++;
            }
          }
          elseif ($operator == 'average') {
            foreach ($parents as $parent) {
              $numerator = 0;
              $denominator = 0;
              foreach ($global_data as $row) {
                if ($parent == $row[0]) {
                  $numerator = $numerator + $row[2];
                  $denominator++;
                }
              }
              $val = $denominator != 0 ? $numerator / $denominator : 0;
              $k = $this->getColorIndex($k);
              $child_2 = [
                'name' => $parent,
                'y' => $val,
                'drilldown' => $parent,
                'color' => $chart_settings['display']['colors'][$k],
              ];
              $series_object['data'][] = $child_2;
              $k++;
            }
          }
          $series[] = $series_object;
          $options['title'] = $chart_settings['display']['title'];
          $options['series'] = $series;
          $options['drilldown'] = $drilldown;
          $chart['#raw_options'] = $options;
        }
        else {
          \Drupal::logger('Charts_highcharts_drilldown')
            ->notice('Select data field in providers fields.');
        }
      }
      else {
        \Drupal::logger('Charts_highcharts_drilldown')
          ->notice('Select parent, drilldown, data and operator fields in the option form.');
      }
    }
    else {
      \Drupal::logger('Charts_highcharts_drilldown')
        ->notice('The chart should be either bar, column or pie.');
    }

    return $chart;
  }

  /**
   * Get an index for config color
   *
   */

  function getColorIndex($k) {
    if ($k < 10) {
      return $k;
    }
    else {
      $array = str_split($k);
      $k = array_sum($array);
      $k = $this->getColorIndex($k);
      return $k;
    }
  }

  /**
   * Utility method to filter out unselected fields from data providers fields.
   *
   * @param array $data_providers
   *   The data providers.
   *
   * @return array
   *   The fields.
   */
  private function getSelectedDataFields(array $data_providers) {
    return array_filter($data_providers, function ($value) {

      return !empty($value['enabled']);
    });
  }

  /**
   * Processes number value based on field.
   *
   * @param int $number
   *   The number.
   * @param string $field
   *   The field.
   *
   * @return \Drupal\Component\Render\MarkupInterface|float|null
   *   The value.
   */
  private function processNumberValueFromField($number, $field) {
    $value = current($this->getField($number, $field));
    if (\Drupal::service('twig')->isDebug()) {
      $value = trim(strip_tags($value));
    }
    if ($value === '' || is_null($value)) {
      $value = NULL;
    }

    return $value;
  }

  function termNames($tid) {
    $query = \Drupal::database()->select('taxonomy_term_field_data', 'td');
    $query->addField('td', 'name');
    $query->condition('td.tid', $tid);
    $term = $query->execute();
    $tname = $term->fetchField();
    return $tname;
  }

}
