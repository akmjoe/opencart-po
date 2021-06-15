<?php
class controllerExtensionEventPo extends Controller {
	public function clean(&$route, &$data, &$output = null) {
		if (!empty($this->request->get['token']) || $route != 'account/login') {
			unset($this->session->data['po_number']);
		}
	}
	
    public function payment(&$route, &$data, &$output) {
        // check if this module is enabled
        if(!$this->active('payment')) {
            return;
        }
		$this->load->language('extension/module/po');
		// build blind checkbox
		$blind_box = '';
		if($this->config->get('module_po_blind')) {
			$blind_box = '<div><label for="blind"><input type="checkbox" name="blind" value="1"'.
				(isset($this->session->data['blind']) && $this->session->data['blind']?' checked="checked"':'').
				'><strong> '.$this->language->get('text_blind').
				' </strong></label>'.
				(isset($this->session->data['po_number'])?$this->session->data['po_number']:'').'</textarea></div>';
		}
		// add PO number field
		$html = new simple_html_dom();
        $html->load($output, $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);
		foreach($html->find('div.buttons') as $node) {
			$node->outertext = '<div><label for="po-number"><strong>'.($this->required?$this->language->get('text_por'):$this->language->get('text_poo')).
				' </strong></label><br><textarea name="po_number" id="po-number" rows="1">'.
				(isset($this->session->data['po_number'])?$this->session->data['po_number']:'').'</textarea></div>'.
				$blind_box .
				$node->outertext;
		}
		$output = $html->save();
		$this->response->setOutput($html->save());
    }
    
    public function shipping(&$route, &$data, &$output) {
        // check if this module is enabled
        if(!$this->active('shipping')) {
            return;
        }
		$this->load->language('extension/module/po');
		// build blind checkbox
		$blind_box = '';
		if($this->config->get('module_po_blind')?'':'') {
			$blind_box = '<div><label for="blind"><input type="checkbox" name="blind" value="1"'.
				(isset($this->session->data['blind']) && $this->session->data['blind']?' checked="checked"':'').
				'><strong>'.$this->language->get('text_blind').
				' </strong></label>'.
				(isset($this->session->data['po_number'])?$this->session->data['po_number']:'').'</textarea></div>';
		}
		// add PO number field
		$html = new simple_html_dom();
        $html->load($output, $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);
		foreach($html->find('div.buttons') as $node) {
			$node->outertext = '<div><label for="po-number"><strong>'.($this->required?$this->language->get('text_por'):$this->language->get('text_poo')).
				' </strong></label><br><textarea name="po_number" id="po-number" rows="1">'.
				(isset($this->session->data['po_number'])?$this->session->data['po_number']:'').'</textarea></div>'.
				$blind_box .
				$node->outertext;
		}
		$output = $html->save();
    }
    
    public function save(&$route, &$data) {
	    if($this->active()) {
		    if(isset($this->request->post['po_number']))
			    $this->session->data['po_number'] = $this->request->post['po_number'];
		    if(isset($this->request->post['blind'])) {
			    $this->session->data['blind'] = $this->request->post['blind'];
			} else {
				unset($this->session->data['blind']);
			}
	    }
    }
    
    public function order(&$route, &$data, &$output = null) {
	    if($this->active() && isset($this->session->data['po_number'])) {
		    // save po to order
		    $this->db->query('update '.DB_PREFIX.'order set po_number="'.$this->db->escape($this->session->data['po_number']).'", blind='.(int)$this->session->data['blind'].'  where order_id='.((int)$output?(int)$output:(int)$data[0]));
	    }
    }
    
    public function confirm(&$route, &$data) {
	    if($this->active() && $this->session->data['po_number']) {
		    // make po number available for template
		    $this->load->language('extension/module/po');
		    $data['po_number'] = $this->session->data['po_number'];
	    }
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
