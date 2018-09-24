$(document).ready(function(){

    var cwdGrid = function(selector) {
        var $grid = $(selector);
        var $headers = $grid.find('th');
        var $params = new URLSearchParams(window.location.search);

        // Filter state
        this.getFilters = function () {
            var $filters = [];
            $params.delete('filter');
            $grid.find(".filter").each(function () {
                value = $(this).val();
                if (value === '') {
                    return;
                }

                $filters.push({
                    'operator': $(this).data('operator'),
                    'value': value,
                    'property': $(this).data('field')
                });
            });
            if ($filters.length > 0) {
                $params.set('filter', encodeURIComponent(JSON.stringify($filters)));
            }
        };
        this.getFilters();


        // Sorting
        $headers.each(function (idx) {
            if (!$(this).hasClass('sortable')) {
                return;
            }

            if ($(this).hasClass('sorted')) {
                if ($(this).hasClass('ASC')) {
                    $(this).append('<i class="fas fa-sort-alpha-down pull-right"></i>');
                } else if ($(this).hasClass('DESC')) {
                    $(this).append('<i class="fas fa-sort-alpha-up pull-right"></i>');
                }
            }

            $(this).on('click', function (e) {
                $params.set('sortDir', 'ASC');
                if ($(this).hasClass('sorted')) {
                    if ($(this).hasClass('ASC')) {
                        $params.set('sortDir', 'DESC');
                    }
                }
                $params.set('sortField', $(this).data('field'));
                $params.set('page', 1);

                document.location.href = '?' + $params.toString();
            });

            $(this).css('cursor', 'pointer');
        });

        // List length
        $grid.find('select.listLengthSelector').on('change', function () {
            $params.set('limit', $(this).val());
            $params.set('page', 1);
            document.location.href = '?' + $params.toString();
        });

        // Filter trigger
        var filterTimeout;
        var filterPage = function () {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function () {
                getFilters();
                $params.set('page', 1);
                document.location.href = '?' + $params.toString();
            }, 500);
        };
        $grid.find(".filter").on('change', function () {
            filterPage();
        });
        $grid.find(".filter").on('keyup', function () {
            filterPage();
        });
    };

    cwdGrid('table.cwd-grid');

});