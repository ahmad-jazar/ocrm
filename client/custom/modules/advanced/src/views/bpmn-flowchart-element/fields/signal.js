
define('advanced:views/bpmn-flowchart-element/fields/signal', ['views/fields/varchar'], function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            Dep.prototype.setupOptions.call(this);

            this.params.options = [
                'create.ENTITY_TYPE',
                'update.ENTITY_TYPE.ID',
                'delete.ENTITY_TYPE.ID',
                'relate.ENTITY_TYPE.ID.LINK_NAME',
                'relate.ENTITY_TYPE.ID.LINK_NAME.FOREIGN_ID',
                'unrelate.ENTITY_TYPE.ID.LINK_NAME',
                'unrelate.ENTITY_TYPE.ID.LINK_NAME.FOREIGN_ID',
            ];
        },
    });
});