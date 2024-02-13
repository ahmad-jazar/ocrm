
define('advanced:views/bpmn-process/record/edit', 'views/record/edit', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);
            this.setupFlowchartDependency();
        },

        setupFlowchartDependency: function () {
            this.listenTo(this.model, 'change:flowchartId', function (model, value, o) {
                if (!o.ui) return;
                this.model.set({
                    'targetId': null,
                    'targetName': null
                });
                if (!value) {
                    this.model.set('startElementIdList', []);
                }

                this.model.set('name', this.model.get('flowchartName'));
            }, this);

            if (this.model.has('startElementIdList')) {
                this.showField('startElementId');
                this.setStartElementIdList(this.model.get('startElementIdList'));
            } else {
                this.hideField('startElementId');
            }

            this.listenTo(this.model, 'change:startElementIdList', function (model, value, o) {
                this.setStartElementIdList(value);
            }, this);
        },

        setStartElementIdList: function (value) {
            value = value || [];
            this.setFieldOptionList('startElementId', value);

            if (value.length) {
                this.model.set('startElementId', value[0]);
            } else {
                this.model.set('startElementId', null);
            }
            if (value.length > 0) {
                this.showField('startElementId');
            } else {
                this.hideField('startElementId');
            }
        },

    });
});
