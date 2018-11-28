<?php

namespace Drupal\entitybulkdelete\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a form for bulk deletion of users.
 */
class BulkDeleteNodesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulk_delete_nodes';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['delete_content'] = [
      '#type' => 'checkbox',
      '#title' => t('Delete nodes of a content type.'),
      '#ajax' => [
        'callback' => [$this, 'entitybulkdelete_type_callback'],
        'wrapper' => 'delete-content',
      ],
    ];
    $form['type_placeholder'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="delete-content">',
      '#suffix' => '</div>',
    ];
    $content_types_list = [];
    if(!empty($form_state->getValue('delete_content'))){
      $content_types = \Drupal::service('entity.manager')
        ->getStorage('node_type')
        ->loadMultiple();
      $content_types_list = [];
      foreach ($content_types as $content_type) {
          $content_types_list[$content_type->id()] = $content_type->label();
      }
      $form['type_placeholder']['type'] = [
        '#title' => 'Content type',
        '#type' => 'select',
        '#options' => $content_types_list,
        '#multiple' => FALSE,
      ];
    }
    $form['delete_nid'] = [
      '#type' => 'checkbox',
      '#title' => t('Delete nodes by nids.'),
      '#ajax' => [
        'callback' => [$this, 'entitybulkdelete_nid_callback'],
        'wrapper' => 'delete-nid',
      ],
    ];
    $form['nid_placeholder'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="delete-nid">',
      '#suffix' => '</div>',
    ];
    if(!empty($form_state->getValue('delete_nid'))){
      $form['nid_placeholder']['nids'] = [
        '#type' => 'textarea',
        '#title' => 'Node Ids',
        '#cols' => 10,
        '#rows' => 5,
        '#description' => t('Enter the "," separated node ids'),
      ];      
    } 
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];   
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // $nids = $values['nids'];
    $content_type = $values['type'];
    $nids = \Drupal::entityQuery('node')
      ->condition('type', $content_type)
      ->execute();
    $node_ids = explode(",",$values['nids']);
    foreach ($node_ids as $nid) {
      array_push($nids, $nid);
    }
    $batch = [
      'title' => t('Deleting Nodes...'),
      'operations' => [],
      'init_message'     => t('Initialising'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished' => '\Drupal\entitybulkdelete\EntityBulkDelete::nodeDeleteFinishedCallback',
    ];
    foreach ($nids as $nid) {
      $batch['operations'][] = ['\Drupal\entitybulkdelete\EntityBulkDelete::nodeDelete', [$nid]];
    }
    batch_set($batch);
  }
  /**
   * { @inheritdoc }.
   */
  public function entitybulkdelete_type_callback(array &$form, FormStateInterface $form_state) {
      return $form['type_placeholder'];
  }
  /**
   * { @inheritdoc }.
   */
  public function entitybulkdelete_nid_callback(array &$form, FormStateInterface $form_state) {
      return $form['nid_placeholder'];
  }  
}
