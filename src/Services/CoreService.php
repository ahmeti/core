<?php

namespace Ahmeti\Core\Services;

# use App\Models\ExchangeRate;
# use App\Models\Log;
use App\Modules\Company\Models\Company;
use App\Modules\Page\Models\Page;
use App\Modules\Status\Models\Status;
use App\Modules\User\Models\Permission;
# use App\Modules\UserStatusLog\Models\UserStatusLog;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPExcel_Shared_Date;

class CoreService {

    private $breadcrumb = [];
    private $companyData;
    private $statuses = [];

    private function getUnique()
    {
        $uniqid = uniqid();
        $random = str_random(13);

        $uniqidSplit = str_split($uniqid);
        $randomSplit = str_split($random);

        $unique = '';

        for ($i=0; $i < 13; $i++){
            $unique .= $randomSplit[$i].''.$uniqidSplit[$i];
        }

        return strtolower(str_random(6).$unique);
    }

    public function preFile($folder, $ext = null)
    {
        $dateY = date('Y');
        $dateM = date('m');
        $dateD = date('d');

        $dir = storage_path('app/'.$folder.'/'.$this->companyId().'/'.$dateY.'/'.$dateM.'/'.$dateD);

        if ( ! is_dir($dir) ){
            File::makeDirectory($dir, $mode = 0775, true, true);
        }

        return $folder.'/'.$this->companyId().'/'.$dateY.'/'.$dateM.'/'.$dateD.'/'.$this->getUnique().'.'.strtolower($ext);
    }

    public function isDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function date($date, $fromFormat, $format)
    {
        if($date instanceof Carbon){
            $date = $date->format($fromFormat);
        }

        if ( $this->isDate($date, $fromFormat ) ){
            if ( str_is('*%*', $format) ){
                return Carbon::createFromFormat($fromFormat, $date)->formatLocalized($format);
            }
            return Carbon::createFromFormat($fromFormat, $date)->format($format);
        }
        return null;
    }

    private function enums($key)
    {
        if( empty($this->statuses) ){

            $statuses = DB::table((new Status)->getTable())
                ->select(['name', 'key', 'value', 'icon', 'color as icon-color'])
                ->get()
                ->toArray();

            foreach ($statuses as &$status){
                $status = (array)$status;
            }

            $this->statuses = collect($statuses)->groupBy('name')->toArray();

            foreach ($this->statuses as &$status){
                foreach ($status as &$item){
                    unset($item['name']);
                }
            }
        }

        if( array_key_exists($key ,$this->statuses) ){
            return $this->statuses[$key];
        }

        return [];
    }

    public function enumsValue($key)
    {
        $enums = $this->enums($key);

        $newEnums = [];
        foreach ((array)$enums as $v) {
            $newEnums[$v['key']] = $v['value'];
        }
        return $newEnums;
    }

    public function enumsSelect($key)
    {
        $enums = $this->enums($key);

        $newEnums = [];
        foreach ((array)$enums as $v) {
            $newEnums[] = ['id' => $v['key'], 'value' => $v['value']];
        }
        return $newEnums;
    }

    public function enumsHtml($key)
    {
        $enums = $this->enums($key);

        $newEnums = [];
        foreach ((array)$enums as $v) {
            if( ! empty($v['html']) ){
                $newEnums[$v['key']] = $v['html'];
            }else{
                $newEnums[$v['key']] = '<i '.(empty($v['icon-color']) ? '' : 'style="color:'.$v['icon-color'].';" ').'class="'.$v['icon'].'"></i> '.$v['value'];
            }
        }
        return $newEnums;
    }

    public function companyId()
    {
        return (int)session('company_id');
    }

    public function userId()
    {
        return (int)session('user_id');
    }

    public function company()
    {
        if( $this->companyData ){
            return $this->companyData;
        }
        $this->companyData = Company::query()->first();
        return $this->companyData;
    }

    public function userFullName()
    {
        if( auth()->user() ){
            return auth()->user()->name;
        }
        return null;
    }

    public function isAdmin()
    {
        if ( auth()->user() && auth()->user()->authority === 'admin' ){
            return true;
        }
        return false;
    }

    public function isRep()
    {
        if ( auth()->user() && auth()->user()->authority === 'rep' ){
            return true;
        }
        return false;
    }

