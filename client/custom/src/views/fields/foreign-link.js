define('custom:views/fields/foreign-link', ['views/fields/enum'], function (Dep) {
    return Dep.extend({
        setupOptions: function () {
            const fields = this.getMetadata().get(['entityDefs', 'Contact', 'fields']) || {};
            this.translatedOptions = {};

            this.params.options = Object.keys(fields)
                .filter((field) => {
                    return fields[field].type === 'link';
                });

            this.params.options.unshift('');

            this.params.options.forEach((field) => {
                this.translatedOptions[field] = this.getLanguage().translate(field, 'fields', 'Contact');
            })
        }
    });
});
