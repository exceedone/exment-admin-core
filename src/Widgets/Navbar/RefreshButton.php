<?php

namespace OpenAdminCore\Admin\Widgets\Navbar;

use OpenAdminCore\Admin\Admin;
use Illuminate\Contracts\Support\Renderable;

class RefreshButton implements Renderable
{
    public function render()
{
    $message = json_encode(__('admin.refresh_succeeded'));
    $script = <<<SCRIPT
(function() {
    const message = {$message};
    /**
     * Show a success message using Toastr.
     */
    function showToastrSuccess() {
        toastr.success(message, '', {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-center",
            timeOut: 4000,
            showMethod: 'slideDown'
        });
    }

    $(function() {
        $('.container-refresh').off('click').on('click', function() {
            $.admin.reload();
            showToastrSuccess();
        });
    });
})();
SCRIPT;

    Admin::script($script);

    return <<<'EOT'
<li>
    <a href="javascript:void(0);" class="container-refresh hidden-xs">
      <i class="fa fa-refresh"></i>
    </a>
</li>
EOT;
}

}