    public function checkPermission($pageId)
    {
        if ( $this->isAdmin() ){
            return true;
        }

        if ( empty($pageId) ){
            abort(403, 'Permission-Error.');
        }

        $permission = Permission::where('user_id', $this->userId())
            ->where('page_id', $pageId)
            ->first();

        if ( is_null($permission) ){
            abort(403, 'Permission-Error.');
        }

        return true;
    }

    public function getRestriction($pageId)
    {
        if ( $this->isAdmin() ){
            return false;
        }

        if ( empty($pageId) ){
            return true;
        }

        $permission = Permission::where('user_id', $this->userId())
            ->where('page_id', $pageId)
            ->first();

        if ( is_null($permission) ){
            return false;
        }

        return true;
    }

    public function addBreadcrumb($title, $url=null, $icon=null, $class='ajaxPage')
    {
        if (is_null($url)){
            $url = 'javascript:void(0)';
            $class = '';
        }
        $this->breadcrumb[] = '<li><i class="fa '.$icon.' fa-fw"></i> <a class="'.$class.'" href="'.$url.'">'.$title.'</a></li>';
    }

    public function getBreadcrumb()
    {
        if ( $this->isMobile() ){
            return '<div class="row" style="height:15px"></div>';
        }

        $out='<!-- Page Heading -->';
        $out.='<div class="row"><div class="col-sm-12"><ol class="breadcrumb" style="color:#337ab7">
        <li>
            <i class="fa fa-home fa-fw"></i> <a class="ajaxPage" href="'.route('home').'">Homepage</a>
        </li>';
        foreach ($this->breadcrumb as $b) {
            $out .= $b;
        }
        $out.='</ol></div></div><!-- row -->';
        return $out;
    }

    public function getUserMenu()
    {
        $userId = $this->userId();
        $companyID = $this->companyId();
        $permissionTable = with(new Permission())->getTable();
        $pageTable = with(new Page())->getTable();
        $items = Page::query()
            ->leftJoin($permissionTable.' AS p', function ($join) use ($companyID, $pageTable, $permissionTable){
                $join->on('p.page_id', '=', $pageTable.'.id');
                //->where($permissionTable.'.sid', $companyID);
            })->whereRaw("( p.company_id={$companyID} AND p.user_id={$userId} AND {$pageTable}.show=1 ) OR ( parent_id=0 AND {$pageTable}.show=1 AND {$pageTable}.url IS NULL ) OR {$pageTable}.id=1")
            ->groupBy($pageTable.'.id')
            ->orderBy($pageTable.'.priority')
            ->selectRaw(implode(',', [
                'DISTINCT('.$pageTable.'.id)',
                $pageTable.'.parent_id',
                $pageTable.'.name',
                $pageTable.'.url',
                $pageTable.'.class',
                $pageTable.'.icon',

            ]))
            ->get()
            ->toArray();

        $menu = [];
        foreach ($items as $r) {

            if ( $r['parent_id'] == 0){
                $menu[$r['id']]=$r;
            }else{
                $menu[$r['parent_id']]['sub'][]=$r;
            }
        }
        return $menu;
    }

    public function alert($status=false, $desc='', $mb=10)
    {
        return '<div style="margin-bottom:'.$mb.'px;padding:8px;border-width:3px" class="alert '.
            ($status ? 'alert-success' : 'alert-danger').'" role="alert">'.$desc.'</div>';
    }

    public function openPanel($title, $data = [])
    {
        // $data['default'] => in | empty
        // $data['links'] => []
        // $data['badge'] => null || 1
        $iid = strtoupper(uniqid());

        //$data['icon'] = empty($data['icon']) ? 'fa-bars' : $data['icon'];

        return '
        <div class="panel panel-app">
            <div class="panel-heading noselect clearfix">
                '.(isset($data['links']) ? '
                <div class="btn-group pull-left app-panel-btn-group" style="margin-right:6px">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                .(isset($data['icon']) ? '<i class="fa '.$data['icon'].'" aria-hidden="true"></i> ': '<i class="fa fa-bars" aria-hidden="true"></i>').'
                    </button>
                    <ul class="dropdown-menu app-dropdown-menu">'.implode('',$data['links']).'</ul>
                </div>' : '').
            ( ! isset($data['links']) && isset($data['icon']) ? '<i class="pull-left fa '.$data['icon'].'" aria-hidden="true" '.
                'style="width:30px;text-align:center;margin-right: 6px;padding: 4px 6px;'.
                'border: 1px solid #ccc;background-color: #f2f2f2;border-radius: 3px;"></i>' : '').
            '<strong class="app-panel-title-collapse pull-left" data-toggle="collapse" data-target="#'.$iid.'">'.$title. '</strong>'.
            (isset($data['badge']) ? '<span aria-hidden="true" class="pull-left badge" style="background-color:darkorange;color:white;display:inline-block;margin-left:6px;padding:5px 10px;font-size:14px;border-radius:12px">'.$data['badge'].'</span>' : '')
            .'<span data-toggle="collapse" data-target="#'.$iid.'" class="app-panel-collapse pull-right fa fa-chevron-right'.
            (isset($data['default']) && $data['default'] != 'in' ? ' collapsed' : '').'"></span>
            </div>
            <div id="'.$iid.'" class="collapse '.(isset($data['default']) ? $data['default'] : 'in').'">
                <div class="panel-body" style="'.$this->isMobile('position:relative !important;overflow-x:hidden !important;').'">';
    }

