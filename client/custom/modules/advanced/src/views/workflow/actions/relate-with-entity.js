
define('advanced:views/workflow/actions/relate-with-entity',
['advanced:views/workflow/actions/base', 'model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow/actions/relate-with-entity',

        type: 'relateWithEntity',

        data: function () {
            let data = Dep.prototype.data.call(this);

            data.linkTranslated = this.translate(this.actionData.link, 'links', this.entityType);

            return data;
        },

        defaultActionData: {
            link: null
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            if (this.actionData.link) {
                this.foreignEntityType = this.getMetadata()
                    .get('entityDefs.' + this.entityType + '.links.' + this.actionData.link + '.entity');
            }

            this.model = new Model();

            this.model.set({
                entityId: this.actionData.entityId,
                entityName: this.actionData.entityName,
            });
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            if (this.actionData.entityId) {
                this.createView('entity', 'views/fields/link', {
                    el: this.getSelector() + ' .field[data-name="entity"]',
                    foreignScope: this.foreignEntityType,
                    name: 'entity',
                    model: this.model,
                    mode: 'detail',
                    readOnly: true,
                }, view => {
                    view.render();
                });
            }
        },
    });
});
