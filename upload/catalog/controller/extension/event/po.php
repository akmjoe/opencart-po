<?php
class controllerExtensionEventPo extends Controller {
	public function clean(&$route, &$data, &$output = null) {
		if (!empty($this->request->get['token']) || $route != 'account/login') {
			unset($this->session->data['po_number']);
		}
	}
	
    public function payment(&$route, &$data, &$output) {
        if($this->active('payment')) {
            $output = $this->load->view('extension/module/po_payment', $data).$output;
        }
    }
    
    public function shipping(&$route, &$data, &$output) {
        if($this->active('shipping')) {
            $output = $this->load->view('extension/module/po_payment', $data).$output;
        }
    }
    
    public function before_view(&$route, &$data, &$output) {
        // check if this module is enabled
        if(!$this->config->get('module_po_status')) {
            return;
        }
        // load required parameters
		$this->load->language('extension/module/po');
        
        if($this->config->get('module_po_blind')) {
            $data['text_blind'] = $this->language->get('text_blind');
        }
        $data['text_po'] = ($this->required?$this->language->get('text_por'):$this->language->get('text_poo'));
        $data['blind'] = (isset($this->session->data['blind']) && $this->session->data['blind']?1:0);
        $data['po_number'] = (isset($this->session->data['po_number'])?$this->session->data['po_number']:'');
        $data['module_po_page'] = $this->config->get('module_po_page');
    }
    
    public function save(&$route, &$data) {
	    if($this->active()) {
		    if(isset($this->request->post['po_number']))
			    $this->session->data['po_number'] = $this->request->post['po_number'];
		    if(isset($this->request->post['blind'])) {
			    $this->session->data['blind'] = $this->request->post['blind'];
                    } elseif(isset($this->request->post['blind_flag'])) {
				unset($this->session->data['blind']);
                    }
	    }
    }
    
    public function order(&$route, &$data, &$output = null) {
	    if($this->active() && isset($this->session->data['po_number'])) {
		    // save po to order
		    $this->db->query('update '.DB_PREFIX.'order set po_number="'.$this->db->escape($this->session->data['po_number']).'", blind='.(isset($this->session->data['blind'])?(int)$this->session->data['blind']:0).'  where order_id='.((int)$output?(int)$output:(int)$data[0]));
	    }
    }
    
    public function confirm(&$route, &$data) {
	    if($this->active() && isset($this->session->data['po_number'])) {
		    // make po number available for template
		    $this->load->language('extension/module/po');
		    $data['po_number'] = $this->session->data['po_number'];
	    }
    }
	
	public function history(&$route, &$data, &$output = null) {
		$this->load->model('extension/module/po');
		if(isset($data['orders'])) {
			foreach($data['orders'] as &$order) {
				$order['po_number'] = $po = $this->model_extension_module_po->getPoNumber($order['order_id']);
			}
		}
		if(isset($data['order_id'])) {
			$data['po_number'] = $po = $this->model_extension_module_po->getPoNumber($data['order_id']);
		}
		$this->load->language('extension/module/po');
		$data['text_po_number'] = $this->language->get('text_po');
		$data['column_po'] = $this->language->get('column_po');
	}
    
    protected function active($page = 'ignore') {
	    if($this->config->get('module_po_status')) {
		    switch(true) {
			    case $page == 'ignore':// override page
			    case $this->config->get('module_po_page') == 'both':
			    case $page == $this->config->get('module_po_page'):
				    return true;
			    default :
				    return false;
				    
		    }
	    }
	    return false;
    }
    
    protected function required() {
	    // checks if po number required for this customer
	    return false;
    }
}
