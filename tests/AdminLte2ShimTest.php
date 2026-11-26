<?php

use ExmentAdminCore\Admin\Admin;

/**
 * 2.0 (B4): AdminLTE 2's app.min.js is replaced by a shim with the parts the markup still uses.
 */
class AdminLte2ShimTest extends TestCase
{
    public function testTheShimIsLoadedInsteadOfAppJs(): void
    {
        $this->assertContains('vendor/open-admin/open-admin/js/adminlte2-shim.js', Admin::$baseJs);
        $this->assertNotContains('vendor/open-admin/AdminLTE/dist/js/app.min.js', Admin::$baseJs);
    }

    public function testTheShimKeepsWhatTheMarkupUses(): void
    {
        $shim = (string) file_get_contents(dirname(__DIR__) . '/resources/assets/open-admin/js/adminlte2-shim.js');

        foreach ([
            "\$.AdminLTE.tree('.sidebar')",            // the sidebar menu (.treeview)
            '.treeview-menu',
            "sidebarToggleSelector: \"[data-bs-toggle='offcanvas']\"",
            "remove: '[data-widget=\"remove\"]'",       // the box tools
            "collapse: '[data-widget=\"collapse\"]'",
            '$.fn.activateBox',
            '$.fn.toggleBox',
            '$.fn.removeBox',
            "toggleBtnSelector: \"[data-bs-toggle='control-sidebar']\"",
            'new window.bootstrap.Tooltip(document.body',
            'window.AdminLTEOptions',
        ] as $needle) {
            $this->assertStringContainsString($needle, $shim, $needle);
        }
        // the plugins 2.0 does not load
        $this->assertStringNotContainsStringIgnoringCase('slimscroll', str_replace('slimScroll, FastClick', '', $shim));
        $this->assertStringNotContainsString('FastClick.attach', $shim);
    }
}
