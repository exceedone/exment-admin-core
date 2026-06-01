<div class="box">

    <div class="box-header px-3 pt-3">
        @if($title)
        <h3 class="box-title">{{$title}}</h3>
        @endif

        @if($useExpandCollapse)
        <div class="btn-group">
            <a class="btn btn-primary btn-sm {{ $id }}-tree-tools" data-action="expand" title="{{ trans('admin.expand') }}"  onclick="admin.tree.expand();">
                <i class="fa fa-plus-square-o"></i>&nbsp;{{ trans('admin.expand') }}
            </a>
            <a class="btn btn-primary btn-sm {{ $id }}-tree-tools" data-action="collapse" title="{{ trans('admin.collapse') }}" onclick="admin.tree.collapse();">
                <i class="fa fa-minus-square-o"></i>&nbsp;{{ trans('admin.collapse') }}
            </a>
        </div>
        @endif

        @if($useSave)
        <div class="btn-group">
            <a class="btn btn-info btn-sm {{ $id }}-save" title="{{ trans('admin.save') }}" onclick="admin.tree.save();"><i class="fa fa-save"></i><span class="d-none d-md-inline">&nbsp;{{ trans('admin.save') }}</span></a>
        </div>
        @endif

        @if($useRefresh)
        <a class="btn btn-warning btn-sm {{ $id }}-refresh text-white"
                title="{{ trans('admin.refresh') }}"
                onclick="admin.ajax.reload(); window._showToastrOnPjax = true;">
                <i class="fa fa-refresh"></i>
                <span class="d-none d-md-inline">&nbsp;{{ trans('admin.refresh') }}</span>
                </a>
        @endif

        <div class="btn-group">
            {!! $tools !!}
        </div>

        @if($useCreate)
        <div class="btn-group pull-right">
            <a class="btn btn-success btn-sm" href="{{ $path }}/create"><i class="fa fa-save"></i><span class="d-none d-md-inline">&nbsp;{{ trans('admin.new') }}</span></a>
        </div>
        @endif

    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding p-1">
        <div class="dd" id="{{ $id }}">
            <ol class="dd-list">
                @each($branchView, $items, 'branch')
            </ol>
        </div>
    </div>
    <!-- /.box-body -->
</div>


<script>
(function() {
    const message = @json(__('admin.refresh_succeeded'));

    // Đảm bảo toastr được load và sẵn sàng
    function showToastrSuccess() {
        toastr.success(message, '', {
            closeButton: true,
            progressBar: true,
            timeOut: 4000,
            showMethod: 'slideDown'
        });
    }

    if (typeof admin !== 'undefined' && admin.pages) {
        if (typeof admin.pages._originalInit === 'undefined') {
            admin.pages._originalInit = admin.pages.init;

            admin.pages.init = function () {
                admin.pages._originalInit.call(this);
                if (window._showToastrOnPjax) {
                    showToastrSuccess();
                    window._showToastrOnPjax = false;
                }
            };
        }
    } 

    // Nếu window._showToastrOnPjax đang true khi load script
    if (window._showToastrOnPjax) {
        showToastrSuccess();
        window._showToastrOnPjax = false;
    }
})();
</script>
