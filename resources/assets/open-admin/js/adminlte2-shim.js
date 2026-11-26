/*!
 * The parts of AdminLTE 2's app.js (2.3.2, MIT, Almsaeed Studio) that the AdminLTE 2 markup still
 * uses, without its other plugins (slimScroll, FastClick) (exment-admin-core 2.0).
 *
 * - the sidebar menu: a .treeview > a opens and closes its .treeview-menu
 * - the sidebar toggle [data-bs-toggle="offcanvas"]: body.sidebar-collapse / body.sidebar-open
 * - the boxes: [data-widget="collapse"] and [data-widget="remove"], $.fn.activateBox / toggleBox / removeBox
 * - the control sidebar [data-bs-toggle="control-sidebar"]
 * - the tooltips [data-bs-toggle="tooltip"], also the ones added later
 * - .btn-group[data-bs-toggle="btn-toggle"]
 * - the content area at least as tall as the window
 *
 * $.AdminLTE.options and window.AdminLTEOptions are read as before.
 */
(function ($) {
    'use strict';

    if (typeof $ === 'undefined') {
        throw new Error('AdminLTE requires jQuery');
    }

    $.AdminLTE = $.AdminLTE || {};
    $.AdminLTE.options = $.extend(true, {
        animationSpeed: 500,
        sidebarToggleSelector: "[data-bs-toggle='offcanvas']",
        sidebarPushMenu: true,
        enableBSToppltip: true,
        BSTooltipSelector: "[data-bs-toggle='tooltip']",
        enableControlSidebar: true,
        controlSidebarOptions: {
            toggleBtnSelector: "[data-bs-toggle='control-sidebar']",
            selector: '.control-sidebar',
            slide: true
        },
        enableBoxWidget: true,
        boxWidgetOptions: {
            boxWidgetIcons: { collapse: 'fa-minus', open: 'fa-plus', remove: 'fa-times' },
            boxWidgetSelectors: { remove: '[data-widget="remove"]', collapse: '[data-widget="collapse"]' }
        },
        screenSizes: { xs: 480, sm: 768, md: 992, lg: 1200 }
    }, $.AdminLTE.options || {});

    var options = function () {
        return $.AdminLTE.options;
    };

    $.AdminLTE.layout = {
        activate: function () {
            var self = this;
            self.fix();
            $(window).on('resize', function () {
                self.fix();
            });
        },
        fix: function () {
            var $content = $('.content-wrapper, .right-side');
            var windowHeight = $(window).height();
            var footer = $('.main-footer').outerHeight() || 0;
            if ($('body').hasClass('fixed')) {
                $content.css('min-height', windowHeight - footer);
                return;
            }
            var header = $('.main-header').outerHeight() || 0;
            var sidebar = $('.sidebar').height() || 0;
            $content.css('min-height', windowHeight >= sidebar ? windowHeight - header - footer : sidebar);
        }
    };

    $.AdminLTE.tree = function (menu) {
        var speed = options().animationSpeed;
        $(document).on('click', menu + ' li a', function (e) {
            var $this = $(this);
            var $next = $this.next();
            if (!$next.is('.treeview-menu')) {
                return;
            }
            e.preventDefault();
            if ($next.is(':visible') && !$('body').hasClass('sidebar-collapse')) {
                $next.slideUp(speed, function () {
                    $next.removeClass('menu-open');
                });
                $next.parent('li').removeClass('active');
            } else if (!$next.is(':visible')) {
                var $parent = $this.parents('ul').first();
                $parent.find('ul:visible').slideUp(speed).removeClass('menu-open');
                var $li = $this.parent('li');
                $next.slideDown(speed, function () {
                    $next.addClass('menu-open');
                    $parent.find('li.active').removeClass('active');
                    $li.addClass('active');
                    $.AdminLTE.layout.fix();
                });
            }
        });
    };

    $.AdminLTE.pushMenu = {
        activate: function (toggleBtn) {
            var self = this;
            var sm = options().screenSizes.sm;
            $(document).on('click', toggleBtn, function (e) {
                e.preventDefault();
                var $body = $('body');
                if ($(window).width() > sm - 1) {
                    if ($body.hasClass('sidebar-collapse')) {
                        $body.removeClass('sidebar-collapse').trigger('expanded.pushMenu');
                    } else {
                        $body.addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                    }
                } else if ($body.hasClass('sidebar-open')) {
                    $body.removeClass('sidebar-open sidebar-collapse').trigger('collapsed.pushMenu');
                } else {
                    $body.addClass('sidebar-open').trigger('expanded.pushMenu');
                }
            });
            $(document).on('click', '.content-wrapper', function () {
                if ($(window).width() <= sm - 1 && $('body').hasClass('sidebar-open')) {
                    $('body').removeClass('sidebar-open');
                }
            });
            self.activated = true;
        }
    };

    $.AdminLTE.controlSidebar = {
        activate: function () {
            var self = this;
            var o = options().controlSidebarOptions;
            $(document).on('click', o.toggleBtnSelector, function (e) {
                e.preventDefault();
                var $sidebar = $(o.selector);
                if ($sidebar.hasClass('control-sidebar-open') || $('body').hasClass('control-sidebar-open')) {
                    self.close($sidebar, o.slide);
                } else {
                    self.open($sidebar, o.slide);
                }
            });
        },
        open: function ($sidebar, slide) {
            if (slide) {
                $sidebar.addClass('control-sidebar-open');
            } else {
                $('body').addClass('control-sidebar-open');
            }
        },
        close: function ($sidebar, slide) {
            if (slide) {
                $sidebar.removeClass('control-sidebar-open');
            } else {
                $('body').removeClass('control-sidebar-open');
            }
        }
    };

    $.AdminLTE.boxWidget = {
        selectors: options().boxWidgetOptions.boxWidgetSelectors,
        icons: options().boxWidgetOptions.boxWidgetIcons,
        animationSpeed: options().animationSpeed,
        activate: function (scope) {
            var self = this;
            $(scope || document).on('click', self.selectors.collapse, function (e) {
                e.preventDefault();
                self.collapse($(this));
            }).on('click', self.selectors.remove, function (e) {
                e.preventDefault();
                self.remove($(this));
            });
        },
        collapse: function ($button) {
            var self = this;
            var $box = $button.parents('.box').first();
            var $body = $box.find('> .box-body, > .box-footer, > form > .box-body, > form > .box-footer');
            if ($box.hasClass('collapsed-box')) {
                $button.children(':first').removeClass(self.icons.open).addClass(self.icons.collapse);
                $body.slideDown(self.animationSpeed, function () {
                    $box.removeClass('collapsed-box');
                });
            } else {
                $button.children(':first').removeClass(self.icons.collapse).addClass(self.icons.open);
                $body.slideUp(self.animationSpeed, function () {
                    $box.addClass('collapsed-box');
                });
            }
        },
        remove: function ($button) {
            $button.parents('.box').first().slideUp(this.animationSpeed);
        }
    };

    $.fn.activateBox = function () {
        $.AdminLTE.boxWidget.activate(this);
        return this;
    };
    $.fn.toggleBox = function () {
        $.AdminLTE.boxWidget.collapse($($.AdminLTE.boxWidget.selectors.collapse, this));
        return this;
    };
    $.fn.removeBox = function () {
        $.AdminLTE.boxWidget.remove($($.AdminLTE.boxWidget.selectors.remove, this));
        return this;
    };

    $(function () {
        $('body').removeClass('hold-transition');
        if (typeof window.AdminLTEOptions !== 'undefined') {
            $.extend(true, $.AdminLTE.options, window.AdminLTEOptions);
        }
        var o = options();

        $.AdminLTE.layout.activate();
        $.AdminLTE.tree('.sidebar');
        if (o.enableControlSidebar) {
            $.AdminLTE.controlSidebar.activate();
        }
        if (o.sidebarPushMenu) {
            $.AdminLTE.pushMenu.activate(o.sidebarToggleSelector);
        }
        if (o.enableBSToppltip && window.bootstrap && window.bootstrap.Tooltip) {
            // one delegated tooltip for the triggers added later (as $('body').tooltip({selector}))
            new window.bootstrap.Tooltip(document.body, { selector: o.BSTooltipSelector });
        }
        if (o.enableBoxWidget) {
            $.AdminLTE.boxWidget.activate();
        }
        $(document).on('click', '.btn-group[data-bs-toggle="btn-toggle"] .btn', function (e) {
            $(this).closest('.btn-group').find('.btn.active').removeClass('active');
            $(this).addClass('active');
            e.preventDefault();
        });
    });
})(window.jQuery);
