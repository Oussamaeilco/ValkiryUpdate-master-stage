(function() {
    $('a[data-action=license-abort]').click(function() {
        var id = $(this).attr('data-target');
        var tab = $(this).attr('data-tab');
        var modal = $('#modalLicenseAbort');

        modal.find('[data-fill=target]').text(id);
        modal.find('[data-value=target]').val(id);
        modal.find('[data-value=tab]').val(tab);

        modal.modal();
    });
})();