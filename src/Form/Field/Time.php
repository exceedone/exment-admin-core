<?php

namespace ExmentAdminCore\Admin\Form\Field;

class Time extends Date
{
    /**
     * @var string
     */
    protected $format = 'HH:mm:ss';

    protected function beforeRender(): void
    {
        $selector = $this->getElementClassSelector();

        // Important for HasMany (1-N): this script must be part of the field script stack
        // so it will be included in the nested templateScript executed after adding a row.
        $this->script .= <<<JS
;(function () {
    var s = '{$selector}';
    $(document).off('focus.openadmin-time-default', s).on('focus.openadmin-time-default', s, function () {
        var \$i = $(this);
        if ((\$i.val() || '').trim()) return;
        var p = \$i.parent().data('DateTimePicker');
        if (!p || !window.moment) return;
        p.date(moment('00:00:00', 'HH:mm:ss'));
        \$i.val('');
    });
})();
JS;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|string
     */
    public function render()
    {
        // Prevent the picker from auto-filling current time.
        $this->options['useCurrent'] = $this->options['useCurrent'] ?? false;

        $this->prepend('<i class="fa fa-clock-o fa-fw"></i>')
            ->defaultAttribute('style', 'width:150px !important; flex:0 0 150px;');

        return parent::render();
    }
}
