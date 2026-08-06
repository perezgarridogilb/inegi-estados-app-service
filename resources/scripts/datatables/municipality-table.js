document.addEventListener('DOMContentLoaded', function () {
    
    $('#municipality-table').DataTable({
        processing: true,
        serverSide: false,
        searching: false,
        ajax: {
            url: $('#municipality-table').data('url'),
            type: 'GET',
            cache: true,
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                name: 'id',
                title: 'ID',
                render: function (data, type, row) {
                    return '<a href="' + row.show_url + '" class="text-hover-primary">' + row.id + '</a>';
                }
            },
            { data: 'cvegeo', name: 'cvegeo', title: 'Clave geo' },
            { data: 'cve_ent', name: 'cve_ent', title: 'Clave entidad' },
            { data: 'nomgeo', name: 'nomgeo', title: 'Nombre' },
            { data: 'nom_abrev', name: 'nom_abrev', title: 'Abreviatura' },
            { data: 'pob_total', name: 'pob_total', title: 'Población total', render: (data) => formatNumber(data) }
        ],
        info: { position: 'bottomCenter' },
        language: {
            url: '/js/scripts/datatables/lang/es.json'
        }
    });
});

/* Listener */

const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

function formatNumber(value) {
    if (value === null || value === undefined || value === '') return '';
    return Number(value).toLocaleString('es-MX');
}

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

    const url = document.getElementById('sync').value;

    ERPSystem.getPetition(url, { states: null }, 'POST').then(response => {
        clearInterval(intervalId);
        // Desbloquear
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        window.states = response.data.datos;
        showToast('Consulta completada', 'success');

        // confirmar o cancelar
        const modal = new bootstrap.Modal('#modal-confirm-insert');
        modal.show();
        renderPreviewTable(window.states);

    }).catch(error => {
        clearInterval(intervalId);
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        showToast('Error al sincronizar', 'danger');
    });
}

function renderPreviewTable(response) {
    let data = response.datos;
    const $table = $('#preview_response_data');
    if (!data || !data.length) {
        $table.html('<tr><td>Sin datos</td></tr>');
        return;
    }

    const columns = Object.keys(data[0]);
    let html = '<thead><tr>';
    columns.forEach(col => html += `<th>${col}</th>`);
    html += '</tr></thead><tbody>';

    data.forEach(row => {
        html += '<tr>';
        columns.forEach(col => html += `<td>${row[col]}</td>`);
        html += '</tr>';
    });

    html += '</tbody>';
    $table.html(html);
}

$('#btn-confirm-insert').on('click', function () {
    $('#modal-confirm-insert').data('confirmed', true);
    const modal = bootstrap.Modal.getInstance('#modal-confirm-insert');
    modal.hide();
    const url = document.getElementById('sync_save').value;
    console.log(url);

    ERPSystem.getPetition(url, { states: window.states }, 'POST').then(response => {
        // Desbloquear
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        showToast('Consulta completada', 'success');

        // confirmar o cancelar
        const modal = new bootstrap.Modal('#modal-confirm-insert');
        modal.show();
        renderPreviewTable(window.states);

    }).catch(error => {
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        showToast('Error al sincronizar', 'danger');
    });

    $('#municipality-table').DataTable().ajax.reload();
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
//     $('#municipality-table').DataTable().ajax.reload();
// }).fail(function () {
//     alert('Error al sincronizar los estados');
// }).always(function () {
//     $spinner.addClass('d-none');
//     $btn.prop('disabled', false);
// });

