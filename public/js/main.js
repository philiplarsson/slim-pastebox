// Hide notification when pressing delete button
$(document).ready(function () {
    $('button.delete').click(function () {
        $(this).closest('.notification').hide();
    });
});
