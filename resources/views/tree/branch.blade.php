<li class="dd-item" data-id="{{ $branch[$keyName] }}">
    @if(isset($branch['children']))
    <button data-action="collapse" type="button">Collapse</button>
    <button data-action="expand" type="button" style="display:none">Expand</button>
    @endif
    <div class="dd-handle">
        {!! $branchCallback($branch) !!}

        @if($useAction)
        <span class="pull-right dd-nodrag">
            <a href="{{ $path }}/{{ $branch[$keyName] }}/edit"><i class="fa fa-edit"></i></a>
            <a onclick="admin.tree.delete({{ $branch[$keyName] }})"  data-id="{{ $branch[$keyName] }}" class="tree_branch_delete" style="cursor: pointer;"><i class="fa fa-trash"></i></a>
        </span>
        @endif
    </div>
    @if(isset($branch['children']))
    <ol class="dd-list">
        @foreach($branch['children'] as $branch)
            @include($branchView, $branch)
        @endforeach
    </ol>
    @endif
</li>