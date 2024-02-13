
define('advanced:views/bpmn-process/fields/variables', ['views/fields/text'], function (Dep) {

    return Dep.extend({

        detailTemplateContent: `
            {{#if isSet}}
            <div class="complex-text">
                <pre><code>{{value}}</code></pre>
            </div>            
            {{else}}
            <span class="loading-value">...</span>
            {{/if}}
        `,

        data: function () {
            if (!this.model.has(this.name)) {
                return {};
            }

            let value = this.model.get(this.name);

            let stringValue = JSON.stringify(value, null, 4);

            return {
                isSet: true,
                value: stringValue,
            };
        },
    });
});
