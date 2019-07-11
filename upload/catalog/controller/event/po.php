<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ControllerEventPo extends Controller {
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
	// add PO number field
	$this->load->helper('simple_html_dom');
	$html = str_get_html($output);
	foreach($html->find('div.buttons') as $node) {
		$node->outertext = '<div><label for="po-number"><strong>'.($this->required?$this->language->get('text_por'):$this->language->get('text_poo')).
		    ' </strong></label><br><textarea name="po_number" id="po-number" rows="1">'.
		    (isset($this->session->data['po_number'])?$this->session->data['po_number']:'').'</textarea></div>'.
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
	// add PO number field
	$this->load->helper('simple_html_dom');
	$html = str_get_html($output);
	foreach($html->find('div.buttons') as $node) {
		$node->outertext = '<div><label for="po-number"><strong>'.($this->required?$this->language->get('text_por'):$this->language->get('text_poo')).
		    ' </strong></label><br><textarea name="po_number" id="po-number" rows="1">'.
		    (isset($this->session->data['po_number'])?$this->session->data['po_number']:'').'</textarea></div>'.
		    $node->outertext;
	}
	$output = $html->save();
    }
    
    public function save(&$route, &$data) {
	    if($this->active()) {
		    if(isset($this->request->post['po_number']))
			    $this->session->data['po_number'] = $this->request->post['po_number'];
	    }
    }
    
    public function order(&$route, &$data, &$output = null) {
	    if($this->active() && isset($this->session->data['po_number'])) {
		    // save po to order
		    $this->db->query('update '.DB_PREFIX.'order set po_number="'.$this->db->escape($this->session->data['po_number']).'" where order_id='.((int)$output?(int)$output:(int)$data[0]));
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
