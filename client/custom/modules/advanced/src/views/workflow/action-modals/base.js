
define('advanced:views/workflow/action-modals/base',
['views/modal', 'advanced:views/workflow/actions/base'], function (Dep, ActionBase) {

    return Dep.extend({

        template: 'advanced:workflow/action-modals/base',

        data: function () {
            return {};
        },

        setup: function () {
            this.actionData = this.options.actionData || {};

            this.actionDataInitial = Espo.Utils.cloneDeep(this.actionData);

            this.actionType = this.options.actionType;
            this.entityType = this.options.entityType;

            this.once('close', () => {
                if (!this.isApplied) {
                    if (this.actionDataInitial && this.actionData) {
                        for (var i in this.actionDataInitial) {
                            this.actionData[i] = this.actionDataInitial[i];
                        }
                    }
                }

                this.isApplied = false;
            });

            this.buttonList = [
                {
                    name: 'apply',
                    label: 'Apply',
                    style: 'primary',
                    onClick: () => {
                        if (this.fetch()) {
                            this.isApplied = true;

                            this.trigger('apply', this.actionData);
                            this.close();
                        }
                    },
                },
                {
                    name: 'cancel',
                    label: 'Cancel',
                    onClick: dialog => {
                        this.trigger('cancel');
                        dialog.close();
                    },
                }
            ];

            this.header = this.translate(this.actionType, 'actionTypes', 'Workflow');
        },

        translateCreatedEntityAlias: function (target, optionItem) {
            return ActionBase.prototype.translateCreatedEntityAlias.call(this, target, optionItem);
        },

        getEntityTypeFromTarget: function (target, targetEntityType) {
            if (target && target.indexOf('created:') === 0) {
                var aliasId = target.substr(8);

                if (!this.options.flowchartCreatedEntitiesData[aliasId]) {
                    return null;
                }

                return this.options.flowchartCreatedEntitiesData[aliasId].entityType;
            }

            if (target && target.indexOf('link:') === 0) {
                var linkPath = target.substr(5);
                var linkList = linkPath.split('.');

                let entityType = targetEntityType || this.entityType;

                linkList.forEach(link => {
                    if (!entityType) {
                        return;
                    }

                    entityType = this.getMetadata().get(['entityDefs', entityType, 'links', link, 'entity']);
                });

                return entityType;
            }

            let entityType = targetEntityType || this.entityType;

            if (target === 'followers') {
                return 'User';
            }

            if (target === 'currentUser') {
                return 'User';
            }

            if (target === 'targetEntity') {
                return entityType;
            }

            if (!target) {
                return entityType;
            }

            return null;
        },

        translateTargetItem: function (target, optionItem, targetEntityType) {
            return ActionBase.prototype.translateTargetItem.call(this, target, optionItem, targetEntityType);
        },
    });
});
