jQuery(function($) {
    console.log('Weighted search in CP');

    $( "#tabs" ).tabs();

});
(function($) {
    "use strict";

    var pluginName = 'weightedSearch';

    var WeightedSearch = function(el, options) {
        this.$el = $(el);

        // Set options for this instance, inheriting from the default options
        this.opts = $.extend({}, plugin.defaults, options);

        // Set up everything else
        this.bindEvents();
    };

    // Define class methods as normal, using WeightedSearch.prototype.name

    /* Set up event listeners */
    WeightedSearch.prototype.bindEvents = function() {
        // For example, bind to the click event
        this.$el.click(function() {
          console.log('Click it!');
          var for_field = $(this).data('for');
          var $table = $(this).siblings('table.add-field-value-table');
          var count = $table.find('tr').length;
          console.log(for_field);
          console.log($table);
          console.log(count);

          $table.find('tbody')
            .append($('<tr>')
              .append($('<td>')
                .append($('<input>')
                  .attr('class', 'input')
                  .attr('type', 'text')
                  .attr('name', 'special[' + for_field + ']['+ count +'][expression]')
                )
              )
              .append($('<td>')
                .append($('<input>')
                  .attr('class', 'input')
                  .attr('type', 'text')
                  .attr('name', 'special[' + for_field + ']['+ count +'][weight]')
                  // .attr('name', '"' + for_field['vw']['weight'] + '"')
                )
              )
              .append($('<td>')
                .append($('<a>')
                  .attr('class', 'remove')
                  .attr('onClick', 'jQuery(this).closest(\'tr\').remove()')
                  .html('<img src="/themes/cp_themes/default/images//remove_layout.png" />')
                  // .attr('name', '"' + for_field['vw']['weight'] + '"')
                )
              )
            );
          // $table.find('tbody')
          //   .append($('<tr>')
          //     .append($('<td>')
          //       .append($('input')
          //         .val('Test')
          //       )
          //     )
          //     // .append($('<td>')
          //     //   .append($('input')
          //     //     .val('Weight')
          //     //   )
          //     // )
          //   );
          // $('')
        });
    };

    /* Do something to this instance */
    WeightedSearch.prototype.doSomething = function(name, options) {
        this.$el.text(name);
        this.$el.attr(options);
    };

    // The following functions, on the WeightedSearch object itself, will be callable
    // using the pattern:
    //
    //     $el.pluginName('doSomething', arg1, arg2, ...);

    /* Do something to all of the elements in $els */
    WeightedSearch.doSomething = function($els, name, options) {
        $els.each(function() {
            WeightedSearch.getOrCreate(this).doSomething(name, options);
        });
        return $els;
    }

    /* Create - or return the existing - instance of WeightedSearch for this element */
    WeightedSearch.getOrCreate = function(el, options) {
        var rev = $(el).data(pluginName);
        if (!rev) {
            rev = new WeightedSearch(el, options);
            $(el).data(pluginName, rev);
        }

        return rev;
    };

    /*
     * Expose the plugin to jQuery. It can be started using $el.pluginName([options]).
     *
     * Methods exposed above on the WeightedSearch object will be accessible using
     *
     *     $el.pluginName('methodName', arg1, arg2);
     */
    var plugin = $.fn[pluginName] = function() {
        var options, fn, args;
        // Create a new WeightedSearch for each element in the selector
        if (arguments.length === 0 || (arguments.length === 1 && $.type(arguments[0]) != 'string')) {
            options = arguments[0];
            return this.each(function() {
                return WeightedSearch.getOrCreate(this, options);
            });
        }

        // Call a function on each WeightedSearch in the selector
        fn = arguments[0];
        args = $.makeArray(arguments).slice(1);

        if (fn in WeightedSearch) {
            // Call the WeightedSearch class method if it exists
            args.unshift(this);
        return WeightedSearch[fn].apply(WeightedSearch, args);
        } else {
            throw new Error("Unknown function call " + fn + " for $.fn." + pluginName);
        }
    };

    // Expose the WeightedSearch and defaults, accessibly via $.fn.pluginName.WeightedSearch
    plugin.WeightedSearch = WeightedSearch;
    plugin.defaults = {
        // default options
    };

    $('.add-field-value-weight').weightedSearch();

})(jQuery);
