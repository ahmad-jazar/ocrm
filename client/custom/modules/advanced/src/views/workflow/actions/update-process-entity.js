
define('advanced:views/workflow/actions/update-process-entity', ['advanced:views/workflow/actions/base'], function (Dep) {

    return Dep.extend({

        type: 'updateProcessEntity',

        defaultActionData: {
            fieldList: [],
            fields: {},
        },

        data: function () {
            var data = Dep.prototype.data.call(this);

            data.noEntityName = true;

            return data;
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            this.displayedLinkedEntityName = this.translate('BpmnProcess', 'scopeNames');
        },
    });
});
