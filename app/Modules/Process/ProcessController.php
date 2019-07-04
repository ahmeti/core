<?php

namespace App\Modules\Process;

use App\Core;
use App\Modules\Process\Models\Process;
use App\Modules\Process\Models\ProcessView;
use App\Modules\Process\Requests\CreateProcess;
use App\Modules\Process\Requests\DestroyProcess;
use App\Modules\Process\Requests\EditProcess;
use App\Modules\Process\Requests\IndexProcess;
use App\Modules\Process\Requests\SearchProcess;
use App\Modules\Process\Requests\StoreProcess;
use App\Modules\Process\Requests\UpdateProcess;
use App\Response;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ProcessController extends Controller
{
    public function __construct()
    {
        view()->addNamespace('Process', app_path('Modules/Process/Views'));
    }

    public function index(IndexProcess $req)
    {
        $items = (new ProcessView)->request($req->all())->result();

        if ($req->export === 'excel'){
            // return (new ProcessExport)->download($items, $req);
        }

        Core::addBreadcrumb(__('Proses Listesi'), route('processes.index'), 'fa-square');

        $body = view('Process::IndexProcess', compact('req', 'items'))
            ->render();

        return Response::title(__('Proses Listesi'))
            ->body($body)
            ->get();
    }

    public function create(CreateProcess $req)
    {
        Core::addBreadcrumb(__('Proses Listesi'), route('processes.index'), 'fa-square');
        Core::addBreadcrumb(__('Yeni Proses'), route('processes.create'), 'fa-plus');

        $body = view('Process::CreateProcess', compact('req'))
            ->render();

        return Response::title(__('Yeni Proses'))
            ->body($body)
            ->get();
    }

    public function store(StoreProcess $req)
    {
        $item = new Process();
        $item->name = $req->name;
        $item->status = $req->status;
        $item->save();

        $goUrl = $req->gourl === 'no' ? null : route('processes.edit', $item->id);

        return Response::status(true)
            ->message(__('İşlem başarılı. Proses kaydedildi.'))
            ->errorName('gonder')
            ->goUrl($goUrl)
            ->setData([
                [
                    'name' => $req->set_element_name ? $req->set_element_name : 'process_id',
                    'id' => $item->id,
                    'value' => $item->name
                ]
            ])
            ->get();
    }

    public function edit(EditProcess $req, $id)
    {
        Core::addBreadcrumb(__('Proses Listesi'), route('processes.index'), 'fa-square');
        Core::addBreadcrumb(__('Proses Düzenle'), route('processes.edit', $id), 'fa-edit');

        $item = (new ProcessView)->find($id);

        $body = view('Process::EditProcess', compact('req', 'item'))
            ->render();

        return Response::title(__('Proses Düzenle'))
            ->body($body)
            ->get();
    }

    public function update(UpdateProcess $req, $id)
    {
        $item = Process::find($id);
        $item->name = $req->name;
        $item->status = $req->status;
        $item->save();

        $goUrl = $req->gourl === 'no' ? null : route('processes.edit', $item->id);

        return Response::status(true)
            ->message(__('İşlem başarılı. Proses güncellendi.'))
            ->errorName('gonder')
            ->goUrl($goUrl)
            ->get();
    }

    public function destroy(DestroyProcess $req, $id)
    {
        Core::addBreadcrumb(__('Proses Listesi'), route('processes.index'), 'fa-square');
        Core::addBreadcrumb(__('Delete Process'), null, 'fa-trash');

        $item = Process::find($id);
        $item->status = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        return Response::status(true)
            ->body(Core::alert(true, __('İşlem başarılı. Proses silindi.')))
            ->get();
    }

    public function search(SearchProcess $req)
    {
        $items = Process::query();

        if( $req->search ){
            $items->where('name', 'like', '%' . $req->search . '%');
        }

        $items = $items->select(['id', 'name as text'])
            ->orderBy('name')
            ->limit(40)
            ->get()
            ->toArray();

        return response()->json(['items' => $items]);
    }
}