
define('advanced:views/workflow/actions/send-request',
['advanced:views/workflow/actions/base', 'model'], function (Dep, Model) {

    return Dep.extend({

        type: 'sendRequest',

        template: 'advanced:workflow/actions/send-request',

        defaultActionData: {
            requestType: 'POST',
            contentType: null,
            content: '{}',
            requestUrl: null,
            headers: null,
            contentVariable: null,
        },

        setModel: function () {
            this.model.set({
                requestType: this.actionData.requestType || null,
                contentType: this.actionData.contentType || null,
                content: this.actionData.content || null,
                requestUrl: this.actionData.requestUrl || null,
                headers: this.actionData.headers || null,
                contentVariable: this.actionData.contentVariable || null,
            });
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            let model = this.model = new Model();
            model.name = 'Workflow';

            this.setModel();

            this.on('change', () => {
                this.setModel();
            });

            this.createView('requestType', 'views/fields/enum', {
                mode: 'detail',
                model: model,
                el: this.options.el + ' .field[data-name="requestType"]',
                defs: {
                    name: 'requestType',
                    params: {
                        options: [
                            'POST',
                            'PUT',
                            'PATCH',
                            'DELETE',
                            'GET',
                        ],
                    }
                },
                readOnly: true,
            });

            this.createView('contentType', 'views/fields/enum', {
                mode: 'detail',
                model: model,
                el: this.options.el + ' .field[data-name="contentType"]',
                defs: {
                    name: 'contentType',
                    params: {
                        options: [
                            'application/json',
                            'application/x-www-form-urlencoded',
                        ],
                    }
                },
                readOnly: true,
            });

            this.createView('requestUrl', 'views/fields/varchar', {
                mode: 'detail',
                model: model,
                el: this.options.el + ' .field[data-name="requestUrl"]',
                defs: {
                    name: 'requestUrl',
                    params: {
                        required: true,
                    }
                },
                readOnly: true,
            });

            this.createView('content', 'views/fields/formula', {
                mode: 'detail',
                model: model,
                el: this.options.el + ' .field[data-name="content"]',
                defs: {
                    name: 'content',
                },
                insertDisabled: true,
                height: 30,
                readOnly: true,
            });

            this.createView('headers', 'views/fields/array', {
                mode: 'detail',
                model: model,
                el: this.options.el + ' .field[data-name="headers"]',
                defs: {
                    name: 'headers',
                    params: {
                        displayAsList: true,
                    },
                },
                readOnly: true,
            });

            this.createView('contentVariable', 'views/fields/varchar', {
                mode: 'detail',
                model: model,
                el: this.options.el + ' .field[data-name="contentVariable"]',
                defs: {
                    name: 'contentVariable',
                    params: {},
                },
                readOnly: true,
            });
        },
    });
});
