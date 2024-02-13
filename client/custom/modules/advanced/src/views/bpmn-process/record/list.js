
define('advanced:views/bpmn-process/record/list',
['views/record/list'], function (Dep) {

    return Dep.extend({

        massActionList: ['remove', 'massUpdate']

    });
});
