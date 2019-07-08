(function() {
    $('a[data-action=periode-edit]').click(function() {
        var id = $(this).attr('data-id');
        var promotion = $(this).attr('data-promotion');
        var startdate = $(this).attr('data-startdate');
        var enddate = $(this).attr('data-enddate');
        var startdateR = $(this).attr('data-startdateR');
        var enddateR = $(this).attr('data-enddateR');
        var modal = $('#modalPeriodeEdit');

        modal.find('[data-value=id]').val(id);
        modal.find('[data-value=startdate]').val(startdate);
        modal.find('[data-value=enddate]').val(enddate);
        modal.find('[data-value=startdateR]').val(startdateR);
        modal.find('[data-value=enddateR]').val(enddateR);
        modal.find('[data-value=promotion]').val(promotion);
        
        modal.modal();
    });
})();