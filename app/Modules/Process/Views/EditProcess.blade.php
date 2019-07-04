{!! Core::openPanel(__('Proses Düzenle'), ['links'=>[
    '<li><a class="app-process" href="'.route('processes.create').'"><i class="fa fa-plus fa-fw"></i> '.__('Yeni Proses').'</a></li>',
    // '<li><a class="app-process" href="'.url('/kullanici/kisayol_form/?pid=70').'"><i class="fa fa-bookmark fa-fw"></i> Yeni Kısayol</a></li>',
    '<li class="divider"></li>',
    '<li><a data-method="delete" data-confirm="'.__('Bu porses silinecek. Devam etmek istiyor musunuz?').'" href="'.route('processes.destroy', $item->id).'"><i class="fa fa-trash fa-fw"></i> Proses Sil</a></li>'
    ]]) !!}

{!! Form::open(['addclass' => 'app-form-process', 'method'=>'put', 'action' => route('processes.update', $item->id)]) !!}

{!! Form::hidden('_method', 'PUT') !!}
{!! Form::hidden('id', $item->id) !!}
{!! Form::hidden('gourl', $req->gourl) !!}

<div class="col-sm-6">
    {!! Form::text('name', __('Adı *'), $item->name, 100,
        ['ph'=>__('Proses adını yazınız.')]) !!}
</div>

<div class="col-sm-6">
    {!! Form::select('status', __('Durum *'), $item->status, Core::enumsSelect('process_status'),
        ['ph'=>__('Proses durumunu seçiniz.'), 'icon'=>true]); !!}

    {!! Form::submit('gonder', __('KAYDET')) !!}
</div>

{!! Form::close() !!}

{!! Core::closePanel() !!}

@include('components.general-item-user-info', [
    'item' => $item
])