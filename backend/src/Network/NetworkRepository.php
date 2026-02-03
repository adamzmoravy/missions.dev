<?php

namespace Karolina\Network;

use Illuminate\Database\Capsule\Manager as DB;
Class NetworkRepository {


	private function getRelationshipModels ($followedId, $followedType, $relationshipType = false) {

		$followers = array();

		if ($relationshipType) {

			return
				\Karolina\Database\Table\Relationship::where('leader_id', $followedId)
				->where('leader_table', $followedType)
				->where('relationship_type', $relationshipType)
				->get();

		} else {

			return
				\Karolina\Database\Table\Relationship::where('leader_id', $followedId)
				->where('leader_table', $followedType)
				->get();
		}

	}


	private function getLeadersRelationshipModels ($leadId, $type) {

			return
				\Karolina\Database\Table\Relationship::where('follower_id', $leadId)
				->where('follower_table', $type)
				->get();

	}

	public function getNetwork ($type, $id) {


		$network = new Root(
			new Node($type, $id)
			);

		$followerModels = $this->getRelationshipModels($id, $type);

		foreach ($followerModels as $model) {


			$node = new Node($model->follower_table, $model->follower_id);

			$network->addFollower($node, $model->relationship_type);

		}

		$leaderModels = $this->getLeadersRelationshipModels($id, $type);

		foreach ($leaderModels as $model) {


			$node = new Node($model->leader_table, $model->leader_id);

			$network->addLeader($node, $model->relationship_type);

		}


		return $network;


	}

	public function saveNetwork (\Karolina\Network\Root $network) {


		$followersArray = $network->getFollowers();

		$deletedRows = \Karolina\Database\Table\Relationship::where('leader_table', $network->getRootType())
		->where('leader_id', $network->getRootId())
		->delete();

		foreach ($followersArray as $relationshipType => $followers) {

			foreach ($followers as $follower) {

				$relationship = \Karolina\Database\Table\Relationship::firstOrNew(
					[
						'follower_id' => $follower->follower->getId(),
						'follower_table' => $follower->follower->getNetworkType(),
						'leader_id' => $follower->getFollowed()->getId(),
						'leader_table' => $follower->getFollowed()->getNetworkType(),
						'relationship_type' => $follower->getRelationshipType(),
                        'platform' => getenv('platform_key')
					]
					);

				$relationship->save();
			}

		}


	}

	public function getStats ($followedType, $followerType, $scope = 'local') {

		$model = new \Karolina\Database\Table\Relationship();
		
		if ($scope == 'global') {
			$model = $model->withoutGlobalScope(\Karolina\Database\PlatformScope::class);
			
		}

		$stats = array();

		$model = $model->where('leader_table', $followedType)->where('follower_table', $followerType);

		$stats['total'] = $model->count();

		$types = $model->groupBy('relationship_type')->select('relationship_type', DB::raw('count(*) as total'))->get();

		$type['like'] = 0;
		$type['updates'] = 0;
		$type['help'] = 0;

		$stats['types'] = $type;

		foreach ($types as $type) {

			$stats['types'][$type->relationship_type] = $type->total;

		};

		return $stats;

	}


}