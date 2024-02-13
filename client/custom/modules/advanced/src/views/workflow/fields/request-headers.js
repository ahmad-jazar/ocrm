
define('advanced:views/workflow/fields/request-headers', ['views/fields/array'], function (Dep) {

    return Dep.extend({

        maxItemLength: 10000,
    });
});