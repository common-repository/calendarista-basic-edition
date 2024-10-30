<?php
class Calendarista_StagingRepository extends Calendarista_RepositoryBase{
	private $wpdb;
	private $staging_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->staging_table_name = $wpdb->prefix . 'calendarista_staging';
	}
	public function read($uniqueId){
		$sql = "SELECT * FROM $this->staging_table_name WHERE id = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $uniqueId) );
		if($result){
			$viewState = $result[0]->viewState;
			if (base64_decode($viewState, true) !== false)
			{
				$viewState = base64_decode($viewState);
			}
			$result[0]->viewState = $viewState;
			return $result[0];
		}
		return false;
	}
	public function insert($viewState){
		$id = uniqid(sprintf('calendarista_%s_', time()));
		$today = new Calendarista_DateTime();
		$result = $this->wpdb->insert($this->staging_table_name,  array(
			'id'=>$id
			, 'viewState'=>base64_encode($viewState)
			, 'entryDate'=>$today->format(CALENDARISTA_FULL_DATEFORMAT)
		), array('%s', '%s', '%s'));

		if($result !== false){
			return $id;
		}
		return $result;
	}
	public function update($args){
		$result = $this->wpdb->update($this->staging_table_name, array('viewState'=>base64_encode($args['viewState'])), array('id'=>$args['id']), array('%s'));
		return $result;
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->staging_table_name WHERE id = %s", $id));
	}
	public function deleteByDaysOld($days = 3){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->staging_table_name WHERE entryDate < DATE_SUB(NOW(), INTERVAL %d DAY)", $days));
	}
	public function deleteAll(){
		return $this->wpdb->query("DELETE FROM $this->staging_table_name");
	}
}
?>