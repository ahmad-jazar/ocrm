
define('advanced:views/workflow/actions/update-created-entity',
['advanced:views/workflow/actions/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/actions/update-created-entity',

        type: 'updateCreatedEntity',

        defaultActionData: {
            target: null,
            fieldList: [],
            fields: {},
        },

        data: function () {
            var data = Dep.prototype.data.call(this);

            if (this.actionData.target) {
                var aliasId = this.actionData.target.substr(8);

                if (this.options.flowchartCreatedEntitiesData[aliasId]) {
                    var link = this.options.flowchartCreatedEntitiesData[aliasId].link;
                    var entityType = this.options.flowchartCreatedEntitiesData[aliasId].entityType;
                    var numberId = this.options.flowchartCreatedEntitiesData[aliasId].numberId;
                    var text = this.options.flowchartCreatedEntitiesData[aliasId].text;

                    if (link) {
                        data.linkTranslated = this.translate(link, 'links', this.entityType);
                    }

                    data.entityTypeTranslated = this.translate(entityType, 'scopeNames');
                    data.numberId = numberId;
                    data.text = text;
                }
            }

            return data;
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            if (this.actionData.target) {
                var id = this.actionData.target;

                if (id.indexOf('created:') === 0) {
                    id = id.substr(8);
                }

                if (this.options.flowchartCreatedEntitiesData[id]) {
                    this.linkedEntityName = this.options.flowchartCreatedEntitiesData[id].entityType;
                }
            }
        },
    });
});
