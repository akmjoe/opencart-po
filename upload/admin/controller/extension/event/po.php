<?php
class controllerExtensionEventPo extends Controller {
	public function index(&$route, &$data, &$output) {
		if(!$this->config->get('module_po_status')) return;
		$po = '';
		if(isset($data['order_id'])) {
			$result = $this->db->query('select po_number from '.DB_PREFIX.'order where order_id = '.(int)$data['order_id']);
			$po = (isset($result->row['po_number'])?$result->row['po_number']:'');
		}
		$this->load->language('extension/module/po');
		// add PO number field
		$html = new simple_html_dom();
        $html->load($output, $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);
		if($html->find('div#tab-payment',0)) {
			$html->find('div#tab-payment', 0)->find('div.form-group', -1)->outertext .= 
				'<div class="form-group"><label class="col-sm-2 control-label" for="po-number">'.$this->language->get('text_po').
				'</label><div class="col-sm-10"><input type="text" name="po_number" id="input-payment-po-number" class="form-control" value="'.
				$po.'"></div></div>';
		}
		$output = $html->save();
	}
	
	public function save(&$route, &$data) {
	    if(!$this->config->get('module_po_status'))  return;
		if(isset($this->request->post['po_number']))
			$this->db->query('update '.DB_PREFIX.'order set po_number="'.$this->db->escape($this->request->post['po_number']).'" where order_id='.(int)$this->session->data['order_id']);
    }
	
	public function order(&$route, &$data, &$output) {
		$this->language->load('extension/module/po');
		$po = $this->db->query('select * from '.DB_PREFIX.'order where order_id = "'.(int)$this->request->get['order_id'].'"');
		$html = new simple_html_dom();
        $html->load($output, $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);
		if($po->num_rows && $po->row['po_number']) {
			$insert = '<tr><td><button type="button" data-toggle="tooltip" title="'.$this->language->get('text_po').'" class="btn btn-info btn-xs"><i class="fa fa-info fa-fw"></i></button></td>';
			$insert .= '<td>'.$this->language->get('text_po').' '.$po->row['po_number'].'</td></tr>';
			if($html->find('div.col-md-4', 0)) {
				$html->find('div.col-md-4', 0)->find('tbody',0)->innertext .= $insert;
			}
		}
		$output = $html->save();
	}
}
