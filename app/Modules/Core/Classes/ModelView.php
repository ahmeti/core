<?php

namespace App\Modules\Core\Classes;

class ModelView
{
    protected $db;
    protected $req = [];
    protected $_Columns = [];
    protected $_OrderColumn = 'id';
    protected $_OrderType = 'desc';
    protected $_PerPage = 20;

    public function getDb()
    {
        return $this->db;
    }

    protected function isExport()
    {
        if ( ! empty($this->req['export']) &&  $this->req['export'] === 'excel' ){
            return true;
        }
        return false;
    }

    public function request(array $req)
    {
        $this->req = $req;
        return $this;
    }

    public function select($columns = [])
    {
        if ( ! empty($columns) ){
            $selects = $columns;
        }elseif ( $this->isExport() ){
            $selects = ['*'];
        }else{
            $selects = $this->_Columns;
        }

        $this->db->select($selects);

        return $this;
    }

    public function orderBy($column = null, $type = null)
    {
        if ( ! empty($column) ) {
            $orderColumn = $column;
        }elseif( ! empty($this->req['orderByCol']) ){
            $orderColumn = $this->req['orderByCol'];
        }else{
            $orderColumn = $this->_OrderColumn;
        }

        if ( ! empty($type) ) {
            $orderType = $type;
        }elseif( ! empty($this->req['orderByType']) && in_array($this->req['orderByType'], ['asc', 'desc']) ){
            $orderType = $this->req['orderByType'];
        }else{
            $orderType = $this->_OrderType;
        }

        $this->db->orderBy($orderColumn, $orderType);

        return $this;
    }

    public function find($id)
    {
        return $this->db->where('Id', $id)->first();
    }

    public function get()
    {
        return $this->db->get();
    }

    public function result()
    {
        # $this->customize();

        $this->filter();
        $this->select();
        $this->orderBy();

        if ( $this->isExport() ){
            return $this->db->get();
        }
        return $this->db->paginate($this->_PerPage);
    }
}