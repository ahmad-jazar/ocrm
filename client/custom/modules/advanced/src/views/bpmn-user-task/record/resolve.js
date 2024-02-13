
define('advanced:views/bpmn-user-task/record/resolve', ['views/record/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:bpmn-user-task/record/resolve',

        setup: function () {
            Dep.prototype.setup.call(this);
        },

        setupBeforeFinal: function () {
            this.dynamicLogicDefs = this.getMetadata().get('clientDefs.' + this.model.name + '.dynamicLogic') || {};
            Dep.prototype.setupBeforeFinal.call(this);

            this.createField('resolution', 'views/fields/enum', {}, 'edit');
            this.createField('resolutionNote', 'views/fields/text', {}, 'edit');

            this.once('after:render', () => {
                if (this.recordHelper.hasFieldOptionList('resolution')) {
                    this.getFieldView('resolution').setOptionList(this.recordHelper.getFieldOptionList('resolution'));
                }
            });
        }
    });
});
