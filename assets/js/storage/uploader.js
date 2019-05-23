import "blueimp-file-upload";

$(function () {
    // Activate file lookup
    $(document).on('click', '[data-toggle="upload"]', function (e) {
        e.preventDefault();

        if (!('File' in window &&
            'FileReader' in window &&
            'FileList' in window &&
            'Blob' in window)
        ) {
            alert('Ваш браузер не поддерживает загрузку файлов.');
        }

        $('input:file')
            .attr('data-upload-url', $(this).attr('href'))
            .attr('data-target', $(this).data('target'))
            .attr('data-pjax-url', $(this).data('url'))
            .trigger('click');
    });

    // File upload
    $(document).on('click', 'input:file', function () {
        let self = $(this),
            modal = $('#upload-modal'),
            bar = modal.find('[data-file]'),
            index = 0,
            m = {
                addError: function (message) {
                    let error = $('<li>').text(message);
                    modal.find('.upload-errors').append(error).show();
                }
            };

        self.fileupload({
            dataType: 'json',
            maxChunkSize: 1000000,
            sequentialUploads: true,
            autoUpload: false,
            add: function (e, data) {
                modal.modal('show');
                modal.find('[data-file-total]').text(data.originalFiles.length);
                data.submit();
            },
            start: function () {
                modal.find('.modal-footer button').prop('disabled', true);
                modal.find('.upload-errors > li').remove();
                modal.find('.upload-errors').hide();
            },
            stop: function () {
                modal.find('.modal-footer button').prop('disabled', false);
                modal.find('.modal-title > .d-none').toggleClass('d-none');
            },
            send: function (e, data) {
                bar.data('file', data.files[0].name);

                if (index <= data.originalFiles.length) {
                    modal.find('[data-file-index]').data('file-index', index).text(index + 1);
                }

                index++;
            },
            fail: function (e, data) {
                m.addError(data.files[0].name + ': ' + (data.errorThrown || 'ошибка загрузки файла'))
            },
            done: function (e, data) {
                // Files after uploading (contains real file size & mime type)
                let file = data.result.files[0];
                // Real uploaded file name (as selected from client file system)
                let name = data.files[0].name;

                $.ajax({
                    url: self.data('hash-url'),
                    type: 'get',
                    data: {'name': name},
                })
                    .then(function (r) {
                        return $.ajax({
                            url: self.data('upload-url'),
                            type: 'put',
                            dataType: 'json',
                            headers: {'X-CSRF-Token': modal.find('[name="_csrf_token"]').val()},
                            data: {
                                'name': name,
                                'size': file.size,
                                'type': file.type,
                                'hash': r.hash
                            }
                        });
                    })
                    .fail(function(r) {
                        m.addError('Ошибка загрузки файлов: ' + r.statusText);
                    })
                    .done(function (r) {
                        if (r.hasOwnProperty('file_uuid')) {
                            $.ajax({
                                url: self.data('move-url'),
                                type: 'post',
                                data: {'name': name, 'uuid': r['file_uuid']}
                            });
                        }

                        if (r.hasOwnProperty('errors')) {
                            let errors = r['errors'];
                            if ((errors.length > 0 || !$.isEmptyObject(errors))) {
                                for (let k in errors) {
                                    if (errors.hasOwnProperty(k)) {
                                        for (let i = 0; i < errors[k].length; i++) {
                                            m.addError(errors[k][i]);
                                        }
                                    }
                                }
                            }
                        }

                        if (index === data.originalFiles.length) {
                            $.ajax({
                                type: 'GET',
                                url: self.data('pjax-url'),
                                success: function (response) {
                                    $(self.data('target')).html(response);
                                    modal.find('.modal-body').toggleClass('d-none');
                                }
                            });
                        }
                    });
            },
            progressall: function (e, data) {
                let progress = parseInt(data.loaded / data.total * 100, 10);

                bar.find('.progress-bar').text(progress + '%').css({'width': progress + '%'});
            }
        });
    });
});