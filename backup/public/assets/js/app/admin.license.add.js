(function() {
    var checkbox = $('#checkboxCustomLicense');
    var input = $('#inputLicense');

    checkbox.change(function () {
        if (this.checked) {
            input.removeAttr('disabled');
            input.removeClass('disabled');
        } else {
            input.attr('disabled', 'true');
            input.addClass('disabled');
        }
    });
})();