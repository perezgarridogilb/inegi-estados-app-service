document.addEventListener('DOMContentLoaded', function () {
    $('#states-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: $('#states-table').data('url'),
            type: 'GET',
            cache: true,
            dataSrc: 'data'
        },
        columns: [
            { data: 'cvegeo', name: 'cvegeo' },
            { data: 'cve_ent', name: 'cve_ent' },
            { data: 'nomgeo', name: 'nomgeo' },
            { data: 'nom_abrev', name: 'nom_abrev' },
            { data: 'pob_total', name: 'pob_total' }
        ],
        info: { position: 'bottomCenter' },
        language: {
            url: '/js/scripts/datatables/lang/es.json'
        }
    });
});

/* Listener */

const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

function showToast(message, type = 'success') {
    const $toast = $('#app-toast');
    $toast.removeClass('text-bg-success text-bg-danger text-bg-info');
    $toast.addClass('text-bg-' + type);
    $('#app-toast-body').text(message);
    bootstrap.Toast.getOrCreateInstance($toast[0]).show();
}
const mensajesSincronizacion = [
    'Sincronizando estados...',
    'Consultando API de INEGI...',
    'Procesando registros...'
];
async function ejecutarAccion($spinner, $btn) {
    // Bloquear
    $spinner.removeClass('d-none');
    $btn.prop('disabled', true);
    let index = 0;
    showToast(mensajesSincronizacion[index], 'info');

    // 2. Alternar texto cada 1000ms (1 segundo)
    const intervalId = setInterval(() => {
        index = (index + 1) % mensajesSincronizacion.length;

        showToast(mensajesSincronizacion[index], 'info');
    }, 1000);

    const url = document.getElementById('sync_save').value;
    console.log(url);

    ERPSystem.getPetition(url, { states: null }, 'POST').then(response => {
        clearInterval(intervalId);
        // Desbloquear
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        let data = response.data.datos;
        showToast('Sincronización completada', 'success');

        // confirmar o cancelar
        const modal = new bootstrap.Modal('#modal-confirm-insert');
        modal.show();
    }).catch(error => {
        clearInterval(intervalId);
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        showToast('Error al sincronizar', 'danger');
    });
}

$('#btn-confirm-insert').on('click', function () {
    $('#modal-confirm-insert').data('confirmed', true);
    const modal = bootstrap.Modal.getInstance('#modal-confirm-insert');
    modal.hide();

    // insertar
    $('#states-table').DataTable().ajax.reload();
    showToast('Estados insertados correctamente', 'success');
});

$('#modal-confirm-insert').on('hidden.bs.modal', function () {
    if (!$(this).data('confirmed')) {
        showToast('Inserción cancelada', 'info');
    }
    $(this).data('confirmed', false);
});

$('#btn-sync-states').on('click', function () {
    const $btn = $(this);
    const $spinner = $btn.find('.spinner-border'); // O el selector que use tu spinner dentro/junto al botón

    ejecutarAccion($spinner, $btn);
});


// $.ajax({
//     url: '/api/states/sync',
//     type: 'POST',
//     data: {
//         _token: '{{ csrf_token() }}'
//     }
// }).done(function () {
//     $('#states-table').DataTable().ajax.reload();
// }).fail(function () {
//     alert('Error al sincronizar los estados');
// }).always(function () {
//     $spinner.addClass('d-none');
//     $btn.prop('disabled', false);
// });