    public function closePanel()
    {
        return '</div>
            </div>
        </div>';
    }

    public function isMobile($check = null)
    {
        $result = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
        if( is_null($check) ){
            return $result;
        }
        return $check;
    }

    public function isCordova($check = null)
    {
        $result = session()->get('platform') === 'cordova';
        if( is_null($check) ){
            return $result;
        }
        return $check;
    }

    public function toInteger($id)
    {
        return (int)preg_replace('@[^0-9]@', '', $id);
    }

    public function toDecimal($decimal)
    {
        if ( is_float($decimal) ){
            return $decimal;
        }
        return (double)str_replace(',', '.', str_replace('.', '', $decimal));
    }

    public function getValue($data, $key)
    {
        if ( is_array($data) && isset($data[$key]) ){
            return $data[$key];
        }

        if ( isset($data->$key) ){
            return $data->$key;
        }
        return null;
    }

    public function pagination($model, $request)
    {
        if( $this->isMobile() ){
            return $model->appends($request->except(['_']))->render("pagination::simple-default");
        }else{
            return $model->appends($request->except(['_']))->render("pagination::default");
        }
    }

    public function options($optionId)
    {
        $options = OptionItem::where('option_id', $optionId)
            ->where('status', 1)
            ->select(['id', 'name as value'])
            ->orderBy('name')
            ->get();

        return $options;
    }

    public function log($tablo, $kolon, $tabloid, $eski, $yeni)
    {
        $log = new Log();
        $log->tablo = $tablo;
        $log->kolon = $kolon;
        $log->tabloid = $tabloid;
        $log->eski = $eski;
        $log->yeni = $yeni;
        $log->save();
    }

    public function numberFormat($number, $decimals=2, $dec_point=',', $thousands_sep='.')
    {
        return number_format($number , $decimals, $dec_point, $thousands_sep);
    }

    public function moneyFormat($number, $minDecimals=0, $dec_point=',', $thousands_sep='.')
    {
        if($minDecimals == 0){
            return rtrim(rtrim(number_format($number , 5, $dec_point, $thousands_sep), '0'), $dec_point);
        }
        return number_format($number , $minDecimals, $dec_point, $thousands_sep);
    }

    public function exchangeRate($date = null)
    {
        if ( ! is_null($date) ){
            if ( $this->isDate($date) ){
                $currencyDate = $date;
            }else{
                $currencyDate = Carbon::now()->format('Y-m-d');
            }
        }else{
            $currencyDate = Carbon::now()->format('Y-m-d');
        }

        return ExchangeRate::where('ref_date', '<', $currencyDate)
            ->whereColumn('tarih', 'ref_date')
            ->orderByDesc('tarih')
            ->first();
    }

    public function apiClient($apiUrl)
    {
        return new Client([
            'debug' => false,
            'base_uri' => $apiUrl,
            'timeout'  => 20,
            'http_errors' => false,
        ]);
    }

    public function br2nl($input){
        return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n","",str_replace("\r","", htmlspecialchars_decode($input))));
    }

    public function dateToExcel($date)
    {
        if(is_null($date)){
            return $date;
        }
        return PHPExcel_Shared_Date::PHPToExcel(Carbon::parse($date, 1)->getTimestamp());
    }

    public function pageConfig($config)
    {
        $default = [
            'portable' => false,
            'filterPanel' => true,
            'rowLimit' => 20
        ];

        if(is_array($config)){
            return array_merge($default, $config);
        }

        return $default;
    }

