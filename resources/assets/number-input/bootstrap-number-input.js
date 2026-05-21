/* ========================================================================
 * bootstrap-spin - v1.0
 * https://github.com/wpic/bootstrap-spin
 * ========================================================================
 * Copyright 2014 WPIC, Hamed Abdollahpour
 *
 * ========================================================================
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================================
 */

(function ($) {

    $.fn.bootstrapNumber = function (options) {

        var settings = $.extend({
            upClass: 'default',
            downClass: 'default',
            center: true
        }, options);

        return this.each(function (e) {
            var self = $(this);
            var clone = self.clone();

            var min = self.attr('min');
            var max = self.attr('max');
            var allowNonNumeric = clone.data('allow-nonnumeric') || clone.attr('data-allow-nonnumeric');
            var datePattern = /^(\d{4})-(\d{2})-(\d{2})$/;

            function setText(n) {
                if (allowNonNumeric) {
                    return false;
                }
                if ((min && n < min) || (max && n > max)) {
                    return false;
                }

                clone.focus().val(n);
                return true;
            }

            function setYearDateDelta(delta) {
                var val = String(clone.val() || '');
                var match = val.match(datePattern);
                if (!match) {
                    return false;
                }
                var year = parseInt(match[1], 10) + delta;
                if (isNaN(year)) {
                    return false;
                }
                clone.focus().val(year + '-01-01');
                return true;
            }

            var group = $("<div class='input-group'></div>");
            var down = $("<button type='button'>-</button>").attr('class', 'btn btn-' + settings.downClass).click(function () {
                var val = clone.val();
                if (val === '') return; 
                if (allowNonNumeric || datePattern.test(val)) {
                    setYearDateDelta(-1);
                    return;
                }
                setText(parseInt(val) - 1);
            });

            var up = $("<button type='button'>+</button>").attr('class', 'btn btn-' + settings.upClass).click(function () {
                var val = clone.val();
                if (val === '') return;
                if (allowNonNumeric || datePattern.test(val)) {
                    setYearDateDelta(1);
                    return;
                }
                setText(parseInt(val) + 1);
            });

            $("<span class='input-group-btn'></span>").append(down).appendTo(group);
            clone.appendTo(group);
            if (clone) {
                clone.css('text-align', 'center');
            }
            $("<span class='input-group-btn'></span>").append(up).appendTo(group);

            // remove spins from original
            clone.prop('type', 'text').keydown(function (e) {
                if (allowNonNumeric) {
                    return;
                }
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                    (e.keyCode == 65 && e.ctrlKey === true) ||
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }

                var c = String.fromCharCode(e.which);
                var n = parseInt(clone.val() + c);

                //if ((min && n < min) || (max && n > max)) {
                //    e.preventDefault();
                //}
            });

            clone.prop('type', 'text').blur(function (e) {
                if (allowNonNumeric) {
                    return;
                }
                var c = String.fromCharCode(e.which);
                var n = parseInt(clone.val() + c);
                if ((min && n < min)) {
                    setText(min);
                }
                else if (max && n > max) {
                    setText(max);
                }
            });


            self.replaceWith(group);
        });
    };
}(jQuery));
