<?php
class controllerExtensionEventPo extends Controller {
	public function index(&$route, &$data, &$output) {
		if(!$this->config->get('module_po_status')) {
                    return;
                }
                //$this->log->write($data);
                $this->load->model('extension/module/po');
                $data['po_number'] = $this->model_extension_module_po->getPoNumber($data['order_id']);
                
		$this->load->language('extension/module/po');
                $data['text_po'] = $this->language->get('text_po');
                
                $data['header'] .= $this->load->view('extension/module/po_edit', $data);
                
                return;
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
                if(!$this->config->get('module_po_status')) {
                    return;
                }
		if(isset($this->request->post['po_number'])) {
			$this->db->query('update '.DB_PREFIX.'order set po_number="'.$this->db->escape($this->request->post['po_number']).'" where order_id='.(int)$this->session->data['order_id']);
                }
        }
	
	public function order(&$route, &$data, &$output) {
                if(!$this->config->get('module_po_status')) {
                    return;
                }
		$this->language->load('extension/module/po');
                $this->load->model('extension/module/po');
                $data['po_number'] = $this->model_extension_module_po->getPoNumber($this->request->get['order_id']);
                
                $data['date_added'] .= $this->load->view('extension/module/po_order', $data);
	}
}
