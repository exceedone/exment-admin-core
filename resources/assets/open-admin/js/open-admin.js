/*-------------------------------------------------*/
/* main init */
/*-------------------------------------------------*/

let admin = {};

admin.ajax = {}; // ajax loading
admin.pages = {}; // shared logic for pages
admin.form = {}; // form in page
admin.grid = {}; // grid / lister
admin.action = {}; // actions

document.addEventListener('DOMContentLoaded', function () {
    admin.init();
});

admin.init = function () {
    admin.menu.init();
    admin.ajax.init();
    admin.pages.init();
};

/*-------------------------------------------------*/
/* menu */
/*-------------------------------------------------*/

admin.menu = {
    init: function () {
        // let menuToggle = document.getElementById('menu-toggle');

        // menuToggle.addEventListener('click', function () {
        //     if (!document.body.classList.contains('side-menu-closed')) {
        //         admin.menu.close();
        //     }

        //     if (window.innerWidth < 576) {
        //         document.body.classList.toggle('side-menu-open');
        //         document.body.classList.remove('side-menu-closed');
        //     } else {
        //         document.body.classList.toggle('side-menu-closed');
        //         document.body.classList.remove('side-menu-open');
        //     }
        // });

        window.addEventListener('resize', function () {
            if (window.innerWidth < 576) {
                document.body.classList.remove('side-menu-closed');
            }
        });

        function removeActiveClass() {
            let activeElements = document.querySelectorAll('.custom-menu > ul > li.active');
            for (let j = 0; j < activeElements.length; j++) {
                activeElements[j].classList.remove('active');
            }
        }

        let elements = document.querySelectorAll('.custom-menu > ul > li > a');
        for (let i = 0; i < elements.length; i++) {
            elements[i].addEventListener(
                'click',
                function () {
                    admin.menu.close();
                    removeActiveClass();
                    this.parentNode.classList.add('active');
                },
                false
            );
        }
        this.initSearch();
    },

    close: function () {
        let open_list = document.getElementById('menu').getElementsByClassName('show');
        for (let is_open of open_list) {
            is_open.previousElementSibling.click();
        }
    },

    initSearch: function () {
        let search_menu = document.querySelector('.sidebar-form .dropdown-menu');
        let search_field = document.querySelector('.sidebar-form .autocomplete');
        let selectedIndex = -1;

        let searchMenu = function (event) {
            if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
                let up = event.key === 'ArrowUp';
                menuItemSelect(up);
                event.preventDefault();
                return false;
            } else if (event.key === 'Enter') {
                search_menu.querySelector('a.selected').click();
            } else {
                selectedIndex = -1;
                let selectedItems = search_menu.querySelector('a.selected');
                if (selectedItems) {
                    selectedItems.classList.remove('selected');
                }
            }

            let text = this.value;

            if (text === '') {
                hide(search_menu);
                return;
            }

            let regex = new RegExp(text, 'i');
            let matched = false;

            search_menu.querySelectorAll('li').forEach((li) => {
                let a = li.querySelector('a');
                if (!regex.test(a.textContent)) {
                    hide(li);
                    li.classList.remove('shown');
                    a.classList.remove('selected');
                } else {
                    show(li);
                    li.classList.add('shown');
                    matched = true;
                }
            });

            if (matched) {
                show(search_menu);
            }
        };

        function menuItemSelect(up) {
            let shownItem = search_menu.querySelectorAll('li.shown');
            if (up) {
                selectedIndex--;
            } else {
                selectedIndex++;
            }
            if (selectedIndex > shownItem.length) {
                selectedIndex = 0;
            }
            if (selectedIndex < 0) {
                selectedIndex = shownItem.length;
            }
            let i = 0;

            shownItem.forEach((li) => {
                let a = li.querySelector('a');
                a.classList.remove('selected');
                if (i === selectedIndex) {
                    a.classList.add('selected');
                }
                i++;
            });
        }

        let hideSearchMenu = function () {
            hide(search_menu);
            search_field.value = '';
        };

        if (search_field) {
            search_field.addEventListener('keyup', searchMenu);
            search_field.addEventListener('focus', searchMenu);
            document.addEventListener('click', hideSearchMenu);
        }
    },

    setActivePage: function (url) {
        let menuItems = document.querySelectorAll('#menu a');
        menuItems.forEach((a) => {
            let li = a.parentNode;
            li.classList.remove('active');
            a.blur();
            if (a.attributes['href'].value === url) {
                let parent = li.parentNode;

                if (!parent.classList.contains('show')) {
                    li.parentNode.classList.add('show');
                }
                if (parent.id === 'menu') {
                    admin.menu.close();
                } else {
                    li.parentNode.parentNode.classList.add('active');
                }
                li.classList.add('active');
            }
        });
    },
};

/*-------------------------------------------------*/
/* page loading */
/*-------------------------------------------------*/

let preventPopState;

