
define('advanced:views/target-list/fields/sync-with-reports', ['views/fields/link-multiple'], function (Dep) {

    return Dep.extend({

        getSelectPrimaryFilterName: function () {
            return 'listTargets';
        },
    });
});
