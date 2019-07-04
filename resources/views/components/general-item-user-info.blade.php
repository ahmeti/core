@if( isset($item->created_user_name) &&  isset($item->updated_user_name) )
    @php($createdUserName = $item->created_user_name)
    @php($updatedUserName = $item->updated_user_name)
@else
    @php($createdUserName = null)
    @php($updatedUserName = null)
@endif

@php( $createdAt = Core::date($item->created_at, 'Y-m-d H:i:s', '%d %b %Y / %H:%M:%S') )
@php( $updatedAt = Core::date($item->updated_at, 'Y-m-d H:i:s', '%d %b %Y / %H:%M:%S') )

{!! Core::openPanel(__('Oluşturma & Düzenleme'), ['icon' => 'fa-edit', 'default'=>'']) !!}
{!! Form::open() !!}
<div class="col-sm-6">
    {!! Form::text('', __('Oluşturan'), $createdUserName, 100, ['disabled'=>true]) !!}
    {!! Form::text('', __('Oluşturma'), $createdAt, 100, ['disabled'=>true]) !!}
</div>
<div class="col-sm-6">
    {!! Form::text('', __('Düzenleyen'), $updatedUserName, 100, ['disabled'=>true]) !!}
    {!! Form::text('', __('Düzenleme'), $updatedAt, 100, ['disabled'=>true]) !!}
</div>
{!! Form::close() !!}
{!! Core::closePanel() !!}