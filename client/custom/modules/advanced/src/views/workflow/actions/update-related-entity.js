
define('advanced:views/workflow/actions/update-related-entity', ['advanced:views/workflow/actions/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/actions/update-related-entity',

        type: 'updateRelatedEntity',

        defaultActionData: {
            link: false,
            fieldList: [],
            fields: {},
        },

        data: function () {
            var data = Dep.prototype.data.call(this);

            if (this.actionData.link) {
                data.linkTranslated = this.translate(this.actionData.link, 'links', this.entityType);
            }

            if (this.actionData.parentEntityType) {
                data.parentEntityTypeTranslated = this.translate(this.actionData.parentEntityType, 'scopeNames');
            }

            return data;
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            if (this.actionData.link) {
                var linkData = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + this.actionData.link);

                this.linkedEntityName = linkData.entity || this.entityType;
                this.displayedLinkedEntityName = null;

                if (linkData.type === 'belongsToParent') {
                    this.linkedEntityName = this.actionData.parentEntityType || this.linkedEntityName;
                    this.displayedLinkedEntityName = this.translate(this.actionData.link, 'links' , this.entityType) +
                        ' <span class="chevron-right"></span> ' +
                        this.translate(this.actionData.parentEntityType, 'scopeNames');
                }
            }
        },
    });
});
