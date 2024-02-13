
define('advanced:views/workflow/actions/create-related-entity', ['advanced:views/workflow/actions/base'], function (Dep) {

    return Dep.extend({

        type: 'createRelatedEntity',

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

            data.numberId = this.numberId;
            data.aliasId = this.aliasId;

            return data;
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.numberId = null;

            if (this.options.flowchartElementId && this.options.flowchartCreatedEntitiesData) {
                var aliasId = this.options.flowchartElementId + '_' + this.actionData.id;

                if (aliasId in this.options.flowchartCreatedEntitiesData) {
                    this.numberId = this.options.flowchartCreatedEntitiesData[aliasId].numberId;
                }

                this.aliasId = aliasId;
            }
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            if (this.actionData.link) {
                this.linkedEntityName = this.getMetadata()
                    .get('entityDefs.' + this.entityType + '.links.' + this.actionData.link + '.entity');
            }
        },
    });
});

