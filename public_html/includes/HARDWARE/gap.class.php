<?php 
class Gap { 
	
	
    public $id = -1; // -1 if new
	public $name;
	public $chamberId;
	public $comments;
    public $area;
	public $dimensions;
	public $TDCMapping;
	public $gaps;
	public $partitions;
	
	public $status;
	public $i0;
	public $HV_WP;
	public $HV_STBY;
	
	public $CAEN_SLOT;
	public $CAEN_CH;
	public $ADC_SLOT;
	public $ADC_CH;
	
	
	function __construct() {
		
		
	}
} 
