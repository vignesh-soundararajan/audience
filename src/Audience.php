<?php
namespace Drupal\audience_visiblity;

use Drupal\user\Entity\User;
use Drupal\custom_rtg\app\model\Category;

class Audience {

	public static function UserUpdate($account,$uid = 0) {

      $user = \Drupal::currentUser()->getRoles();
  if(!in_array("administrator", $user)) {
  	
 
	$data = Category::getMyAudienceList();

	foreach ($data as  $value) {

		$query = \Drupal::entityQuery('taxonomy_term')
		->condition('vid', 'audience')
		->condition('field_audience_id', intval($value['id']));
		$tids = $query->execute();
		$t_ids = array_values($tids);
		$audiences[] = $t_ids[0];
	}

	foreach ($audiences as $value) {
		$target_ids[] = ['target_id' => $value];
	}

	if($target_ids) {
		$account->set('field_audience', $target_ids)->save();
	}
    }
  }
}
