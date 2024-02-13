
define('advanced:views/workflow/actions/create-entity', ['advanced:views/workflow/actions/base', 'model'],
function (Dep, Model) {

    return Dep.extend({

        type: 'createEntity',

        defaultActionData: {
            link: false,
            fieldList: [],
            fields: {},
        },

        data: function () {
            var data = Dep.prototype.data.call(this);

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

            this.displayedLinkedEntityName = this.translate(this.actionData.link, 'scopeNames');
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            if (this.type === 'createEntity') {
                var model = new Model;

                model.set('linkList', this.actionData.linkList);

                var translatedOptions = {};

                (this.actionData.linkList || []).forEach(link => {
                    translatedOptions[link] = this.getLanguage().translate(link, 'links', this.actionData.link);
                });

                this.createView('linkList', 'views/fields/multi-enum', {
                    model: model,
                    el: this.getSelector() + ' .field[data-name="linkList"]',
                    mode: 'detail',
                    defs: {
                        name: 'linkList'
                    },
                    inlineEditDisabled: true,
                    translatedOptions: translatedOptions,
                }, view => {
                    view.render();
                });
            }
        },
    });
});

