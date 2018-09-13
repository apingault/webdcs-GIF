<?php 
class Chamber { 
	
    public $id = -1; // -1 if new
	public $name;
	public $comments;
	public $enabled;
    public $area; 
	public $dimensions;
	public $TDCMapping;
	public $gaps;
	public $partitions;
	public $daq; // NONE, DEFAULT, HARDROC
	
	
	private $gapIds;
	
	
	function __construct() {
		
		
	}
	
	function save() {
		
		if($id == -1) {
			
			
		}
		else {
			
		}
		
	}
} 
