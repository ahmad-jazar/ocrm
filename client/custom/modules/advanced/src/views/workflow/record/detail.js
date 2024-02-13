
define('advanced:views/workflow/record/detail', 'views/record/detail', function (Dep) {

    return Dep.extend({

        editModeEnabled: false,

        editModeDisabled: true,

        bottomView: 'advanced:views/workflow/record/detail-bottom',

        duplicateAction: true,

        stickButtonsContainerAllTheWay: true,

        saveAndContinueEditingAction: true,

        setup: function () {
            Dep.prototype.setup.call(this);
            this.manageFieldsVisibility();
            this.listenTo(this.model, 'change', function (model, options) {
                if (this.model.hasChanged('portalOnly') || this.model.hasChanged('type')) {
                    this.manageFieldsVisibility(options.ui);
                }
            }, this);

            if (!this.model.isNew()) {
                this.setFieldReadOnly('type');
                this.setFieldReadOnly('entityType');
            }
        },

        manageFieldsVisibility: function (ui) {
            let type = this.model.get('type');

            if (
                this.model.get('portalOnly') &&
                ~['afterRecordSaved', 'afterRecordCreated', 'afterRecordUpdated', 'signal'].indexOf(type)
            ) {
                this.showField('portal');
            } else {
                this.hideField('portal');
            }

            if (type !== 'scheduled') {
                this.hideField('targetReport');
                this.hideField('scheduling');
                this.setFieldNotRequired('targetReport');
            }

            if (type === 'manual') {
                this.hideField('portalOnly');
                this.hideField('portal');

                if (this.mode === 'edit' && ui) {
                    setTimeout(() => {
                        this.model.set({
                            'portalId': null,
                            'portalName': null,
                            'portalOnly': false
                        });
                    }, 100);
                }

                return;
            }

            if (type === 'scheduled') {
                this.showField('targetReport');
                this.showField('scheduling');
                this.setFieldRequired('targetReport');
                this.hideField('portal');
                this.hideField('portalOnly');

                if (this.mode === 'edit' && ui) {
                    setTimeout(() => {
                        this.model.set({
                            'portalId': null,
                            'portalName': null,
                            'portalOnly': false
                        });
                    }, 100);
                }

                return;
            }

            if (type === 'sequential') {
                this.hideField('portal');
                this.hideField('portalOnly');

                if (this.mode === 'edit' && ui) {
                    setTimeout(() => {
                        this.model.set({
                            'portalId': null,
                            'portalName': null,
                            'portalOnly': false
                        });
                    }, 100);
                }

                return;
            }

            if (this.model.get('portalOnly')) {
                this.showField('portal');
            }

            this.showField('portalOnly');
        },
    });
});
