<?php

namespace Karolina\Tag;

Class TagRepository {

	public function save (Tag $tag) {

		// First we must save the tag model to get an ID
		if ($tag->getId() == NULL) { 

			// Has not been saved before
			$model = new \Karolina\Database\Table\Tag;

		} else {

			$model = \Karolina\Database\Table\Tag::findOrFail($tag->getId());

		}

		$model->label_language = json_encode($tag->getLabelLanguageFields());
		$model->active = $tag->getEnabled();
		$model->type = $tag->getType();
		$model->types = json_encode($tag->getTypes());

		$model->platform = getenv('platform_key');

		$model->save();

		return $model->tag_id;

	}

	public function addTagToEntity (int $tagId, $entityType, int $entityId, $tagType = null) {

		// Check if a record already exists with the same parameters
		$existingRecord = \Karolina\Database\Table\TagsToEntities::where('tag_id', $tagId)
			->where('entity_type', $entityType)
			->where('entity_id', $entityId)
			->where('tag_type', $tagType)
			->first();
			
		// If record exists, return early without creating a new one
		if ($existingRecord) {
			return;
		}

		$model = new \Karolina\Database\Table\TagsToEntities;

		$model->tag_id = $tagId;
		$model->entity_type = $entityType;
		$model->entity_id = $entityId;
		$model->active = 1;
		$model->tag_type = $tagType;

		$model->platform = getenv('platform_key');

		$model->save();

	}

	public function removeTagFromEntity (int $tagId, $entityType, int $entityId, $tagType = null) {

		\Karolina\Database\Table\TagsToEntities::where('tag_id', $tagId)
			->where('entity_type', $entityType)
			->where('entity_id', $entityId)
			->where('tag_type', $tagType)
			->delete();
	}
	

	public function getById ($id) {

		$model = \Karolina\Database\Table\Tag::findOrFail($id);

		$tag = new Tag();

		$tag->setId($id);
        $tag->setEnabled($model->active);
        $tag->setType($model->type);
		$tag->setLabelLanguageFields(json_decode($model->label_language, true));
		$tag->setTypes(json_decode($model->types, true));

		return $tag;

	}

	public function getAll ($type = false) {

		$models = \Karolina\Database\Table\Tag::all();

        if ($type) {
            $models = $models->where('type', $type);
        }

		$tags = array();
		foreach ($models as $model) {

		    if ($model->active == true) {
                $tag = new Tag();
                $tag->setLabelLanguageFields(json_decode($model->label_language, true));
                $tag->setId($model->tag_id);
                $tag->setEnabled($model->active);
                $tag->setType($model->type);
				$tag->setTypes(json_decode($model->types, true));
                $tags[] = $tag;
            }

		}

		return $tags;

	}

	public function getTagsFromTagIdCollection ($tagIdCollection) {

		$tags = array();

		foreach ($tagIdCollection as $tagId) {

			$tag = $this->getById($tagId);
			$tags[] = $tag;
		}

		return $tags;

	}

}