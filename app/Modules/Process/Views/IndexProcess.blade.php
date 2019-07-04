{!! Core::openPanel(__('Proses Listesi'), ['links'=>[
    '<li><a class="app-process" href="'.route('processes.create').'"><i class="fa fa-plus fa-fw"></i> '.__('Yeni Proses').'</a></li>',
    //'<li><a class="app-process" href="'.url('/kullanici/kisayol_form/?pid=35').'"><i class="fa fa-bookmark fa-fw"></i> Yeni Kısayol</a></li>',
    '<li class="divider"></li>',
    '<li><a class="app-export-excel-button" href="javascript:void(0)"><i class="fa fa-file-excel-o fa-fw"></i> '.__('Excel\'e Aktar').'</a></li>'
    ]]) !!}

@if( $items->isEmpty() )
    {!! Core::alert(false, __('Kayıt bulunamadı.'), 0) !!}
@else
    <table data-app-datatable="1"
           data-app-filterid="{{ Form::id() }}"
           data-order-column="{{ $req->orderByCol }}"
           data-order-type="{{ $req->orderByType }}"
           class="compact nowrap cell-border row-border table-border hover" style="width:100%">
        <thead>
        <tr>
            <th data-orderable="true" data-name="Id">{{ __('No') }}</th>
            @if( $req->set_form_id )
                <th data-orderable="false">{{ __('Seç') }}</th>
            @endif
            <th data-orderable="false" class="desktop">{{ __('İşlemler') }}</th>
            <th data-orderable="true" data-name="name">{{ __('Adı') }}</th>
            <th data-orderable="true" data-name="created_at">{{ __('Oluşturma') }}</th>
            <th data-orderable="true" data-name="status">{{ __('Durum') }}</th>
        </tr>
        </thead>
        <tbody>
        @php( $enumStatus = Core::enumsHtml('process_status') )

        @foreach($items as $item)

            @if( $item->status != 1 )
                @php( $statusClass = 'text-red' )
            @else
                @php( $statusClass = '' )
            @endif

            <tr>
                <td>
                    <span class="{{ $statusClass }}">{{ $item->id }}</span>
                </td>

                @if( $req->set_form_id )
                    <td>
                        <a class="btn btn-xs btn-warning text-nowrap app-set-form btn-in-table"
                           data-form-id="{{ $req->set_form_id }}"
                           data-element-name="{{ $req->element_name }}"
                           data-id="{{ $item->id }}"
                           data-value="{{ $item->name }}" href="javascript:void(0)">
                            <span class="glyphicon glyphicon-play" aria-hidden="true"></span> {{ __('Seç') }}
                        </a>
                    </td>
                @endif

                <td>
                    <a data-toggle="tooltip" title="{{ __('Düzenle') }}" class="btn btn-primary btn-xs ajaxPage" href="{{ route('processes.edit', $item->id) }}">
                        <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                    </a>

                    <a data-toggle="tooltip" title="{{ __('Sil') }}" data-method="delete" data-confirm="{{ __('Bu porses silinecek. Devam etmek istiyor musunuz?') }}"
                       class="btn btn-danger btn-xs" href="{{ route('processes.destroy', $item->id) }}">
                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                    </a>
                </td>

                <td>
                    <a class="app-process {{ $statusClass }}" data-toggle="tooltip" title="{{ $item->name }}" href="{{ route('processes.edit', $item->id) }}">
                        {{ Core::strLimit($item->name, 30) }}
                    </a>
                </td>

                <td data-order="{{ $item->created_at }}">
                        <span class="{{ $statusClass }}" data-toggle="tooltip" title="{{ Core::date($item->created_at, 'Y-m-d H:i:s', 'd.m.Y H:i:s') }}">
                            {{ Core::date($item->created_at, 'Y-m-d H:i:s', '%d %b %Y / %H:%M') }}</span>
                </td>
                <td>
                    <span class="{{ $statusClass }}">{!! isset($enumStatus[$item->status]) ? $enumStatus[$item->status] : '' !!}</span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ Core::pagination($items, $req) }}
@endif

{!! Core::closePanel() !!}