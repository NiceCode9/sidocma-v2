<script>
    function showDocumentPermissions(documentId) {
        $('#documentPermissionModal').modal('show');

        $.get(`{{ url('/documents/get-permissions') }}`, {
                document_id: documentId,
            })
            .done((response) => {
                if (response.success) {
                    console.log(response)
                    const documentPermissionTable = $('#documentPermissionTable');
                    const tbody = documentPermissionTable.find('tbody');
                    const documentIdField = $('#documentId');
                    documentIdField.val(documentId);

                    tbody.empty(); // Clear existing data

                    response.data.forEach((permission, index) => {
                        const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${permission.user ? permission.user.name : 'N/A'}</td>
                                    <td>${permission.unit ? permission.unit.name : 'N/A'}</td>
                                    <td>${permission.role ? permission.role.name : 'N/A'}</td>
                                    <td>${permission.permission_type}</td>
                                </tr>
                            `;
                        tbody.append(row);
                    });
                }
            });
    }

    function setDocumentPermission() {
        const units = $('#folderUnit').val(); // Array of unit IDs
        const roles = $('#folderRole').val(); // Array of role IDs
        const users = $('#folderUser').val(); // Array of user IDs
        const permissionTypes = $('#folderPermissionTypes').val();
        const documentId = $('#documentId').val();

        const data = {
            document_id: documentId,
            user_id: users,
            role_id: roles,
            unit_id: units,
            permission_types: permissionTypes,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        console.log(data);

        $.post("{{ route('documents.set-permissions') }}", data)
            .done(function(response) {
                console.log(response);
                if (response.success) {
                    showAlert('success', 'Izin document berhasil disetel');
                    $('#documentPermissionModal').modal('hide');
                } else {
                    // Handle warning/error dari response
                    if (response.status === 'warning' && response.existing_permissions) {
                        showExistingDocumentPermissionsModal(response);
                    } else {
                        showAlert('error', response.message || 'Gagal menyetel izin folder');
                    }
                }
            })
            .fail(function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);

                let errorMessage = 'Gagal menyetel izin folder';

                try {
                    const response = JSON.parse(xhr.responseText);

                    if (response.status === 'warning' && response.existing_permissions) {
                        // Handle warning case dari fail handler (status 409)
                        showExistingDocumentPermissionsModal(response);
                        return;
                    } else if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // Jika response bukan JSON, gunakan status text atau error message
                    errorMessage = xhr.statusText || error || 'Terjadi kesalahan sistem';
                }

                showAlert('error', errorMessage);
            });
    }

    // Function untuk menampilkan modal existing permissions
    function showExistingDocumentPermissionsModal(response) {
        let existingList = '';

        response.existing_permissions.forEach(function(perm) {
            existingList +=
                `<li><strong>${perm.name}</strong> (${perm.type}) - Permission: ${perm.permission_type}</li>`;
        });

        const modalHtml = `
                <div class="modal fade" id="existingPermissionsModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Permission Sudah Ada
                                </h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Permission berikut sudah terdaftar untuk folder ini:</strong></p>
                                <ul class="list-unstyled">
                                    ${existingList}
                                </ul>
                                <p class="text-muted mt-3">
                                    <small>Apakah Anda ingin melanjutkan dengan menimpa permission yang sudah ada, atau batalkan operasi ini?</small>
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Batal
                                </button>
                                <button type="button" class="btn btn-warning" onclick="forceSetDocumentPermission()">
                                    Timpa Permission
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

        // Remove existing modal if any
        $('#existingDocumentPermissionsModal').remove();

        // Add modal to body and show
        $('body').append(modalHtml);
        $('#existingDocumentPermissionsModal').modal('show');
    }

    // Function untuk force set permission (menimpa yang sudah ada)
    function forceSetDocumentPermission() {
        // Tambahkan parameter force ke data
        const forceData = {
            ...data,
            force: true
        };

        $.post("{{ route('documents.set-permissions') }}", forceData)
            .done(function(response) {
                if (response.success) {
                    showAlert('success', 'Izin document berhasil disetel');
                    $('#existingDocumentPermissionsModal').modal('hide');
                    $('#documentPermissionModal').modal('hide');
                } else {
                    showAlert('error', response.message || 'Gagal menyetel izin document');
                }
            })
            .fail(function(xhr) {
                let errorMessage = 'Gagal menyetel izin document';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    errorMessage = xhr.statusText || 'Terjadi kesalahan sistem';
                }
                showAlert('error', errorMessage);
            });
    }
</script>
