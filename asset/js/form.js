Form = {
    validationURL: '',
    errors: [],
    header: '',
    button: '<div class="bd-clipboard float-right"><button class="btn-clipboard btn btn-primary" data-clipboard-target="#link" title="" data-original-title="Copy to clipboard">Copy</button></div>',
    init: function () {
        Form.buildAttemptContainer()
    },
    buildAttemptContainer: function () {
        var link = '<pre id="link">' + Form.validationURL + '</pre>'
        console.log(link)
        $("#left_col").html(Form.header + '<div class="alert alert-secondary">' + Form.button + link + '</div>')
        Form.initClipboardJS()
    },
    initClipboardJS: function () {
        new ClipboardJS('.btn')
    }
}
window.onload = function () {
    Form.init();
}
