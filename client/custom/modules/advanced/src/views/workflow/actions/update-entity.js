
define('advanced:views/workflow/actions/update-entity', ['advanced:views/workflow/actions/base'], function (Dep) {

    return Dep.extend({

        type: 'updateEntity',

        defaultActionData: {
            fieldList: [],
            fields: {},
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            this.displayedLinkedEntityName = this.translate(this.entityType, 'scopeNames');
        },
    });
});
