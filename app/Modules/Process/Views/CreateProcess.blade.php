{!! Core::openPanel(__('Yeni Proses'), ['links'=>[
    '<li><a class="app-process" href="'.route('processes.create').'"><i class="fa fa-plus fa-fw"></i> '.__('Yeni Proses').'</a></li>',
    //'<li><a class="app-process" href="'.url('/kullanici/kisayol_form/?pid=36').'"><i class="fa fa-bookmark fa-fw"></i> Yeni Kısayol</a></li>',
    ]]) !!}

    {!! Form::open(['addclass' => 'app-form-process', 'action' => route('processes.store')]) !!}

    {!! Form::hidden('gourl', $req->gourl) !!}
    {!! Form::hidden('set_element_name', $req->set_element_name) !!}

    <div class="col-sm-6">
        {!! Form::text('name', __('Adı *'), $req->name, 100,
            ['ph'=>__('Proses adını yazınız.')]) !!}
    </div>

    <div class="col-sm-6">
        {!! Form::select('status', __('Durum *'), $req->status, Core::enumsSelect('process_status'),
            ['ph'=>__('Proses durumunu seçiniz.'), 'icon'=>true]); !!}

        {!! Form::submit('gonder', __('KAYDET')) !!}
    </div>

    {!! Form::close() !!}

{!! Core::closePanel() !!}