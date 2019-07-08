(function() {
    $('a[data-action=periode-abort]').click(function() {
        var id = $(this).attr('data-target');
        var modal = $('#modalPeriodeAbort');

        modal.find('[data-fill=target]').text(id);
        modal.find('[data-value=target]').val(id);
       
        modal.modal();
    });
})();