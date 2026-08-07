document.addEventListener('DOMContentLoaded', function () {
    $('#municipality-table').DataTable({
        processing: true,
        serverSide: false,
        searching: false,
        searching: true, 
        dom: '<"d-flex justify-content-start mb-3"f>rtip',
        search: {
            search: ''
        },
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
                title: 'ID'
            },
            { data: 'cve_ent', name: 'cve_ent', title: 'Clave entidad' },
            { data: 'nomgeo', name: 'nomgeo', title: 'Nombre' },
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
    'Sincronizando municipios...',
    'Consultando API de INEGI...',
    'Procesando registros...'
];
async function ejecutarAccion($spinner, $btn) {
    // Bloquear
    $spinner.removeClass('d-none');
    $btn.prop('disabled', true);
    let index = 0;
    showToast(mensajesSincronizacion[index], 'info');

    // by 1000ms (1 seg)
    const intervalId = setInterval(() => {
        index = (index + 1) % mensajesSincronizacion.length;

        showToast(mensajesSincronizacion[index], 'info');
    }, 1000);

    const url = document.getElementById('sync').value;

    ERPSystem.getPetition(url, { states: null }, 'POST').then(response => {
        clearInterval(intervalId);
        // disb
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        window.municipalities = response.data.datos;
        showToast('Consulta completada', 'success');

        // confirm or cancel
        const modal = new bootstrap.Modal('#modal-confirm-insert');
        modal.show();
        renderPreviewTable(window.municipalities);

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

    data.forEach((row, index) => {
        const hiddenClass = index >= 7 ? 'd-none extra-row' : '';
        
        html += `<tr class="${hiddenClass}">`;
        columns.forEach(col => html += `<td>${row[col] ?? ''}</td>`);
        html += '</tr>';
    });

    if (data.length > 7) {
        const remaining = data.length - 7;
        html += `
            <tr id="row-toggle-more">
                <td colspan="${columns.length}" class="text-center py-2">
                    <button type="button" class="btn btn-sm btn-link text-primary fw-bold" id="btn-show-more">
                        Ver más (${remaining} restantes)
                    </button>
                </td>
            </tr>
        `;
    }

    html += '</tbody>';
    $table.html(html);
}

$(document).on('click', '#btn-show-more', function () {
    const $btn = $(this);
    const $extraRows = $('#preview_response_data .extra-row');

    if ($extraRows.hasClass('d-none')) {
        $extraRows.removeClass('d-none');
        $btn.text('Ver menos');
    } else {
        $extraRows.addClass('d-none');
        $btn.text(`Ver más (${$extraRows.length} restantes)`);
    }
});

$('#btn-confirm-insert').on('click', function () {
    $('#modal-confirm-insert').data('confirmed', true);
    const modal = bootstrap.Modal.getInstance('#modal-confirm-insert');
    modal.hide();
    const url = document.getElementById('sync_save').value;
    console.log(url);

    ERPSystem.getPetition(url, { municipalities: JSON.stringify(window.municipalities.datos) }, 'POST').then(response => {
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        showToast('Consulta completada', 'success');

        // confirmar o cancelar
        const modal = new bootstrap.Modal('#modal-confirm-insert');
        modal.show();
        renderPreviewTable(window.municipalities);

    }).catch(error => {
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
        showToast('Error al sincronizar', 'danger');
    });

    $('#municipality-table').DataTable().ajax.reload();
    showToast('Municipios insertados correctamente', 'success');
});

$('#modal-confirm-insert').on('hidden.bs.modal', function () {
    if (!$(this).data('confirmed')) {
        showToast('Inserción cancelada', 'info');
    }
    $(this).data('confirmed', false);
});

$('#btn-sync-municipalities').on('click', function () {
    const $btn = $(this);
    const $spinner = $btn.find('.spinner-border'); // O el selector que use tu spinner dentro/junto al botón

    ejecutarAccion($spinner, $btn);
});

$(document).on('click', '#btn-clear-states', function (e) {
    e.preventDefault();

    Swal.fire({
        title: '¿Limpiar registros asociados a este municipio?',
        text: 'Se eliminarán los registros asociados a este estado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = document.getElementById('delete').value;

            ERPSystem.getPetition(url, {}, 'POST').then(response => {
                // disb
                // $btn.prop('disabled', false);
                // $spinner.addClass('d-none');
                // $('#municipality-table').DataTable().ajax.reload();

                showToast('Completado', 'success');
                location.reload();
            }).catch(error => {
                // $spinner.addClass('d-none');
                showToast('Error', 'danger');
            });
        }
    });
});

