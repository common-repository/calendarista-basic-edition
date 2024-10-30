<?php
class Calendarista_BookingSearchResultTmpl extends Calendarista_TemplateBase{
	public $searchList;
	public function __construct(){
		parent::__construct();
		$this->searchList = new Calendarista_SearchResultList();
		$this->searchList->bind();
		$this->render();
	}
	public function render(){
	?>
		<?php $this->searchList->display(); ?>
<?php
	}
}