<select class="form-control {{ $class }}" name="{{$name}}[]" multiple style="width: 100%; display: none;">
    @foreach($options as $select => $option)
        @if(is_array(request($name, [])))
        <option value="{{$select}}" {{ in_array((string)$select, request($name, [])) ? 'selected':'' }}>{{$option}}</option>
        @else
        <option value="{{$select}}" {{ (string)$select === (string)request($name) ? 'selected':'' }}>{{$option}}</option>
        @endif
    @endforeach
</select>