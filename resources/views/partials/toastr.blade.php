@if(Session::has('toastr'))
    @php
        $toastr     = Session::get('toastr');
        $type       = \Illuminate\Support\Arr::get($toastr->get('type'), 0, 'success');
        // only toastr's own methods; the message is written as JSON
        $type       = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
        $message    = \Illuminate\Support\Arr::get($toastr->get('message'), 0, '');
        // the message is shown as text; one marked as HTML (an Htmlable, e.g. new HtmlString($html)) as it is
        $isHtml     = $message instanceof \Illuminate\Contracts\Support\Htmlable;
        $message    = $isHtml ? (string)$message->toHtml() : (string)$message;
        $options    = $toastr->get('options', []);
        $options['escapeHtml'] = !$isHtml;
        $options    = json_encode($options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    @endphp
    <script>
        $(function () {
            toastr[@json($type)](@json($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), null, {!! $options !!});
        });
    </script>
@endif
