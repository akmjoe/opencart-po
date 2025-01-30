<?php

class ModelExtensionModulePo extends Model {
    
	public function getPoNumber($order_id = null) {
		if(!(int)$order_id) {
			$order_id = isset($this->session->data['order_id'])?$this->session->data['order_id']:0;
		}
		$result = $this->db->query("select po_number from ".DB_PREFIX."order where order_id = '".(int)$order_id."'");
		return $result->row['po_number'];
	}
}