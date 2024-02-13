
define('advanced:views/workflow-log-record/record/list', ['views/record/list'], function (Dep) {

    return Dep.extend({

    	massActionList: ['remove'],
        rowActionsView: 'views/record/row-actions/remove-only',
    });
});
