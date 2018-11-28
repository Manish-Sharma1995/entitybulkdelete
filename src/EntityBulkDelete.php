<?php

namespace Drupal\entitybulkdelete;

use Drupal\node\Entity\Node;


/**
 * Provides a bulk delete node functions.
 */
class EntityBulkDelete {
  /**
   *
   */
  public static function nodeDelete($nid, &$context) {
    $message = 'Deleting Nodes...';
    $results = [];
    // Deletes a node of given id.
    $node = Node::load($nid);
    $node->delete();
    $context['results'][] = $node;
    $context['message'] = $message;
  }

  /**
   *
   */
  public static function nodeDeleteFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One node deleted.', '@count nodes deleted.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }
}
