$(document).ready(function () {
    
    $('#states-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/states/data',
            type: 'GET',
            dataSrc: ''
        },
        columns: [
            { data: 'id' },
            { data: 'nomgeo' },
            { data: 'cve_ent' }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        }
    });
});
