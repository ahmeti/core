<?php

namespace Ahmeti\Core\Services;

# use App\Models\ExchangeRate;
# use App\Models\Log;
use App\Modules\Company\Models\Company;
use App\Modules\Page\Models\Page;
use App\Modules\Status\Models\Status;
use App\Modules\User\Models\Permission;
# use App\Modules\UserStatusLog\Models\UserStatusLog;
use App\Modules\Company\Services\CompanySettingService;
use App\Core;
use App\Response;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
                ->select(['name', 'key', 'value', 'icon', 'color'])
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
            $newEnums[] = ['id' => $v['key'], 'value' => $v['value'], 'icon' => $v['icon'], 'icon-color' => $v['color']];
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
                $newEnums[$v['key']] = '<i '.(empty($v['color']) ? '' : 'style="color:'.$v['color'].';" ').'class="'.$v['icon'].'"></i> '.$v['value'];
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

    public function isSuperAdmin()
    {
        if ( auth()->user() && auth()->user()->authority === 'superadmin' ){
            return true;
        }
        return false;
    }

    public function isAdmin()
    {
        if ( auth()->user() && in_array(auth()->user()->authority, ['superadmin', 'admin']) ){
            return true;
        }
        return false;
    }

    public function isRep()
    {
        if ( auth()->user() && auth()->user()->authority === 'representative' ){
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
            <i class="fa fa-home fa-fw"></i> <a class="ajaxPage" href="'.route('home').'">'.__('Anasayfa').'</a>
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

            $r['name'] = __($r['name']);

            if ( $r['parent_id'] == 0){
                $menu[$r['id']] = $r;
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

    private function numberToText($sayi){

        $o = [
            'birlik' => ['BİR', 'İKİ', 'ÜÇ', 'DÖRT', 'BEŞ', 'ALTI', 'YEDİ', 'SEKİZ', 'DOKUZ'],
            'onluk' => ['ON', 'YİRMİ', 'OTUZ', 'KIRK', 'ELLİ', 'ALTMIŞ', 'YETMİŞ', 'SEKSEN', 'DOKSAN'],
            'basamak' => ['YÜZ', 'BİN', 'MİLYON', 'MİLYAR', 'TRİLYON', 'KATRİLYON']
        ];

        // Sayıyı basamaklarına ayırıyoruz
        $basamak = array_reverse(str_split(implode('', array_reverse(str_split($sayi))), 3));

        // Basamak sayısını belirliyoruz
        $basamak_sayisi = count($basamak);

        // Her basamak için:
        for ($i=0; $i < $basamak_sayisi; ++$i)
        {
            // Sayıyı basamaklarına ayırdığımızda basamaklar tersine döndüğü için burada ufak bir işlem ile basamakları düzeltiyoruz
            $basamak[$i] = implode(array_reverse(str_split($basamak[$i])));

            // Eğer basamak 4, 8, 15, 16, 23, 42 gibi 1 veya 2 rakamlıysa başına 3 rakama tamamlayacak şekilde "0" ekliyoruz ki foreach döngüsünde problem olmasın
            if (strlen($basamak[$i]) == 1)
                $basamak[$i] = '00' . $basamak[$i];
            elseif (strlen($basamak[$i]) == 2)
                $basamak[$i] = '0' . $basamak[$i];
        }

        $yenisayi = array();

        // Her basamak için: ($yenisayi değişkenine)
        foreach ($basamak as $k => $b)
        {
            // basamağın ilk rakamı 0'dan büyük ise
            if ($b[0] > 0)
                // değişkene rakamın harfle yazılışı ve "yüz" ekliyoruz
                $yenisayi[] = ($b[0] > 1 ? $o['birlik'][$b[0]-1] . ' ' : '') . $o['basamak'][0];

            // basamağın 2. rakamı 0'dan büyük ise
            if ($b[1] > 0)
                // değişkene rakamın harfle yazılışını ekliyoruz
                $yenisayi[] = $o['onluk'][$b[1]-1];

            // basamağın 3. rakamı 0'dan büyük ise
            if ($b[2] > 0)
                // değişkene rakamın harfle yazılışını ekliyoruz
                $yenisayi[] = $o['birlik'][$b[2]-1];

            // değişkene basamağın ismini (bin, milyon, milyar) ekliyoruz
            if ($basamak_sayisi > 1)
                $yenisayi[] = $o['basamak'][$basamak_sayisi-1];

            // Basamak sayısını azaltıyoruz ki her basamağın sonuna ilkinde ne yazıyorsa o yazılmasın
            --$basamak_sayisi;
        }

        return implode(' ', $yenisayi);
    }

    public function totalText($sayi, $currCode = 'TRY', $separator = '.')
    {
        $sayi = $this->numberFormat((float)$sayi, 2, $separator, '');
        $sayarr = explode($separator, $sayi);

        $left = $this->numberToText($sayarr[0]);
        $right = $this->numberToText($sayarr[1]);

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

        if (empty($left)) {
            $left = 'SIFIR';
        }

        if (!empty($right)) {
            $right = ' '.$right.' '.@$currencyCents[$currCode];
        }

        $text = $left.' '.@$currencies[$currCode].$right;

        return str_replace(' ', '', $text);

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

        }elseif ( in_array($data['ext'], ['pdf', 'html']) ){
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

    public function failedValidation(Validator $validator, $title = null)
    {
        $errorKey = null;
        $errorMessage = null;
        foreach ($validator->errors()->toArray() as $key => $message){
            $errorKey = $key;
            $errorMessage = $message[0];
            break;
        }

        if( $title ){
            $response = Response::title($title)
                ->body( Core::alert(false, __('İşlem başarısız. ').$errorMessage) )
                ->get();
        }else{
            $response = Response::status(false)->message($errorMessage)->errorName($errorKey)->get();
        }

        throw new HttpResponseException($response);
    }

    public function strLimit($value, $limit = 100, $end = '...')
    {
        return Str::limit($value, $limit, $end);
    }

    public function unitPriceFormat($number, $minDecimals=2, $dec_point=',', $thousands_sep='.')
    {
        $long = number_format((float)$number, 5, $dec_point, $thousands_sep);
        $exp = explode($dec_point, rtrim($long, '0'));

        if( strlen($exp[1]) >= $minDecimals ){
            return rtrim($exp[0].$dec_point.$exp[1], '.');
        }

        return $exp[0].$dec_point.str_pad($exp[1],$minDecimals,'0',STR_PAD_RIGHT);
    }


    public function getCompanySettings($name = null)
    {
        if( empty($this->companySettings) ){
            $this->companySettings = (new CompanySettingService)->get();
        }

        if( ! empty($name) ){
            return isset($this->companySettings->$name) ? $this->companySettings->$name : null;
        }

        return $this->companySettings;
    }
}