/* global jQuery, _ZoomCustomizerReset, ajaxurl, wp */

jQuery(function ($) {
    var __ = wp.i18n.__;
    var sprintf = wp.i18n.sprintf;

    var $container = $('#customize-header-actions');

    // Create reset tools button (matches WordPress Publish button style)
    var $gearButton = $('<button type="button" class="button button-primary zoom-reset-settings">')
        .attr({
            'aria-label': __('Customizer Reset Tools', 'customizer-reset-by-wpzoom'),
            'title': __('Reset, Backup, Import & Export', 'customizer-reset-by-wpzoom')
        })
        .html('<span class="dashicons dashicons-admin-generic"></span> ' + __('Reset', 'customizer-reset-by-wpzoom'));

    // Append button to header
    $container.append($gearButton);

    // When gear is clicked, open the Reset Settings section
    $gearButton.on('click', function(e) {
        e.preventDefault();
        if (wp.customize.section('zoom_reset_section')) {
            wp.customize.section('zoom_reset_section').expand();
        }
    });

    // Hidden file input for import
    var $fileInput = $('<input type="file" id="zoom-reset-import-file" accept=".json,.dat" style="display:none;">');
    $('body').append($fileInput);

    // Handle action buttons in the section
    $(document).on('click', '.zoom-reset-section-content button[data-action]', function(e) {
        e.preventDefault();
        var action = $(this).data('action');

        switch(action) {
            case 'export':
                handleExport();
                break;
            case 'import':
                handleImportClick();
                break;
            case 'backup-reset':
                handleBackupAndReset();
                break;
            case 'reset':
                handleReset();
                break;
            case 'cleanup-inactive':
                handleCleanupInactive();
                break;
        }
    });

    // Handle restore backup buttons
    $(document).on('click', '.zoom-restore-backup', function(e) {
        e.preventDefault();
        var backupKey = $(this).data('backup-key');
        handleRestore(backupKey);
    });

    // Handle delete backup buttons
    $(document).on('click', '.zoom-delete-backup', function(e) {
        e.preventDefault();
        var backupKey = $(this).data('backup-key');
        handleDeleteBackup(backupKey);
    });

    // Handle delete all backups button
    $(document).on('click', '.zoom-delete-all-backups', function(e) {
        e.preventDefault();
        handleDeleteAllBackups();
    });

    // Handle create backup button (without reset)
    $(document).on('click', '.zoom-create-backup', function(e) {
        e.preventDefault();
        handleCreateBackup();
    });

    // Handle file selection
    var fromDropzone = false;
    $fileInput.on('change', function() {
        if (this.files.length > 0) {
            var file = this.files[0];

            // If file was selected from dropzone, show confirmation
            if (fromDropzone) {
                // Validate file type
                if (!file.name.match(/\.(json|dat)$/i)) {
                    showNotification('error', __('Please select a JSON or DAT file', 'customizer-reset-by-wpzoom'));
                    fromDropzone = false;
                    return;
                }

                droppedFile = file;
                showDroppedFileConfirmation(file);
                fromDropzone = false;
            } else {
                // Import button click - import directly
                handleImportFile(file);
            }
        }
    });

    // Drag and drop functionality - use event delegation since dropzone is dynamically loaded
    var droppedFile = null;

    // Handle dropzone click - trigger file input
    $(document).on('click', '.zoom-import-dropzone', function(e) {
        e.preventDefault();
        // Don't trigger file input if clicking on the confirm or cancel buttons
        if ($(e.target).hasClass('zoom-confirm-import') || $(e.target).parent().hasClass('zoom-confirm-import') ||
            $(e.target).hasClass('zoom-cancel-import') || $(e.target).parent().hasClass('zoom-cancel-import')) {
            return;
        }
        fromDropzone = true;
        $fileInput.val('').trigger('click');
    });

    // Handle drag enter/over
    $(document).on('dragover dragenter', '.zoom-import-dropzone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });

    // Handle drag leave
    $(document).on('dragleave', '.zoom-import-dropzone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });

    // Handle file drop
    $(document).on('drop', '.zoom-import-dropzone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');

        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            var file = files[0];

            // Validate file type
            if (!file.name.match(/\.(json|dat)$/i)) {
                showNotification('error', __('Please select a JSON or DAT file', 'customizer-reset-by-wpzoom'));
                return;
            }

            // Store the file and show confirmation
            droppedFile = file;
            showDroppedFileConfirmation(file);
        }
    });

    // Show confirmation UI after file is dropped
    function showDroppedFileConfirmation(file) {
        var $dropzone = $('.zoom-import-dropzone');

        // Update dropzone content to show file info and confirm button
        $dropzone.html(
            '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>' +
            '<p><strong>' + __('File ready to import:', 'customizer-reset-by-wpzoom') + '</strong></p>' +
            '<p class="description" style="margin-bottom: 12px;">' + $('<div>').text(file.name).html() + '</p>' +
            '<button type="button" class="button button-primary zoom-confirm-import">' +
            '<span class="dashicons dashicons-upload"></span> ' + __('Import this file', 'customizer-reset-by-wpzoom') +
            '</button>' +
            '<button type="button" class="button zoom-cancel-import">' +
            __('Cancel', 'customizer-reset-by-wpzoom') +
            '</button>'
        );
    }

    // Handle confirm import button click
    $(document).on('click', '.zoom-confirm-import', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (droppedFile) {
            handleImportFile(droppedFile);
            droppedFile = null;
            resetDropzone();
        }
    });

    // Handle cancel import button click
    $(document).on('click', '.zoom-cancel-import', function(e) {
        e.preventDefault();
        e.stopPropagation();
        droppedFile = null;
        resetDropzone();
    });

    // Reset dropzone to original state
    function resetDropzone() {
        var $dropzone = $('.zoom-import-dropzone');
        $dropzone.html(
            '<span class="dashicons dashicons-upload"></span>' +
            '<p>' + __('Or drag and drop a file here', 'customizer-reset-by-wpzoom') + '</p>' +
            '<span class="description">' + __('.json or .dat file', 'customizer-reset-by-wpzoom') + '</span>'
        );
    }

    // Export functionality
    function handleExport() {
        // Get selected format
        var selectedFormat = $('input[name="zoom-export-format"]:checked').val() || 'json';

        showNotification('info', __('Exporting settings...', 'customizer-reset-by-wpzoom'));

        $.post(ajaxurl, {
            wp_customize: 'on',
            action: 'customizer_export',
            nonce: _ZoomCustomizerReset.nonce.export,
            format: selectedFormat
        }, function (response) {
            if (response.success) {
                var fileExtension = selectedFormat === 'dat' ? 'dat' : 'json';
                var mimeType = selectedFormat === 'dat' ? 'application/octet-stream' : 'application/json';
                var dataStr, dataBlob;

                if (selectedFormat === 'dat') {
                    // DAT format comes pre-serialized from PHP
                    dataBlob = new Blob([response.data], {type: mimeType});
                } else {
                    // JSON format needs to be stringified
                    dataStr = JSON.stringify(response.data, null, 2);
                    dataBlob = new Blob([dataStr], {type: mimeType});
                }

                var url = URL.createObjectURL(dataBlob);
                var link = document.createElement('a');
                link.href = url;
                link.download = 'customizer-settings-' + (response.data.stylesheet || get_stylesheet()) + '-' + Date.now() + '.' + fileExtension;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);

                showNotification('success', __('Settings exported successfully!', 'customizer-reset-by-wpzoom'), 3000);
            } else {
                showNotification('error', sprintf(
                    /* translators: %s: error message returned by the server. */
                    __('Export failed: %s', 'customizer-reset-by-wpzoom'),
                    response.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                ));
            }
        }).fail(function() {
            showNotification('error', sprintf(
                /* translators: %s: error message returned by the server. */
                __('Export failed: %s', 'customizer-reset-by-wpzoom'),
                __('Network error', 'customizer-reset-by-wpzoom')
            ));
        });
    }

    // Helper to get stylesheet name from WordPress
    function get_stylesheet() {
        if (wp.customize && wp.customize.settings && wp.customize.settings.theme) {
            return wp.customize.settings.theme.stylesheet;
        }
        return 'theme';
    }

    // Import click - trigger file input
    function handleImportClick() {
        $fileInput.val('').trigger('click');
    }

    // Import file
    function handleImportFile(file) {
        // Accept both .json and .dat files
        if (!file.name.match(/\.(json|dat)$/i)) {
            showNotification('error', __('Please select a JSON or DAT file', 'customizer-reset-by-wpzoom'));
            return;
        }

        // Detect format from file extension
        var fileFormat = file.name.match(/\.dat$/i) ? 'dat' : 'json';

        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                var fileContent = e.target.result;
                var data;

                // Try to parse as JSON first (works for both formats since PHP will handle DAT)
                try {
                    data = JSON.parse(fileContent);
                } catch (jsonErr) {
                    // If JSON parsing fails and it's a DAT file, send raw content
                    if (fileFormat === 'dat') {
                        data = {
                            raw_import: fileContent,
                            is_dat: true
                        };
                    } else {
                        throw jsonErr;
                    }
                }

                // Validate data structure (skip for raw DAT)
                if (!data.is_dat && (!data.mods || !data.stylesheet)) {
                    showNotification('error', __('Invalid file format', 'customizer-reset-by-wpzoom'));
                    return;
                }

                var confirmMsg = sprintf(
                    /* translators: 1: theme name the settings were exported from, 2: date the file was created. */
                    __('Import settings from "%1$s"?\n\nThis will replace your current customizer settings.\nCreated: %2$s', 'customizer-reset-by-wpzoom'),
                    data.theme || data.template || __('Unknown', 'customizer-reset-by-wpzoom'),
                    data.exported || data.created || __('Unknown', 'customizer-reset-by-wpzoom')
                );

                if (!confirm(confirmMsg)) {
                    return;
                }

                showNotification('info', __('Importing settings...', 'customizer-reset-by-wpzoom'));

                // Get checkbox state for image import
                var importImages = $('#zoom-import-images-checkbox').is(':checked');

                $.post(ajaxurl, {
                    wp_customize: 'on',
                    action: 'customizer_import',
                    nonce: _ZoomCustomizerReset.nonce.import,
                    import_data: data.is_dat ? fileContent : JSON.stringify(data),
                    format: fileFormat,
                    import_images: importImages ? '1' : '0'
                }, function(response) {
                    if (response.success) {
                        wp.customize.state('saved').set(true);
                        showNotification('success', __('Settings imported! Reloading customizer...', 'customizer-reset-by-wpzoom'));

                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification('error', sprintf(
                            /* translators: %s: error message returned by the server. */
                            __('Import failed: %s', 'customizer-reset-by-wpzoom'),
                            response.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                        ));
                    }
                }).fail(function() {
                    showNotification('error', sprintf(
                        /* translators: %s: error message returned by the server. */
                        __('Import failed: %s', 'customizer-reset-by-wpzoom'),
                        __('Network error', 'customizer-reset-by-wpzoom')
                    ));
                });

            } catch (err) {
                showNotification('error', sprintf(
                    /* translators: %s: error message describing why the file could not be read. */
                    __('Invalid file: %s', 'customizer-reset-by-wpzoom'),
                    err.message
                ));
            }
        };
        reader.readAsText(file);
    }

    // Backup & Reset functionality
    function handleBackupAndReset() {
        if (!confirm(_ZoomCustomizerReset.confirm)) {
            return;
        }

        showNotification('info', __('Creating backup...', 'customizer-reset-by-wpzoom'));

        // Get checkbox state for CSS reset
        var resetCss = $('#zoom-reset-css-checkbox').is(':checked');

        // First create backup
        $.post(ajaxurl, {
            wp_customize: 'on',
            action: 'customizer_backup',
            nonce: _ZoomCustomizerReset.nonce.backup
        }, function (backupResponse) {
            if (backupResponse.success) {
                showNotification('info', __('Resetting...', 'customizer-reset-by-wpzoom'));

                // Then perform reset
                $.post(ajaxurl, {
                    wp_customize: 'on',
                    action: 'customizer_reset',
                    nonce: _ZoomCustomizerReset.nonce.reset,
                    reset_css: resetCss ? '1' : '0'
                }, function (resetResponse) {
                    if (resetResponse.success) {
                        wp.customize.state('saved').set(true);
                        showNotification('success', __('Backup created! Resetting customizer...', 'customizer-reset-by-wpzoom'));

                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification('error', sprintf(
                            /* translators: %s: error message returned by the server. */
                            __('Reset failed: %s', 'customizer-reset-by-wpzoom'),
                            resetResponse.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                        ));
                    }
                });
            } else {
                showNotification('error', sprintf(
                    /* translators: %s: error message returned by the server. */
                    __('Backup failed: %s', 'customizer-reset-by-wpzoom'),
                    backupResponse.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                ));
            }
        }).fail(function() {
            showNotification('error', sprintf(
                /* translators: %s: error message returned by the server. */
                __('Backup failed: %s', 'customizer-reset-by-wpzoom'),
                __('Network error', 'customizer-reset-by-wpzoom')
            ));
        });
    }

    // Reset without backup functionality
    function handleReset() {
        var warningMessage = _ZoomCustomizerReset.confirm;
        if (!_ZoomCustomizerReset.hasBackup) {
            warningMessage += '\n\n' + __('WARNING: No backup exists. Consider using "Backup & Reset" instead.', 'customizer-reset-by-wpzoom');
        }

        if (!confirm(warningMessage)) {
            return;
        }

        showNotification('info', __('Resetting...', 'customizer-reset-by-wpzoom'));

        // Get checkbox state for CSS reset
        var resetCss = $('#zoom-reset-css-checkbox').is(':checked');

        $.post(ajaxurl, {
            wp_customize: 'on',
            action: 'customizer_reset',
            nonce: _ZoomCustomizerReset.nonce.reset,
            reset_css: resetCss ? '1' : '0'
        }, function (response) {
            if (response.success) {
                wp.customize.state('saved').set(true);
                showNotification('success', __('Customizer reset successfully!', 'customizer-reset-by-wpzoom'));

                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                showNotification('error', sprintf(
                    /* translators: %s: error message returned by the server. */
                    __('Reset failed: %s', 'customizer-reset-by-wpzoom'),
                    response.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                ));
            }
        }).fail(function() {
            showNotification('error', sprintf(
                /* translators: %s: error message returned by the server. */
                __('Reset failed: %s', 'customizer-reset-by-wpzoom'),
                __('Network error', 'customizer-reset-by-wpzoom')
            ));
        });
    }

    // Create backup only (without reset)
    function handleCreateBackup() {
        showNotification('info', __('Creating backup...', 'customizer-reset-by-wpzoom'));

        $.post(ajaxurl, {
            wp_customize: 'on',
            action: 'customizer_backup',
            nonce: _ZoomCustomizerReset.nonce.backup
        }, function (response) {
            if (response.success) {
                showNotification('success', __('Backup created successfully! Reloading...', 'customizer-reset-by-wpzoom'), 2000);

                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                showNotification('error', sprintf(
                    /* translators: %s: error message returned by the server. */
                    __('Backup failed: %s', 'customizer-reset-by-wpzoom'),
                    response.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                ));
            }
        }).fail(function() {
            showNotification('error', sprintf(
                /* translators: %s: error message returned by the server. */
                __('Backup failed: %s', 'customizer-reset-by-wpzoom'),
                __('Network error', 'customizer-reset-by-wpzoom')
            ));
        });
    }

    // Restore backup functionality
    function handleRestore(backupKey) {
        if (!confirm(__('Restore this backup?\n\nThis will replace your current customizer settings with the backed up version.', 'customizer-reset-by-wpzoom'))) {
            return;
        }

        showNotification('info', __('Restoring...', 'customizer-reset-by-wpzoom'));

        $.post(ajaxurl, {
            wp_customize: 'on',
            action: 'customizer_restore_backup',
            nonce: _ZoomCustomizerReset.nonce.restore,
            backup_key: backupKey
        }, function(response) {
            if (response.success) {
                wp.customize.state('saved').set(true);
                showNotification('success', __('Backup restored successfully!', 'customizer-reset-by-wpzoom'));

                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                showNotification('error', sprintf(
                    /* translators: %s: error message returned by the server. */
                    __('Restore failed: %s', 'customizer-reset-by-wpzoom'),
                    response.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                ));
            }
        }).fail(function() {
            showNotification('error', sprintf(
                /* translators: %s: error message returned by the server. */
                __('Restore failed: %s', 'customizer-reset-by-wpzoom'),
                __('Network error', 'customizer-reset-by-wpzoom')
            ));
        });
    }

    // Delete single backup functionality
    function handleDeleteBackup(backupKey) {
        if (!confirm(__('Delete this backup?\n\nThis action cannot be undone.', 'customizer-reset-by-wpzoom'))) {
            return;
        }

        var deletingNotificationId = 'zoom-deleting-' + Date.now();

        // Show deleting notification
        if (wp.customize && wp.customize.notifications) {
            wp.customize.notifications.add(new wp.customize.Notification(deletingNotificationId, {
                type: 'info',
                message: __('Deleting backup...', 'customizer-reset-by-wpzoom')
            }));
        }

        $.post(ajaxurl, {
            wp_customize: 'on',
            action: 'customizer_delete_backup',
            nonce: _ZoomCustomizerReset.nonce.delete,
            backup_key: backupKey
        }, function(response) {
            // Remove the deleting notification
            if (wp.customize && wp.customize.notifications) {
                wp.customize.notifications.remove(deletingNotificationId);
            }

            if (response.success) {
                showNotification('success', __('Backup deleted successfully!', 'customizer-reset-by-wpzoom'), 3000);

                // Remove the backup item from UI
                $('.zoom-backup-item[data-backup-key="' + backupKey + '"]').fadeOut(300, function() {
                    $(this).remove();

                    // Update backup count
                    var remainingBackups = $('.zoom-backup-item').length;
                    $('.zoom-backup-count').text('(' + remainingBackups + ')');

                    // Show "no backups" message if all are deleted
                    if (remainingBackups === 0) {
                        $('.zoom-backup-list').remove();
                        $('.zoom-backup-actions').remove();
                        $('.zoom-backup-history').append(
                            $('<p class="description">').text(__('No backups found. Use "Backup & Reset" to create a backup before resetting.', 'customizer-reset-by-wpzoom'))
                        );
                    }
                });
            } else {
                showNotification('error', sprintf(
                    /* translators: %s: error message returned by the server. */
                    __('Delete failed: %s', 'customizer-reset-by-wpzoom'),
                    response.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                ));
            }
        }).fail(function() {
            // Remove the deleting notification on error too
            if (wp.customize && wp.customize.notifications) {
                wp.customize.notifications.remove(deletingNotificationId);
            }
            showNotification('error', sprintf(
                /* translators: %s: error message returned by the server. */
                __('Delete failed: %s', 'customizer-reset-by-wpzoom'),
                __('Network error', 'customizer-reset-by-wpzoom')
            ));
        });
    }

    // Delete all backups functionality
    function handleDeleteAllBackups() {
        if (!confirm(__('Delete ALL backups?\n\nThis will permanently delete all your backup history. This action cannot be undone.', 'customizer-reset-by-wpzoom'))) {
            return;
        }

        var deletingNotificationId = 'zoom-deleting-all-' + Date.now();

        // Show deleting notification
        if (wp.customize && wp.customize.notifications) {
            wp.customize.notifications.add(new wp.customize.Notification(deletingNotificationId, {
                type: 'info',
                message: __('Deleting all backups...', 'customizer-reset-by-wpzoom')
            }));
        }

        $.post(ajaxurl, {
            wp_customize: 'on',
            action: 'customizer_delete_all_backups',
            nonce: _ZoomCustomizerReset.nonce.deleteAll
        }, function(response) {
            // Remove the deleting notification
            if (wp.customize && wp.customize.notifications) {
                wp.customize.notifications.remove(deletingNotificationId);
            }

            if (response.success) {
                showNotification('success', response.data.message || __('All backups deleted successfully!', 'customizer-reset-by-wpzoom'), 3000);

                // Remove all backup items from UI
                $('.zoom-backup-list').fadeOut(300, function() {
                    $(this).remove();
                    $('.zoom-backup-actions').remove();
                    $('.zoom-backup-count').text('(0)');
                    $('.zoom-backup-history').append(
                        $('<p class="description">').text(__('No backups found. Use "Backup & Reset" to create a backup before resetting.', 'customizer-reset-by-wpzoom'))
                    );
                });
            } else {
                showNotification('error', sprintf(
                    /* translators: %s: error message returned by the server. */
                    __('Delete failed: %s', 'customizer-reset-by-wpzoom'),
                    response.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                ));
            }
        }).fail(function() {
            // Remove the deleting notification on error too
            if (wp.customize && wp.customize.notifications) {
                wp.customize.notifications.remove(deletingNotificationId);
            }
            showNotification('error', sprintf(
                /* translators: %s: error message returned by the server. */
                __('Delete failed: %s', 'customizer-reset-by-wpzoom'),
                __('Network error', 'customizer-reset-by-wpzoom')
            ));
        });
    }

    // Clean up inactive theme mods
    function handleCleanupInactive() {
        if (!confirm(__('Remove all old settings from other themes?\n\nThis will delete customizer settings stored for previously active themes, including shared WPZOOM theme options. This prevents old colors, fonts, and other settings from reappearing when switching themes.', 'customizer-reset-by-wpzoom'))) {
            return;
        }

        showNotification('info', __('Cleaning up inactive theme mods...', 'customizer-reset-by-wpzoom'));

        $.post(ajaxurl, {
            wp_customize: 'on',
            action: 'customizer_cleanup_inactive',
            nonce: _ZoomCustomizerReset.nonce.cleanupInactive
        }, function (response) {
            if (response.success) {
                showNotification('success', response.data.message || __('Inactive theme mods removed!', 'customizer-reset-by-wpzoom'));

                // Remove the inactive mods section from UI
                $('.zoom-inactive-mods').fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                showNotification('error', sprintf(
                    /* translators: %s: error message returned by the server. */
                    __('Cleanup failed: %s', 'customizer-reset-by-wpzoom'),
                    response.data || __('Unknown error', 'customizer-reset-by-wpzoom')
                ));
            }
        }).fail(function() {
            showNotification('error', sprintf(
                /* translators: %s: error message returned by the server. */
                __('Cleanup failed: %s', 'customizer-reset-by-wpzoom'),
                __('Network error', 'customizer-reset-by-wpzoom')
            ));
        });
    }

    // Helper function to show notifications
    function showNotification(type, message, duration) {
        if (wp.customize && wp.customize.notifications) {
            var notificationId = 'zoom-customizer-reset-' + Date.now();
            wp.customize.notifications.add(new wp.customize.Notification(notificationId, {
                type: type,
                message: message
            }));

            if (duration) {
                setTimeout(function() {
                    wp.customize.notifications.remove(notificationId);
                }, duration);
            }
        } else {
            // Fallback to alert if notifications API not available
            alert(message);
        }
    }
});
