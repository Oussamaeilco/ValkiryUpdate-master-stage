(function() {
    $('a[data-action=license-edit]').click(function() {
        var id = $(this).attr('data-id');
        var email = $(this).attr('data-email');
        var license = $(this).attr('data-license');
        var startdate = $(this).attr('data-startdate');
        var enddate = $(this).attr('data-enddate');
        var tab = $(this).attr('data-tab');
        var modal = $('#modalLicenseEdit');

        modal.find('[data-value=id]').val(id);
        modal.find('[data-value=email]').val(email);
        modal.find('[data-value=license]').val(license);
        modal.find('[data-value=startdate]').val(startdate);
        modal.find('[data-value=enddate]').val(enddate);
        modal.find('[data-value=tab]').val(tab);

        modal.modal();
    });
})();