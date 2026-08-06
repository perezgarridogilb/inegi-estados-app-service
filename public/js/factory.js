var env = $('meta[name="env"]').attr('content');
var lang = $('html')[0].lang;
var target = document.querySelector("#kt_app_main");
var blockUI = new KTBlockUI(target, {
    message: '<div class="blockui-message"><span class="spinner-border text-primary"></span> Cargando...</div>',
});

ERPSystem = {

    getHeaders: () => {
        let headers = { 'headers': {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-Active-Warehouse' : $('meta[name="active-warehouse"]').attr('content'),
                                'X-CSRF-TOKEN': token
                            }
                        };
        return headers;

    },

    errInputs: (jsonResponse) => {

        if(jsonResponse.hasOwnProperty('message')){
            swal.fire({
                text: jsonResponse.message,
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok",
                customClass: {
                    confirmButton: "btn font-weight-bold btn-light-danger"
                }
            });
        }

        let errors = jsonResponse.errors;

        if (env != 'production')
            console.log({errors});

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        $.each( errors, function( key, value ) {
            $ele = $(':input[name="' + key + '"]');
            $ele.addClass('is-invalid');
            $ele.parent().append('<div class="invalid-feedback">' + value[0] + '</div>');
        });
    },

    getPetition: (path, params, type = 'POST', async = true) => {
        return getPetition = new Promise((resolve, reject) => {

            let token = $('meta[name="csrf-token"]').attr('content');
            let active_warehouse = $('meta[name="active-warehouse"]').attr('content');
            let _token  = { '_token': token, 'api' : true, 'active_warehouse' : active_warehouse };
            $.extend( true, params, _token );

            if (env != 'production')
                console.log("WEBSERVICE [" + type + "]: \n" + path + "\nTOKEN [" + token + "] \n" + JSON.stringify(params, null, 2));

            $.ajax({
                type: type,
                async: async,
                url: path,
                dataType: 'json',
                data: params,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Active-Warehouse' : $('meta[name="active-warehouse"]').attr('content'),
                    'X-CSRF-TOKEN': token
                },
                success:(data) => {
                    resolve(data);
                },
                error: (err) => {
                    setTimeout(blockUI.release(), 500);

                    if(err.status == 422) {
                        if(err.message)
                            toastr.error(err.message);
                        ERPSystem.errInputs(err.responseJSON);
                    } else if(err.status == 404) {
                        toastr.error('No se encuentra el ID');
                    } else {
                        reject(err);
                    }
                },
                beforeSend: function(){
                    blockUI.block();
                },
                complete: function(){
                    // console.log('getPetition '+ path +' => completed');
                    blockUI.release();
               }
            });
        });
    },

    getPetitionFile: (path, params, type = 'POST') => {
        return getPetition = new Promise((resolve, reject) => {

            let token = $('meta[name="csrf-token"]').attr('content');
            let active_warehouse = $('meta[name="active-warehouse"]').attr('content');
            params.append('_token', token);
            params.append('api' , true);
            params.append('active_warehouse' , active_warehouse);

            if (env != 'production')
                console.log("WEBSERVICE [" + type + "]: \n" + path + "\nTOKEN [" + token + "] \n" + JSON.stringify(params, null, 2));

            $.ajaxSetup({
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Active-Warehouse' : $('meta[name="active-warehouse"]').attr('content'),
                    'X-CSRF-TOKEN': token
                }
            });

            $.ajax({
                type: type,
                url: path,
                enctype: 'multipart/form-data',
                data: params,
                processData: false,
                contentType: false,
                success:(data) => {
                    $('.loadsubmit').removeClass('kt-spinner kt-spinner--right kt-spinner--md kt-spinner--light').attr('disabled', false);
                    resolve(data);
                },
                error: (err) => {
                    $('.loadsubmit').removeClass('kt-spinner kt-spinner--right kt-spinner--md kt-spinner--light').attr('disabled', false);
                    setTimeout(blockUI.release(), 1001);

                    if(err.status == 422) {
                        if(err.message)
                            toastr.error(err.message);
                        ERPSystem.errInputs(err.responseJSON);
                    } else if(err.status == 404) {
                        toastr.error('No se encuentra el ID');
                    } else {
                        reject(err);
                    }
                },
                beforeSend: function(){
                    blockUI.block();
                },
                complete: function(){
                    blockUI.release();
               }
            });
        });
    },

    deletePetition: (model, id) => {
        return deletePetition = new Promise((resolve, reject) => {

            let url   = $('meta[name="api"]').attr('content'); //|| 'http://127.0.0.1:8000';
            let token = $('meta[name="csrf-token"]').attr('content');
            let path  = url + '/' + model +'/' + id;
            let type  = 'DELETE';

            swal.fire({
                title: '¿Estas seguro que desea eliminar el registro?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: 'Si, eliminarlo',
                cancelButtonText: 'No, cancelar',
                reverseButtons: true
            }).then(function(result){
                if(result.value){

                    if (env != 'production')
                        console.log("WEBSERVICE [" + type + "]: \n" + path + "\nTOKEN [" + token + "]");

                    $.ajaxSetup({
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Active-Warehouse' : $('meta[name="active-warehouse"]').attr('content'),
                            'X-CSRF-TOKEN': token
                        }
                      });

                    $.ajax({
                        type: type,
                        url: path,
                        dataType: 'json',
                        success:(data) => {
                            if(data.money_entry)
                                $('#money_entry').html(formatCurrency(data.money_entry));
                            resolve(data);
                        },
                        error: (err) => {
                            setTimeout(blockUI.release(), 1001);
                            if(err.status == 422) {
                                if(err.message)
                                    toastr.error(err.message);
                                ERPSystem.errInputs(err.responseJSON);
                            } else {
                                reject(err);
                            }
                        },
                        beforeSend: function(){
                            blockUI.block();
                        },
                        complete: function(){
                            setTimeout(blockUI.release(), 1001);
                       }
                    });
                }
            });
       });
    },

    /** Para utilizar esta función, el name y el id del input deben ser iguales **/
    ajaxForm: ($form, params = false, path = false, type = false) => {

        return ajaxForm = new Promise((resolve, reject) => {

            let token = $('meta[name="csrf-token"]').attr('content');
            if (!token)
                token = $('input[name="_token"]').val();

            let active_warehouse   = $('meta[name="active-warehouse"]').attr('content');

            let _token = { '_token': token, 'web' : true, 'active_warehouse' : active_warehouse };

            if (!params)
                params = $form.serializeObject();

            $.extend( true, params, _token );

            if (!path)
                path = $form.attr('action');
            else
                path = $('meta[name="api"]').attr('content') + path;

            if (!type)
                type = $form.attr('method');

            if (env != 'production')
                console.log("WEBSERVICE [" + type + "]: \n" + path + "\nTOKEN [" + token + "] \n" + JSON.stringify(params, null, 2));

            $.ajaxSetup({
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Active-Warehouse' : $('meta[name="active-warehouse"]').attr('content'),
                    'X-CSRF-TOKEN': token
                }
            });

            $.ajax({
                type: type,
                url: path,
                dataType: 'json',
                data: params,
                success:(response) => {
                    if ($form) {
                        let elements = $form.find('.is-invalid');
                        $.each( elements, function( key, value ) {
                            if(value.id != ""){
                                $ele = $("#"+value.id);
                                $ele.removeClass('is-invalid');
                                $ele.parent().find('.invalid-feedback').remove();
                                $ele.addClass('is-valid');
                            }
                        });
                    }

                    resolve(response);
                },
                error: (response) => {

                    setTimeout(function () { blockUI.release(); }, 650);

                    if(response.status == 422 || response.status == 429) {
                        if(response.message)
                            toastr.error(response.message);

                        ERPSystem.errInputs(response.responseJSON);

                        if(!response.responseJSON.msg)
                            toastr.error('Favor de verificar el formulario');
                        else
                            toastr.error(response.responseJSON.msg);

                    } else if(response.status == 404) {

                        if(!response.responseJSON.msg)
                            toastr.error('No se encuentra el ID');
                        else
                            toastr.error(response.responseJSON.msg);

                    } else if(response.status == 419) {

                         toastr.error('Tu sesión ha terminado.');
                         setTimeout(function () { window.location.href = '/login'; }, 1001);

                    } else {
                        reject(response);
                    }
                },
                beforeSend: function(){
                    blockUI.block();
                    if ($form)
                        $form.find(':submit').addClass('spinner spinner-white spinner-right').attr('disabled', true);
                },
                complete: function(response){
                    if(response.responseJSON.hasOwnProperty('redirect') === false && response.responseJSON.hasOwnProperty('reload') === false)
                        setTimeout(function () { 
                            blockUI.release(); 

                            if ($form)
                                $form.find(':submit').removeClass('spinner spinner-white spinner-right').attr('disabled', false);
                        }, 1001);
               }
            });
       });
    },

    /** Notificaciones */
    checkNotifications: () => {
        ERPSystem.getPetition('/api/user/check-notifications', {}).then(notifications => {
            if (env != 'production')
                console.log({notifications});
            if(notifications.count > 0){
                $('.new-notifications').html(notifications + ' nuevas');
                $('.btn-notifications').addClass('pulse pulse-info');
            }
        }, err => {
            console.log('error:', err);
            toastr.error(err.msg);
        });
    },

    enableSubmitBtn(btn){
        btn.disabled = false;
        btn.removeAttribute('data-kt-indicator');
    },

    disableSubmitBtn(btn){
        btn.setAttribute('data-kt-indicator', 'on');
        btn.disabled = true;
    },

    serializeFormData: (formData) => {
        let obj = {};
        for (let [key, value] of formData) {
            if (obj[key] !== undefined) {
                if (!Array.isArray(obj[key])) {
                    obj[key] = [obj[key]];
                }
                obj[key].push(value);
            } else {
                obj[key] = value;
            }
        }
        return obj;
    }

};

toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toastr-top-center",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "400",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};


$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    o['web'] = true;

    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

function clearForm ($form)
{
    $form[0].reset();
    $("#dropdown").select2("destroy");
    $("#dropdown").select2();
}

function getStrAfterChar(str, char = '=')
{
    return str.split(char)[1];
}

jQuery(document).ready(function () {

if ($(".daterangepicker").length) $(".daterangepicker").daterangepicker();


    $('.automatic-submit').submit(function( event ) {
        event.preventDefault();

        ERPSystem.ajaxForm($(this)).then(response => {
            if (!response.error){

                toastr.success(response.message);

                if (env != 'production')
                    console.table(response);

                if(response.reload){
                    setTimeout(function () { location.reload(); }, 1001);
                } else if(response.redirect) {
                    window.location.href = response.redirect;
                }
                if(response.datatable){
                    // $(response.datatable).DataTable().ajax.reload();
                    setTimeout(function () { $(response.datatable).DataTable().ajax.reload(); }, 1001);
                }
                if(response.modal)
                {
                    $.each( $('#form-' + response.modal +' :input'), function( key, input ) {

                        $ele = $('#form-' + response.modal +' :input[name="' + input.name + '"]');

                        if($ele.length){
                            let t = $ele.attr('type');
                            if(t == 'text' || t == 'hidden' || t == 'number') {
                                $ele.val('');
                            }else if($ele[0].localName == "select"){
                                $('#' + $ele[0].id).val(null).trigger('change');
                            }else if($ele[0].localName == "textarea"){
                                $ele.html('');
                            }else if(t == 'radio') {
                                $ele.prop('checked', false);
                            }
                        }
                    });

                    $('#modal-' + response.modal).modal('hide');
                    clearForm($(this));

                }

                if(response._blank)
                    window.open(response._blank, '_blank');
            }else{
                toastr.error(response.message);
            }
        }).catch((e) => {
            console.log('Error', e);
        });
    });

    $('body').on('click', '.btn-get', function ()
    {
        let $btn = $(this);
        let path = $btn.data('url');
        let title = ($btn.data('bs-title') !== undefined) ? $btn.data('bs-title') : '¿Desea procesar este registro?';

        if(!path)
            toastr.error('No se ha seleccioando una ruta');

        swal.fire({
            title: title,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: "#2CA329",
            confirmButtonText: 'Si',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then(function(result){
            if(result.value){

                ERPSystem.getPetition(path, {}, 'GET').then(response => {

                    if(!response.error){

                        toastr.success(response.message);

                        if(response.remove_btn)
                            $btn.remove();

                        if(response.redirect)
                            window.location.href = response.redirect;
                        if(response.reload)
                            setTimeout(function () { location.reload(); }, 1001);
                        if(response._blank)
                            window.open(response._blank, '_blank');
                        if(response.reload_ktdatable)
                            $(response.reload_ktdatable).KTDatatable('reload');

                        if(response.blob) //Crear archivo
                        {
                            // download
                            let blob = new Blob([response.data], {
                                type: response.blob
                            });
                            let link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = response.filename;

                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }

                    }else{
                        toastr.error(message);
                    }
                }, err => {
                    console.log('error:', err);
                    toastr.error(err.msg);
                });
            }
        });
    });

    $('body').on('click', '.btn-edit', function (){
        let $btn  = $(this);
        let path  = $btn.data('url');
        if(!path)
            return toastr.error('No se ha seleccioando una ruta');

        ERPSystem.getPetition(path, {}, 'GET').then(response => {

            if(!response.error){

                toastr.success(response.message);

                if(response.modal){
                    $('#'+response.record).val(response.data.id);
                    $.each( response.data, function( key, value ) {
                        $ele = $('#form-' + response.modal +' :input[name="' + key + '"]');
                        if($ele.length){
                            let t = $ele.attr('type');
                            if(t == 'text' || t == 'hidden' || t == 'number' || t == 'color' || t == 'date') {
                                $ele.val(value);
                            }else if($ele[0].localName == "select"){
                                $('#' + $ele[0].id).val(value).trigger('change');
                            }else if($ele[0].localName == "textarea"){
                                $ele.html(value);
                            }else if(t == 'checkbox'){
                                if(value){
                                    $ele.prop('checked', true);
                                }else{
                                    $ele.prop('checked', false);
                                }
                            }else if(t == 'radio') {
                                $ele.filter('[value="' + value + '"]').prop('checked', true);
                            }
                        }
                    });

                    $('#modal-' + response.modal).modal('show');
                }

            }else{
                toastr.error(message);
            }
        }, err => {
            console.log('error:', err);
            toastr.error(err.msg);
        });
    });

    $('body').on('click', '.btn-del', function (){

        let model = $(this).parent().data('model');
        let id = $(this).parent().data('id');
        let code = $(this).parent().data('code');
        let url = ($(this).data('url')) ? $(this).data('url') : false;

        if(id && !url){
            url  = window.location.origin + '/api' + '/'+ model + '/' + id;
        }else if(code && !url){
            url  = window.location.origin + '/api' + '/'+ model + '/' + code;
        }

        if(!url)
            return toastr.error('No se ha seleccioando una ruta');

        swal.fire({
            title: '¿Desea eliminar este registro?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: "#CC0D0D",
            confirmButtonText: 'Si',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then(function(result){
            if(result.value){

                ERPSystem.getPetition(url, {'id': id, 'model' : model}, 'DELETE').then(response => {

                    if(!response.error){

                        toastr.success(response.message);

                        if(response.redirect)
                            window.location.href = response.redirect;
                        if(response.reload)
                            setTimeout(function () { location.reload(); }, 1001);
                        if(response._blank)
                            window.open(response._blank, '_blank');
                        if(response.reload_ktdatable)
                            $(response.reload_ktdatable).KTDatatable('reload');
                        if(response.modal)
                            $('#modal-' + response.modal).modal('hide');
                        if(response.remove)
                            $('#'+response.remove).remove();
                        if(response.datatable)
                            setTimeout(function () { $(response.datatable).DataTable().ajax.reload(); }, 1001);
                    }else{
                        toastr.error(response.message);
                    }
                }, err => {
                    if(err.status == 404) {
                        if(!err.responseJSON.msg)
                            toastr.error('No se encuentra disponible en este momento.');
                        else
                            toastr.error(err.responseJSON.msg);
                    } else if(err.status == 403) {
                        toastr.error('No cuenta con los permisos para eliminar este.');
                    } else{
                        toastr.error(err.msg);
                    }
                });
            }
        });
    });

    $('body').on('click', '.btn-delete', function (){

        let path = $(this).data('url');

        if(!path || path == '//')
            toastr.error('No se ha seleccionado una ruta');

        swal.fire({
            title: '¿Desea eliminar este registro?',
            type: 'danger',
            showCancelButton: true,
            confirmButtonColor: "#CC0D0D",
            confirmButtonText: 'Si',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then(function(result){
            if(result.value){

                ERPSystem.getPetition(path, {}, 'DELETE').then(response => {

                    if(!response.error){

                        toastr.success(response.message);

                        if(response.redirect)
                            window.location.href = response.redirect;
                        if(response.reload)
                            setTimeout(function () { location.reload(); }, 1001);
                        if(response._blank)
                            window.open(response._blank, '_blank');
                        if(response.reload_ktdatable)
                            $(response.reload_ktdatable).KTDatatable('reload');
                        if(response.modal)
                            $('#modal-' + response.modal).modal('hide');
                        if(response.remove)
                            $('#'+response.remove).remove();
                    }else{
                        toastr.error(response.message);
                    }
                }, err => {
                    if(err.status == 404) {

                        if(!err.responseJSON.msg)
                            toastr.error('No se encuentra disponible en este momento.');
                        else
                            toastr.error(err.responseJSON.msg);
                    }else{
                        toastr.error(err.msg);
                    }
                });
            }
        });
    });

    $('body').on('click', '#btn-datatable_export', function (){

        let url = $(this).data('url');

        if (url == '')
            return toastr.error('Falta la url para obtener el archivo.');

        let data = {
            'search' : $('#kt_datatable_search_query').val(),
            'client_id' : $('#kt_datatable_search_client').val(),
            'supplier_id' : $('#kt_datatable_search_supplier').val(),
            'status_id' : $('#kt_datatable_search_status').val(),
            'project_id' : $('#kt_datatable_search_project').val(),
            'priority_id' : $('#kt_datatable_search_priority').val(),
            'warehouses' : $('#kt_datatable_search_warehouses').val(),
        };

        let token   = $('meta[name="csrf-token"]').attr('content');

        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Active-Warehouse' : $('meta[name="active-warehouse"]').attr('content'),
                'X-CSRF-TOKEN': token
            }
        });

        $.ajax({
            xhrFields: {
                responseType: 'blob',
            },
            type: 'POST',
            url: url,
            data: data,
            success: function(result, status, xhr) {

                let dt = new Date();
                let currentime = dt.getFullYear() + (dt.getMonth()+1) + dt.getDate() + dt.getHours() + dt.getMinutes() + dt.getSeconds();

                let disposition = xhr.getResponseHeader('content-disposition');
                let docName = getStrAfterChar(disposition);
                let validFileName = /^[^*&%\s]+\.(xls|xlsx)$/;
                let filename = (docName.match(validFileName) ? docName : currentime + '.xlsx');

                // download
                let blob = new Blob([result], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                document.body.appendChild(link);

                link.click();
                document.body.removeChild(link);
            },
            error: (response) => {
                toastr.error('Ha ocurrido un error al crear el reporte.');
                setTimeout(blockBtn.release(), 1001);
            },
            beforeSend: function(){
                blockBtn.block();
            },
            complete: function(){
                setTimeout(blockBtn.release(), 1001);
           }
        });
    });

    $('body').on('click', '.btn-pdf', function (){

        let url = $(this).data('url');
        let name = $(this).data('name');

        if (url == '')
            return toastr.error('Falta la url para obtener el archivo.');

        let token   = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/pdf',
                'X-Active-Warehouse' : $('meta[name="active-warehouse"]').attr('content'),
                'X-CSRF-TOKEN': token
            },
            xhrFields: {
                responseType: 'blob',
            },
            url: url,
            success: function(result, status, xhr) {
                console.log(result);
                console.log(status);
                console.log(xhr);

                let dt = new Date();
                let currentime = dt.getFullYear().toString() + (dt.getMonth()+1).toString() + dt.getDate().toString() + dt.getHours().toString() + dt.getMinutes().toString() + dt.getSeconds().toString();
                let filename = name + '_' + currentime + '.pdf';

                // download
                let blob = new Blob([result], {
                    type: 'application/pdf'
                });

                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                document.body.appendChild(link);

                link.click();
                document.body.removeChild(link);
            },
            error: (response) => {
                console.log({response});
                toastr.error('Ha ocurrido un error al crear el archivo PDF.');
                setTimeout(blockUI.release(), 1001);
            },
            beforeSend: function(){
                blockUI.block();
            },
            complete: function(){
                blockUI.release();
            }
        });
    });

    $('body').on('click', '.btn-close_session', function (){
        event.preventDefault(); document.getElementById('logout-form').submit();
    });

    $('body').on('click', '.btn-notifications', function (){
        ERPSystem.getPetition('/api/user/get-notifications', {}).then(response => {
            if (env != 'production')
                console.log({response});

            let alerts = '';
            $.each( response.notifications, function( key, notification ) {
                if (env != 'production')
                    console.log('notification', data.greeting);

                let data = notification.data;
                alerts += `<a href="${data.url}" class="navi-item btn btn-link text-left btn-block read-notification" data-id="${notification.id}">
                    <div class="navi-link " >
                        <div class="navi-icon mr-2">
                            <i class="flaticon2-line-chart text-success"></i>
                        </div>
                        <div class="navi-text">
                            <div class="font-weight-bold">
                                ${data.greeting}
                            </div>
                            <div class="text-muted">
                                ${notification.updated_at}
                            </div>
                        </div>
                    </div>
                </a>`;
            });
            $('#notifications-list').html(alerts);
        }, err => {
            console.log('error:', err);
            toastr.error(err.msg);
        });
    });

    // Phone
    if($('.phone-mask').length)
        Inputmask({
            'mask' : '(999) 999-9999'
        }).mask('.phone-mask');
});
