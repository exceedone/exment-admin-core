<div {!! $attributes !!}>
    @foreach($items as $key => $item)
    <div class="panel box box-primary" style="margin-bottom: 0px">
        <div class="box-header with-border">
            <h3 class="box-title">
                <a data-bs-toggle="collapse" data-parent="#{{$id}}" href="#collapse{{ $key }}">
                    {{ $item['title'] }}
                </a>
            </h3>
        </div>
        <div id="collapse{{ $key }}" class="panel-collapse collapse {{ $key == 0 ? 'in' : '' }}">
            <div class="box-body">
                {!! $item['content'] !!}
            </div>
        </div>
    </div>
    @endforeach

</div>