    public function totalText($sayi, $currCode = 'TRY', $separator = '.'){

        $sayi = $this->numberFormat((float)$sayi, 2, $separator, '');
        $sayarr = explode($separator,$sayi);

        if(isset($sayarr[1])){
            $sayarr[1]=str_pad($sayarr[1], 2, '0', STR_PAD_RIGHT);
            $sayarr[1]=substr($sayarr[1], 0, 2);
        }

        $str = "";
        $items = array(
            array("", ""),
            array("BİR", "ON"),
            array("İKİ", "YİRMİ"),
            array("ÜÇ", "OTUZ"),
            array("DÖRT", "KIRK"),
            array("BEŞ", "ELLİ"),
            array("ALTI", "ALTMIŞ"),
            array("YEDİ", "YETMİŞ"),
            array("SEKİZ", "SEKSEN"),
            array("DOKUZ", "DOKSAN")
        );

        $currencies = [
            'TRY' => 'TÜRK LİRASI',
            'EUR' => 'EURO',
            'USD' => 'ABD DOLARI',
            'GBP' => 'İNGİLİZ STERLİNİ',

        ];


        $currencyCents = [
            'TRY' => 'KURUŞ',
            'EUR' => 'CENT',
            'USD' => 'CENT',
            'GBP' => 'CENT',

        ];

        for ($eleman = 0; $eleman<count($sayarr); $eleman++) {

            for ($basamak = 1; $basamak <=strlen($sayarr[$eleman]); $basamak++) {
                $basamakd = 1 + (strlen($sayarr[$eleman]) - $basamak);


                try {
                    switch ($basamakd) {
                        case 6:
                            $str = $str . "" . $items[substr($sayarr[$eleman],$basamak - 1,1)][0] . "YÜZ";
                            break;
                        case 5:
                            $str = $str . "" . $items[substr($sayarr[$eleman],$basamak - 1,1)][1];
                            break;
                        case 4:
                            if ($items[substr($sayarr[$eleman],$basamak - 1,1)][0] != "BİR") $str = $str . "" . $items[substr($sayarr[$eleman],$basamak - 1,1)][0] . "BİN";
                            else $str = $str . "BİN";
                            break;
                        case 3:
                            if($items[substr($sayarr[$eleman],$basamak - 1,1)][0]=="") {
                                $str.="";
                            }
                            elseif ($items[substr($sayarr[$eleman],$basamak - 1,1)][0] != "BİR" ) $str = $str . "" . $items[substr($sayarr[$eleman],$basamak - 1,1)][0] . "YÜZ";

                            else $str = $str . "YÜZ";
                            break;
                        case 2:
                            $str = $str . "" . $items[substr($sayarr[$eleman],$basamak - 1,1)][1];
                            break;
                        default:
                            $str = $str . "" . $items[substr($sayarr[$eleman],$basamak - 1,1)][0];
                            break;
                    }
                } catch (\Exception $err) {
                    //echo $err->getMessage();
                    //break;
                }
            }
            if ($eleman< 1) $str = $str . ' '.@$currencies[$currCode].' ';
            else {
                if ($sayarr[1] != "00") $str = $str . ' '.@$currencyCents[$currCode];
            }
        }
        return $str;
    }

    public function getAllowUploadExtensions()
    {
        $data = [
            'zip', 'rar', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'
        ];

        sort($data);

        return $data;
    }

    public function fileSizeUnits($bytes)
    {
        if ($bytes >= 1000000000) {
            $bytes = $this->numberFormat($bytes / 1000000000) . ' GB';
        } elseif ($bytes >= 1000000) {
            $bytes = $this->numberFormat($bytes / 1000000) . ' MB';
        } elseif ($bytes >= 1000) {
            $bytes = $this->numberFormat($bytes / 1000) . ' KB';
        } elseif ($bytes > 0) {
            $bytes = $bytes . ' B';
        } else {
            $bytes = '0 B';
        }

        return $bytes;
    }

    public function accountBalance($accountId, $currCode = 'TRY')
    {
        $balance = ViewCurrBalance::init()
            ->where('AccountId', $accountId)
            ->where('CurrCode', $currCode)
            ->first();

        if( isset($balance->BalanceCurrWithVat) ){
            return (float)$balance->BalanceCurrWithVat;
        }
        return 0.0;
    }

