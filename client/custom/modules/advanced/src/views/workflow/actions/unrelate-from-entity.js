
define('advanced:views/workflow/actions/unrelate-from-entity',
['advanced:views/workflow/actions/relate-with-entity'], function (Dep) {

    return Dep.extend({

        type: 'unrelateFromEntity',
    });
});
