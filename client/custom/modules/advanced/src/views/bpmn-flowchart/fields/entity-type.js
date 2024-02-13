
define(
    'advanced:views/bpmn-flowchart/fields/entity-type',
    ['views/fields/enum', 'advanced:bpmn-element-helper'], function (Dep, ElementHelper) {

    return Dep.extend({

        setupOptions: function () {
            let helper = new ElementHelper(this.getHelper(), this.model);

            this.params.options = helper.getTargetEntityTypeList();
            this.params.options.unshift('');
            this.params.translation = 'Global.scopeNames';
        },
    });
});
