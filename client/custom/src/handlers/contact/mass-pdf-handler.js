define('custom:handlers/contact/mass-pdf-handler', [], function () {

    const PdfHandler = function (view) {
        this.view = view;
    };

    _.extend(PdfHandler.prototype, {

        init: function () {
            if (!this.view.getAcl().check('Template', 'read')) {
                this.view.removeMassAction('pdfEx');
            }
        },

        actionPdfEx: function (data) {
            const params = data.params;
            const ids = params.ids;
            let foreignLink = this.view.getMetadata().get(['clientDefs', this.view.entityType, 'foreignLink']) || '';
            foreignLink = foreignLink.charAt(0).toUpperCase() + foreignLink.slice(1);

            this.view.createView('pdfTemplate', 'views/modals/select-template', {
                entityType: foreignLink
            }, (view) => {
                view.render();

                this.view.listenToOnce(view, 'select', (model) => {
                    this.view.clearView('pdfTemplate');

                    if (!ids) {
                        console.error('No ids provided');
                        return;
                    }
                    window.open(
                        '?entryPoint=pdfEx&entityType=' +
                        this.view.entityType + '&ids=' +
                        ids + '&templateId=' + model.id + '&foreignLink=' + foreignLink, '_blank'
                    );
                });
            });
        },
    });

    return PdfHandler;
});