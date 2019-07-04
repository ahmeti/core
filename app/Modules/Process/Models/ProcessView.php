<?php

namespace App\Modules\Process\Models;

use App\Core;
use App\Modules\Core\Classes\ModelView;
use Illuminate\Support\Facades\DB;

class ProcessView extends ModelView
{
    protected $db;
    protected $req = [];
    protected $_Columns = [
        '*'
    ];
    protected $_OrderColumn = 'id';
    protected $_OrderType = 'desc';
    protected $_PerPage = 20;

    public function __construct($softDelete = true)
    {
        $this->db = DB::table('view_processes')
            ->where('company_id', Core::companyId());

        if($softDelete){
            $this->db->whereNull('deleted_at');
        }
    }

    protected function filter()
    {
        if ( ! empty($this->req['id']) ){
            $this->db->where('id', $this->req['id']);
        }
    }

}