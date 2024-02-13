
define('advanced:views/workflow/action-modals/create-related-entity',
['advanced:views/workflow/action-modals/create-entity', 'model'],
function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow/action-modals/create-related-entity',

        permittedLinkTypes: ['belongsTo', 'hasMany', 'hasChildren'],

        getLinkOptionsHtml: function () {
            var value = this.actionData.link;

            var list = Object.keys(this.getMetadata().get('entityDefs.' + this.entityType + '.links') || [])
                .sort((v1, v2) => {
                     return this.translate(v1, 'links', this.scope)
                         .localeCompare(this.translate(v2, 'links', this.scope));
                });

            var html = '<option value="">--' + this.translate('Select') + '--</option>';

            list.forEach(item => {
                var defs = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + item);

                if (defs.disabled) {
                    return;
                }

                if (~this.permittedLinkTypes.indexOf(defs.type)) {
                    var label = this.translate(item, 'links' , this.entityType);

                    label = this.getHelper().escapeString(label);

                    html += '<option value="' + item + '" ' + (item === value ? 'selected' : '') + '>' +
                        label + '</option>';
                }
            });

            return html;
        },

        setupScope: function (callback) {
            if (this.actionData.link) {
                var scope = this.getMetadata()
                    .get('entityDefs.' + this.entityType + '.links.' + this.actionData.link + '.entity');

                this.scope = scope;

                if (scope) {
                    this.wait(true);

                    this.getModelFactory().create(scope, model => {
                        this.model = model;

                        (this.actionData.fieldList || []).forEach(field => {
                            var attributes = (this.actionData.fields[field] || {}).attributes || {};

                            model.set(attributes, {silent: true});
                        });

                        callback();
                    });
                } else {
                    throw new Error;
                }
            } else {
                this.model = null;

                callback();
            }
        },

        setupFormulaView: function () {
            var model = new Model;

            if (this.hasFormulaAvailable) {
                model.set('formula', this.actionData.formula || null);

                this.createView('formula', 'views/fields/formula', {
                    name: 'formula',
                    model: model,
                    mode: this.readOnly ? 'detail' : 'edit',
                    height: 100,
                    el: this.getSelector() + ' .field[data-name="formula"]',
                    inlineEditDisabled: true,
                    targetEntityType: this.scope,
                }, view => {
                    view.render();
                });
            }
        },
    });
});
