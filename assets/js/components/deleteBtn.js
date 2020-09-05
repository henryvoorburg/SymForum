import $ from 'jquery';

document.querySelectorAll('.js-delete-button').forEach((btn) => btn.addEventListener('click', () => {
    if (!confirm('Etes-vous certain de vouloir faire cela ?')) return;

    let alert, type, message;
    let url = btn.dataset.deleteUrl;
    let $row = $(this).closest('tr');

    $.ajax({
        type: 'DELETE',
        url: url
    }).done((response) => {
        type = 'success';
        message = response.message;
        $row.fadeOut('normal', () => {
            $(this).closest('tr').remove();
        });
    }).fail((error) => {
        type = 'danger';
        message = error.responseJSON.message;
    }).always(() => {
        alert = '<div class="alert alert-dismissible alert-' + type + '"><button type="button" class="close" data-dismiss="alert">&times;</button>' + message + '</div>';
        $('.messages').append(alert).hide().fadeIn('normal');
    });
}));
