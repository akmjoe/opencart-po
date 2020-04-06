<?php
class ControllerExtensionModulePo extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/po');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_po', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/po', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/po', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		
		if (isset($this->request->post['module_po_page'])) {
			$data['module_po_page'] = $this->request->post['module_po_page'];
		} else {
			$data['module_po_page'] = $this->config->get('module_po_page');
		}
		
		$data['pages'] = array(
			array('page'=>'shipping', 'name'=>$this->language->get('text_shipping')),
			array('page'=>'payment', 'name'=>$this->language->get('text_payment')),
			array('page'=>'both', 'name'=>$this->language->get('text_both')),
		);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/po', $data));
	}
	
	public function install() {
		// add db field for po number
		$fields = $this->db->query('DESCRIBE `'.DB_PREFIX.'order`');
		$po = false;
		foreach($fields->rows as $row) {
			if($row['Field'] == 'po_number') {
				$po = true;
			}
		}
		if(!$po) {
			$this->db->query('ALTER TABLE `'.DB_PREFIX.'order` ADD `po_number` varchar(24)');
		}
		// now add triggers for admin to edit
		$this->load->model('setting/event');
		$this->model_setting_event->addEvent('po','admin/view/sale/order_form/after','extension/event/po/index');
		$this->model_setting_event->addEvent('po','admin/view/sale/order_info/after','extension/event/po/order');
		// add triggers for checkout to show field
		$this->model_setting_event->addEvent('po','catalog/view/checkout/payment_method/after','extension/event/po/payment');
		$this->model_setting_event->addEvent('po','catalog/view/checkout/shipping_method/after','extension/event/po/shipping');
		// add triggers to save to session
		$this->model_setting_event->addEvent('po','catalog/controller/checkout/payment_method/save/after','extension/event/po/save');
		$this->model_setting_event->addEvent('po','catalog/controller/checkout/shipping_method/save/after','extension/event/po/save');
		$this->model_setting_event->addEvent('po','catalog/controller/api/payment/address/after','extension/event/po/save');
		// add triggers to save with order
		$this->model_setting_event->addEvent('po','catalog/model/checkout/order/addOrder/after','extension/event/po/order');
		$this->model_setting_event->addEvent('po','catalog/model/checkout/order/editOrder/after','extension/event/po/order');
		// add trigger to show with order confirmation
		$this->model_setting_event->addEvent('po','catalog/view/checkout/confirm/before','extension/event/po/confirm');
		// cleanup after order place/logout
		$this->model_setting_event->addEvent('po', 'catalog/controller/checkout/success/after', 'extension/event/po/clean');
		$this->model_setting_event->addEvent('po', 'catalog/controller/account/logout/after', 'extension/event/po/clean');
		$this->model_setting_event->addEvent('po', 'catalog/controller/account/login/after', 'extension/event/po/clean');
	}

	public function uninstall() {
		// remove evant triggers
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('po');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/po')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}