    public function getExchangeFromType(ExchangeRate $exchangeRate, $type)
    {
        if( $type == 1 ){
            return (object)[
                'usd' => $exchangeRate->usd_buy,
                'eur' => $exchangeRate->eur_buy,
                'gbp' => $exchangeRate->gbp_buy,
            ];
        }elseif( $type == 3 ){
            return (object)[
                'usd' => $exchangeRate->usd_banknote_buy,
                'eur' => $exchangeRate->eur_banknote_buy,
                'gbp' => $exchangeRate->gbp_banknote_buy,
            ];
        }elseif( $type == 4 ){
            return (object)[
                'usd' => $exchangeRate->usd_banknote_sell,
                'eur' => $exchangeRate->eur_banknote_sell,
                'gbp' => $exchangeRate->gbp_banknote_sell,
            ];
        }

        # DÖVİZ SATIŞ (VARSAYILAN)
        return (object)[
            'usd' => $exchangeRate->usd,
            'eur' => $exchangeRate->eur,
            'gbp' => $exchangeRate->gbp,
        ];
    }

    public function productPrice($productPrice, $type, $value)
    {
        $productPrice = (float)$productPrice;
        $value = (float)$value;

        // ['percent_raise','percent_discount','fixed_raise','fixed_discount','fixed'];

        if( $type == 'percent_raise' && $value > 0 ){
            return  $productPrice + ($productPrice * ($value / 100));

        }elseif( $type == 'percent_discount' && $value > 0 ){
            $price = $productPrice - ($productPrice * ($value / 100));
            if( $price < 0 ){
                return 0;
            }
            return $price;

        }elseif( $type == 'fixed_raise' ){
            return $productPrice + $value;

        }elseif( $type == 'fixed_discount' ){
            $price = $productPrice - $value;
            if( $price < 0 ){
                return 0;
            }
            return $price;

        }elseif( $type == 'fixed' ){
            return $value;
        }

        return $productPrice;
    }

    public function newSerialNumber($lastSerialNo)
    {
        if(empty($lastSerialNo)){
            return null;
        }
        $string = (string)preg_replace('/[0-9]+/', '', $lastSerialNo);
        $strlen = strlen((string)$lastSerialNo) - strlen($string);
        $number = str_pad($this->toInteger($lastSerialNo) + 1, $strlen, '0', STR_PAD_LEFT);
        return $string.$number;
    }

    public function fileElement($href, $data = [])
    {
        $data = array_merge([

            'class' => '',
            'deskclass' => '',
            'mobclass' => '',
            'cordclass' => '',

            'text' => '',

            'icon' => '',

            'ext' => '', // for cordova
            'title' => '', // for modal title

        ], $data);

        if( $this->isCordova() ){

            return '<a class="'.$data['cordclass'].'" href="javascript:void(0)" onclick="CordovaApp.openFile(\''.$href.'\', \''.$data['ext'].'\')">'
                .(empty($data['icon']) ? '' : '<span class="'.$data['icon'].'"></span> ').$data['cordtext'].'
                </a>';

        }elseif( $this->isMobile() ){

            return '<a class="'.$data['mobclass'].'" href="'.$href.'" target="_blank">'
                .(empty($data['icon']) ? '' : '<span class="'.$data['icon'].'"></span> ').$data['mobtext'].'
                </a>';

        }elseif ( in_array($data['ext'], ['pdf']) ){
            # IFRAME
            return '<a data-toggle="tooltip" title="'.$data['title'].'" data-modal-size="xl" data-modal-type="iframe" data-modal-title="'.$data['title'].'" 
                    class="'.$data['deskclass'].' app-process" href="'.$href.'">'
                .(empty($data['icon']) ? '' : '<span class="'.$data['icon'].'"></span> ').$data['desktext'].'
                </a>';
        }

        return '<a data-toggle="tooltip" title="'.$data['title'].'" target="_blank"
                    class="'.$data['deskclass'].'" href="'.$href.'">'
            .(empty($data['icon']) ? '' : '<span class="'.$data['icon'].'"></span> ').$data['desktext'].'
                </a>';
    }

    public function userStatusLog($id, $status)
    {
        $userStatusLog = new UserStatusLog();
        $userStatusLog->fill([
            'type' => $status == 'on' ? 1 : 0,
            'user_id' => $id,
        ]);
        $userStatusLog->save();
    }
}