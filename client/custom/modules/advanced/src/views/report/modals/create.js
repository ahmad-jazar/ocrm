
define('advanced:views/report/modals/create', ['views/modal', 'model'], function (Dep, Model) {

    return Dep.extend({

        cssName: 'create-report',

        template: 'advanced:report/modals/create',

        data: function () {
            return {
                entityTypeList: this.entityTypeList,
                typeList: this.typeList,
            };
        },

        events: {
            'click [data-action="create"]': function (e) {
                let type = $(e.currentTarget).data('type');

                /** @type {module:views/fields/base.Class} */
                let entityTypeView = this.getView('entityType');

                entityTypeView.fetch();
                entityTypeView.validate();

                let entityType = this.model.get('entityType')

                if (!entityType) {
                    return;
                }

                this.trigger('create', {
                    type: type,
                    entityType: entityType,
                });
            }
        },

        setup: function () {
            this.buttonList = [
                {
                    name: 'cancel',
                    label: 'Cancel',
                    onClick: dialog => {
                        dialog.close();
                    },
                }
            ];

            this.typeList = this.getMetadata().get('entityDefs.Report.fields.type.options');

            let scopes = this.getMetadata().get('scopes');
            let entityListToIgnore = this.getMetadata().get('entityDefs.Report.entityListToIgnore') || [];
            let entityListAllowed = this.getMetadata().get('entityDefs.Report.entityListAllowed') || [];

            this.entityTypeList = Object.keys(scopes)
                .filter(scope => {
                    if (~entityListToIgnore.indexOf(scope)) {
                        return;
                    }

                    if (!this.getAcl().check(scope, 'read')) {
                        return;
                    }

                    let defs = scopes[scope];

                    return (
                        defs.entity &&
                        (defs.tab || defs.object || ~entityListAllowed.indexOf(scope))
                    );
                })
                .sort((v1, v2) => {
                     return this.translate(v1, 'scopeNamesPlural')
                         .localeCompare(this.translate(v2, 'scopeNamesPlural'));
                });

            this.entityTypeList.unshift('');

            this.model = new Model();

            this.createView('entityType', 'views/fields/enum', {
                model: this.model,
                mode: 'edit',
                name: 'entityType',
                el: this.getSelector() + ' [data-name="entityType"]',
                params: {
                    options: this.entityTypeList,
                    translation: 'Global.scopeNames',
                    required: true,
                },
                labelText: this.translate('entityType', 'fields', 'Report'),
            });

            this.header = this.translate('Create Report', 'labels', 'Report');
        },
    });
});
