<?php
class Calendarista_TagsController extends Calendarista_BaseController{
	private $repo;
	private $tag;
	public function __construct($createCallback = null, $updateCallback = null, $deleteCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'tags')){
			return;
		}
		if(!Calendarista_PermissionHelper::allowAccess()){
			exit();
		}
		if(!Calendarista_NonceHelper::valid()){
			return;
		}
		$this->tag = new Calendarista_Tag(array(
			'name'=>isset($_POST['name']) ? sanitize_text_field($_POST['name']) : null,
			'tagId'=>isset($_POST['tagId']) ? (int)$_POST['tagId'] : null,
			'id'=>isset($_POST['id']) ? (int)$_POST['name'] : null
		));
		$this->repo = new Calendarista_TagsRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		if(array_key_exists('calendarista_delete_tags', $_POST)){
			$this->delete($deleteCallback);
		}else if(array_key_exists('calendarista_save_tag_list', $_POST)){
			$this->saveTagList();
		}
	}
	public function saveTagList(){
		$availabilityId = (int)$this->getPostValue('availabilityId');
		$tags = explode(',', $this->getPostValue('tags'));
		foreach($tags as $t){
			$keyValuePair = explode(':', $t);
			$tagId = (int)$keyValuePair[0];
			$selected = boolval($keyValuePair[1]);
			$result2 = $this->repo->readTagAvailability((int)$keyValuePair[0], $availabilityId);
			if($result2 && !$selected){
				$this->repo->deleteFromTagAvailability($tagId, $availabilityId);
			}else if(!$result2 && $selected){
				$result = $this->repo->insertTagAvailability($tagId, $availabilityId);
			}
		}
	}
	public function create($callback){
		$names = explode(',', $this->getPostValue('name'));
		$result = null;
		foreach($names as $n){
			$name = trim($n);
			if(!$name){
				continue;
			}
			$result = $this->repo->insert(new Calendarista_Tag(array('name'=>trim($name))));
		}
		$this->executeCallback($callback, array($result));
	}
	public function update($callback){
		$result = $this->repo->update($this->tag);
		$this->executeCallback($callback, array($result));
	}
	public function delete($callback){
		$tags = isset($_POST['tags']) ? (array)$_POST['tags'] : null;
		if(!$tags){
			$id = (int)$this->getPostValue('calendarista_delete');
			$tags = array($id ? $id : $this->tag->id);
		}
		$result = false;
		foreach($tags as $tagId){
			$result = $this->repo->delete((int)$tagId);
			if(!$result){
				break;
			}
		}
		$this->executeCallback($callback, array($result));
	}
}
?>