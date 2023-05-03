Form = {
    header: '',
    init: function () {
        // trigger shib tab
        //find table login tab header then delete it.
        $('a[aria-controls="non-inst-login"]').parent().remove()

        // click on shib table tab
        $("#shib_login_tab0").trigger('click')

        // remove table login tab body
        $("#non-inst-login").removeClass('active').remove()

        // display shib tab body
        $("#inst-login0").addClass('active')

        // remove all tabs
        $('.nav-tabs').remove()

        if (Form.header != '') {
            Form.replaceContent()
        }
        var url = new URL(location.href);
        if (url.pathname.startsWith('/webauth') === false) {
            var new_url = '/webauth' + url.pathname + url.search
            // $("#login_url").attr('href', new_url);
            window.location = new_url
        }
    },
    replaceContent: function () {

        $("#inst-login0").nextAll('hr').after(Form.header)
        $("#inst-login0").nextAll('div.row').remove()
    }

}
window.onload = function () {
    Form.init();
}
