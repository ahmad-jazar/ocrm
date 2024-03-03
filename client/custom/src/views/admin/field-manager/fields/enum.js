define('custom:views/admin/field-manager/fields/enum', 'views/fields/enum', function (Dep) {
    return Dep.extend({
        setupOptions: function () {
            this.params.options = [''];
            this.translatedOptions = {};

            const fields = this.getMetadata().get(['entityDefs', this.options.scope, 'fields']);
            for (const field in fields) {
                const defs = fields[field] || {};
                if (['int', 'float', 'currency'].includes(defs.type)) {
                    this.params.options.push(field);
                    this.translatedOptions[field] = this.translate(field, 'fields', this.options.scope);
                }
            }
        }
    });
});