admin.ajax = {
    currenTarget: false,
    defaults: {
        headers: { 'X-PJAX': true, 'X-PJAX-CONTAINER': '#pjax-container', 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html, application/json, text/plain, */*' },
        method: 'get',
    },

    init: function () {
        // history back
        // window.onpopstate = function (event) {
            
        //     preventPopState = true;
        //     // Restore scroll position from browser history state when user clicks back/forward
        //     let scrollY = event.state && event.state.scrollY ? event.state.scrollY : 0;

        //     $.pjax({
        //         url: document.location.href,
        //         container: '#pjax-container',
        //         timeout: 2000,
        //         scrollTo: false // Disable PJAX auto-scroll to prevent jumping to top
        //     }).done(function () {
        //         // Restore the saved scroll position after PJAX content is loaded
        //         if (scrollY > 0) {
        //             setTimeout(() => window.scrollTo(0, scrollY), 50);
        //         }
        //     });
        // };

        // link in content and menu

        // forms that should be submitted with ajax
        // now handled by admin.form.initAjax()
        // also needs to work for widgets

        NProgress.configure({ parent: '#app' });
    },

    // use navigate when you want history working
    // and the url to be changed
    navigate: function (url, preventPopState) {
        admin.collectGarbage();
        if (window.innerWidth < 540) {
            document.body.classList.remove('side-menu-closed');
            document.body.classList.remove('side-menu-open');
        }

        if (url != document.location.href) {
            if (!preventPopState) {
                this.setUrl(url);
            }
            admin.menu.setActivePage(url);
        }

        this.load(url);
    },

    setUrl: function (url) {
        if (url != document.location.href && !admin.ajax.currenTarget) {
            // Save current scroll position before navigating to new page
            let scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
            // Store scroll position in browser history state for later restoration
            history.pushState({ scrollY: scrollY }, '', url);
        }
    },

    reload: function () {
        preventPopState = true;
        this.navigate(document.location.href);
    },

    // use load for loading without history state
    // and don't refresh the url
    load: function (url, obj) {
        this.request(url, obj);
    },

    request: function (url, obj, result_function) {
        if (typeof obj == 'undefined') {
            obj = {};
        }

        NProgress.start();

        obj.url = url;
        let axios_obj = merge_default(this.defaults, obj);

        axios(axios_obj)
            .then(function (response) {
                if (typeof result_function === 'function') {
                    result_function(response);
                } else {
                    admin.ajax.done(response);
                }
            })
            .catch(function (error) {
                admin.ajax.error(error);
            })
            .then(function () {
                NProgress.done();
                if (typeof result_function == 'undefined' && !admin.ajax.currenTarget) {
                    admin.pages.init();
                }
            });
    },

    // posts and load this into the page
    loadPost: function (url, data) {
        let obj = {
            method: 'post',
            data: data,
        };
        obj.data._token = LA.token;
        this.request(url, obj);
    },

    /*
            NOTICE: axios automatically converts data to json string if its an object.
            also NOTE: axios.delete doesn't support _POST data. (dont use formData in combination with delete, just grab the vars from the json payload from the request)
            to send application/x-www-form-urlencoded data use formData object:

            const formData = new FormData();
            formData.append('name', value);
         */
    post: function (url, data, result_function) {
        let obj = {
            method: 'post',
            data: data,
            url: url,
        };
        obj.data._token = LA.token;
        this.request(url, obj, result_function);
    },

    get: function (url, data, result_function) {
        let obj = {
            method: 'get',
            data: data,
            url: url,
        };
        obj.data._token = LA.token;
        this.request(url, obj, result_function);
    },

    done: function (response) {
        if (window.location !== response.request.responseURL) {
            this.setUrl(response.request.responseURL);
        }

        let main = false;
        if (admin.ajax.currenTarget) {
            main = admin.ajax.currenTarget;
        }
        if (!main) {
            main = document.querySelector('#pjax-container');
        }

        let data = response.data;
        if (typeof data != 'string') {
            data = JSON.stringify(data);
        }
        main.innerHTML = data;

        main.querySelectorAll('script').forEach((script) => {
            var src = script.getAttribute('src');
            if (src) {
                script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = src;
                // Append to the main PJAX container if present, otherwise to body.
                // Avoid referencing a missing '#app' element which throws in some setups.
                try {
                    var _target = main || document.getElementById('app') || document.body;
                    _target.appendChild(script);
                } catch (e) {
                    document.body.appendChild(script);
                }
            } else {
                eval(script.innerText);
            }
        });

        if (!admin.ajax.currenTarget) {
            admin.pages.setTitle();
        }
    },

    error: function (error) {
        if (error.response) {
            console.log(error.response.data);
            console.log(error.response.status);
            console.log(error.response.headers);

            admin.ajax.done(error.response);
        } else if (error.request) {
            // The request was made but no response was received
            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
            // http.ClientRequest in node.js
            console.log(error.request);
        } else {
            // Something happened in setting up the request that triggered an Error
            console.log('An error has accurred:');
            console.log(error);
        }
    },
};

admin.pages = {
    init: function () {
        bindSubmitButtonWithLoading();
        this.setTitle();
        admin.menu.setActivePage(window.location.href);
        admin.grid.init();
        admin.grid.inline_edit.init();
        admin.form.init();
        this.initBootstrap();
    },

    setTitle: function () {
        if (document.querySelector('main h1')) {
            let h1_title = document.querySelector('main h1').innerText;
            if (h1_title) {
                document.title = 'Admin | ' + h1_title;
            }
        }
    },

    initBootstrap: function () {
        // popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]:not(.ie)'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"].enable-tooltip'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

};

admin.collectGarbage = function () {
    document.querySelectorAll('.flatpickr-calendar').forEach((cal) => {
        cal.remove();
    });
};

toastr.options = {
    closeButton: true,
    progressBar: true,
    showMethod: 'slideDown',
    timeOut: 4000
};

$.pjax.defaults.timeout = 5000;
$.pjax.defaults.maxCacheLength = 0;
$(document).on('pjax:timeout', function (event) {
    event.preventDefault();
})
$(document).pjax('a:not(a[target="_blank"]):not(a[data-nopjax])', {
    container: '#pjax-container'
});

$(document).on('submit', 'form[pjax-container]', function (event) {
    $.pjax.submit(event, '#pjax-container')
});

$(function () {
    $('.sidebar-menu li:not(.treeview) > a').on('click', function () {
        var $parent = $(this).parent().addClass('active');
        $parent.siblings('.treeview.active').find('> a').trigger('click');
        $parent.siblings().removeClass('active').find('li').removeClass('active');
    });
    var menu = $('.sidebar-menu li > a[href="' + (location.pathname + location.search + location.hash) + '"]').parent().addClass('active');
    menu.parents('ul.treeview-menu').addClass('menu-open');
    menu.parents('li.treeview').addClass('active');

    $('[data-toggle="popover"]').popover();

    // Sidebar form autocomplete
    $('.sidebar-form .autocomplete').on('keyup focus', function () {
        var $menu = $('.sidebar-form .dropdown-menu');
        var text = $(this).val();

        if (text === '') {
            $menu.hide();
            return;
        }

        var regex = new RegExp(text, 'i');
        var matched = false;

        $menu.find('li').each(function () {
            if (!regex.test($(this).find('a').text())) {
                $(this).hide();
            } else {
                $(this).show();
                matched = true;
            }
        });

        if (matched) {
            $menu.show();
        }
    }).click(function (event) {
        event.stopPropagation();
    });

    $('.sidebar-form .dropdown-menu li a').click(function () {
        $('.sidebar-form .autocomplete').val($(this).text());
    });
});


(function ($) {

    var Grid = function () {
        this.selects = {};
    };

    Grid.prototype.select = function (id) {
        this.selects[id] = id;
    };

    Grid.prototype.unselect = function (id) {
        delete this.selects[id];
    };

    Grid.prototype.selected = function () {
        var rows = [];
        $.each(this.selects, function (key, val) {
            rows.push(key);
        });

        return rows;
    };

    $.fn.admin = LA;
    $.admin = LA;
    $.admin.swal = swal;
    $.admin.toastr = toastr;
    $.admin.grid = new Grid();

    $.admin.reload = function () {
        $.pjax.reload('#pjax-container');
    };

    $.admin.redirect = function (url) {
        $.pjax({ container: '#pjax-container', url: url });
    };

    $.admin.getToken = function () {
        return $('meta[name="csrf-token"]').attr('content');
    };

})(jQuery);

$(document).on('pjax:end', function () {
    // Clean up all tooltips before reinitializing
    document.querySelectorAll('.tooltip').forEach(el => el.remove());
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        var instance = bootstrap.Tooltip.getInstance(el);
        if (instance) instance.dispose();
    });
});

// On mobile, tapping a tooltip trigger that opens a modal leaves the tooltip visible
// because no mouseleave/blur fires. Hide all visible tooltips when any modal starts to show.
document.addEventListener('show.bs.modal', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        var instance = bootstrap.Tooltip.getInstance(el);
        if (instance) instance.hide();
    });
});
$(document).on('pjax:send', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        const $form = $(xhr.relatedTarget);        
        const $submit_btn = $form.find(':submit');

        if ($submit_btn.length) {
            $submit_btn.data('original-text', $submit_btn.html());
            $submit_btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>loading...');
            $submit_btn.css('display', 'inline-flex');
            $submit_btn.css('align-items', 'center');
            $submit_btn.prop('disabled', true);
        }
    }
    NProgress.start();
});

$(document).on('pjax:complete', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        const $form = $(xhr.relatedTarget);
        const $submit_btn = $form.find(':submit');

        if ($submit_btn.length) {
            const originalText = $submit_btn.data('original-text');
            $submit_btn.html(originalText || '<i class="fa fa-search"></i>');
            $submit_btn.prop('disabled', false);
        }
    }
    NProgress.done();
    $.admin.grid.selects = {};
});


$.fn.editable.defaults.params = function (params) {
    params._token = LA.token;
    params._editable = 1;
    params._method = 'PUT';
    return params;
};

$.fn.editable.defaults.error = function (data) {
    var msg = '';
    if (data.responseJSON.errors) {
        $.each(data.responseJSON.errors, function (k, v) {
            msg += v + "\n";
        });
    }
    return msg
};