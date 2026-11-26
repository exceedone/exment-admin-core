{{-- included by content.blade.php (and Exment's pages) without isShowDetail: as Handler::renderException decides it --}}
@php($isShowDetail = $isShowDetail ?? boolval(config('app.debug', false)))
@if($errors->hasBag('exception'))
    <?php $error = $errors->getBag('exception');?>
    <div class="alert alert-warning alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4>
            <i class="icon fa fa-warning"></i>
            @if($isShowDetail)
                <i style="border-bottom: 1px dotted #fff;cursor: pointer;" title="{{ $error->first('type') }}" ondblclick="var f=this.innerHTML;this.innerHTML=this.title;this.title=f;">{{ class_basename($error->first('type')) }}</i>
                In <i title="{{ $error->first('file') }} line {{ $error->first('line') }}" style="border-bottom: 1px dotted #fff;cursor: pointer;" ondblclick="var f=this.innerHTML;this.innerHTML=this.title;this.title=f;">{{ basename($error->first('file')) }} line {{ $error->first('line') }}</i> :
            @else
                {{ trans('admin.error_exception') }}
            @endif
        </h4>

        @if($isShowDetail)
        <p><a style="cursor: pointer;" onclick="$('#laravel-admin-exception-trace').toggleClass('hidden');$('i', this).toggleClass('fa-angle-double-down fa-angle-double-up');"><i class="fa fa-angle-double-down"></i>&nbsp;&nbsp;{{ $error->first('message') }}</a></p>
        <p class="hidden" id="laravel-admin-exception-trace"><br>{{ nl2br($error->first('trace')) }}</p>
        @else
        <p>{{ $error->first('message') }}</p>
        @endif
    </div>
@endif
