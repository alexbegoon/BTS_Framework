<?php

class BTS_View_Helper_Breadcrumbs extends Zend_View_Helper_Placeholder_Container_Standalone {
    
    protected $_crumbs = array();
    
    public function breadcrumbs() {
        return $this;
    }
    
    public function toString() {
        $html = "";
        if (count($this->_crumbs) > 1) {
            $html = '<ul class="breadcrumbs">';
            $count = 0;
            foreach ($this->_crumbs as $crumb) {
                $count++;
                $html .= '<li>';
                if (!is_null($crumb['link']) && $count < count($this->_crumbs)) {
                    if (is_array($crumb['link'])) {
                        $link = $this->view->url($crumb['link']);
                    }
                    else {
                        $link = $this->view->serverUrl($crumb['link']);
                    }
                    $html .= '<a href="' . $link . '" title="' . $crumb['title'] . '">';
                }
                $html .= $crumb['title'];
                if (!is_null($crumb['link']) && $count < count($this->_crumbs)) {
                    $html .= '</a>';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }
    
    public function addBreadcrumb($title, $link = null) {
        $crumb = array("title" => $title, "link" => $link);
        //array_push($this->_crumbs, $crumb);
        return $this;
    }
    
}
