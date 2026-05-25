<script data-exec-on-popstate>

    (function () {
        var run = function () {
            @foreach($script as $s)
                {!! $s !!}
            @endforeach
        };
        if (document.readyState === 'complete') {
            run();
        } else {
            $(run);
        }
    })();
</script>