<?php

class ModelExtensionModulePo extends Model {
    
	public function getPoNumber($order_id = null) {
		$result = $this->db->query("select po_number from ".DB_PREFIX."order where order_id = '".(int)$order_id."'");
		return $result->row['po_number'];
	}
}