<?php

class ControllerExtensionModuleViewed extends Controller {

    public function index($setting) {
        $this->load->language('extension/module/viewed');

        
      
        $this->session->data['product_viewed'] = isset($this->session->data['product_viewed']) ? $this->session->data['product_viewed'] : [];

        if(isset($this->request->get['product_id'])) {
            $product_id = (int) $this->request->get['product_id'];
        } else {
            $product_id = 0;
        }


        if(!isset($this->session->data['product_viewed'][$product_id]) && $product_id) {
            $this->session->data['product_viewed'][$product_id] = $product_id; 
        }

        $data['heading_title'] = $setting['name'];
       

        $products = $this->session->data['product_viewed'];
        unset($products[$product_id]);

        foreach ($products as $product_id) {
            $result = $this->model_catalog_product->getProduct($product_id);

            if($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
            }

            if($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            if((float) $result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $special = false;
            }

            if($this->config->get('config_tax')) {
                $tax = $this->currency->format((float) $result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
            } else {
                $tax = false;
            }

            if($this->config->get('config_review_status')) {
                $rating = (int) $result['rating'];
            } else {
                $rating = false;
            }
            $attributes_groups = $this->model_catalog_product->getProductAttributes($result['product_id']);
            $attributes        = [];
            foreach ($attributes_groups as $group) {
                if($group['attribute_group_id'] == $attribute_group) {
                    foreach ($group['attribute'] as $attribute) {
                        $attributes[] = $attribute;
                    }
                }
            }

            $data['products'][] = array(
                'attributes'  => $attributes,
                'product_id'  => $result['product_id'],
                'thumb'       => $image,
                'name'        => $result['name'],
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'price'       => $price,
                'special'     => $special,
                'percent'     => $result['special'] ? round((1 - $result['special'] / $result['price']) * 100, 0) : 0,
                'tax'         => $tax,
                'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                'rating'      => $result['rating'],
                'reviews'     => $result['reviews'],
                'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'])
            );
        }





        return $this->load->view('extension/module/viewed', $data);
    }

}
