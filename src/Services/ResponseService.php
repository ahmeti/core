<?php

namespace Ahmeti\Core\Services;

use App\Core;

class ResponseService {

    private $_status; # status
    private $_message; # msg
    private $_errorName; # ename
    private $_goUrl; # gourl
    private $_setData; # setdata
    private $_data; # data
    private $_hedef; # hedef
    private $_hedef2; # hedef2
    private $_output; # output
    private $_baseId; # baseid
    private $_hideModal; # hidemodal

    private $_title; # breadcrumb
    private $_breadcrumb; # breadcrumb
    private $_body; # body
    private $_footer; # footer
    private $_jsCode; # jscode
    private $_jsFiles; # jsfile
    private $_jsCallbacks; # callback


    public function status($status)
    {
        $this->_status = $status;
        return $this;
    }

    public function message($message)
    {
        $this->_message = $message;
        return $this;
    }

    public function errorName($errorName)
    {
        $this->_errorName = $errorName;
        return $this;
    }

    public function goUrl($goUrl)
    {
        $this->_goUrl = $goUrl;
        return $this;
    }

    public function setData($setData)
    {
        $this->_setData = $setData;
        return $this;
    }

    public function data($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function hedef($hedef)
    {
        $this->_hedef = $hedef;
        return $this;
    }

    public function hedef2($hedef2)
    {
        $this->_hedef2 = $hedef2;
        return $this;
    }

    public function output($output)
    {
        $this->_output = $output;
        return $this;
    }

    public function baseid($baseId)
    {
        $this->_baseId = $baseId;
        return $this;
    }

    public function hideModal($hideModal)
    {
        $this->_hideModal = $hideModal;
        return $this;
    }

    # ------------------------------


    public function title($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function breadcrumb($breadcrumb)
    {
        $this->_breadcrumb = $breadcrumb;
        return $this;
    }

    public function body($body)
    {
        $this->_body = $body;
        return $this;
    }

    public function footer($footer)
    {
        $this->_footer = $footer;
        return $this;
    }

    public function jsCode($jsCode)
    {
        $this->_jsCode = $jsCode;
        return $this;
    }

    public function jsFiles(array $jsFiles)
    {
        $this->_jsFiles = $jsFiles;
        return $this;
    }

    public function jsCallbacks(array $jsCallbacks)
    {
        $this->_jsCallbacks = $jsCallbacks;
        return $this;
    }

    public function get()
    {
        return response()->json([

            'status'    => $this->_status === true ? 'yes' : 'no', // From kontrolü için. (yes | no)
            'msg'       => $this->_message,
            'ename'     => $this->_errorName,
            'gourl'     => $this->_goUrl,
            'setdata'   => $this->_setData,
            'data'      => $this->_data,
            'hedef'     => $this->_hedef ? $this->_hedef : request('hedef'),
            'hedef2'    => $this->_hedef2 ? $this->_hedef2 : request('hedef2'),
            'output'    => $this->_output ? $this->_output : request('output'),
            'baseid'    => $this->_baseId ? $this->_baseId : request('baseid'),
            'hidemodal' => $this->_hideModal ? $this->_hideModal : request('hidemodal'),

            'title'      => $this->_title, // Title
            'breadcrumb' => Core::getBreadcrumb(),
            'body'       => $this->_body,
            'footer'     => $this->_footer,
            'jscode'     => $this->_jsCode,
            'jsfile'     => $this->_jsFiles, // Array
            'callback'   => $this->_jsCallbacks, // Array

        ]);
    }

    public function simple()
    {
        return response()->json([

            'status'    => (bool)$this->_status === true,
            'message'   => $this->_message,
            'data'      => $this->_data,
        ]);
    }
}