define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'jquery/ui',
    'prototype'
], function ($) {
    window.hasLoadedProfiles = false;
    window.defaultTemplateModal = {
        // Open modal
        open: function () {
            if (!window.hasLoadedProfiles) {
                $.ajaxSetup({
                    showLoader: true
                });
                $.ajax({
                    type: 'GET',
                    url: 'https://www.xtento.com/xtcustom/profileSettings/load',
                    data: 'source=Xtento_ProductExport&version=M2&demo=' + window.isDemoEnvironment,
                    dataType: 'jsonp',
                    success: function (responseData, textStatus, jqXHR) {
                        if (responseData.success === false) {
                            $('#profile_service_response').html(responseData.html);
                        } else {
                            // Got profiles
                            window.hasLoadedProfiles = true;
                            $('#profile_service_response').html(responseData.html);
                            $('#profile_table').show();
                            profiles = responseData.profiles;

                            // Populate profile selector
                            window.availableProfiles = responseData.profiles;

                            // First for sample templates
                            var optgroup = "<optgroup label='"+$.mage.__('Sample Templates')+"'>";
                            var templatesFound = false;
                            $.each(window.availableProfiles, function (id, profile) {
                                if (profile.type === 'sample') {
                                    templatesFound = true;
                                    optgroup += "<option value='" + id + "'>" + profile.name + "</option>";
                                }
                            });
                            optgroup += "</optgroup>";
                            if (templatesFound) {
                                $('#profile_name').append(optgroup);
                            }

                            // Then feed templates
                            optgroup = "<optgroup label='"+$.mage.__('Ready-To-Use Feeds')+"'>";
                            templatesFound = false;
                            $.each(window.availableProfiles, function (id, profile) {
                                if (profile.type === 'feed') {
                                    templatesFound = true;
                                    optgroup += "<option value='" + id + "'>" + profile.name + "</option>";
                                }
                            });
                            optgroup += "</optgroup>";
                            if (templatesFound) {
                                $('#profile_name').append(optgroup);
                            }
                        }
                    },
                    error: function (responseData, textStatus, errorThrown) {
                        console.warn(responseData, textStatus, errorThrown);
                        $('body').trigger('processStop');
                        alert($.mage.__('There was a problem talking to the XTENTO Profile Service. Please try again later or contact us if this issue persists.'));
                    }
                });
            }
            $('#load_default_template_window').modal('openModal');
        },
        // Close modal
        close: function () {
            $('#load_default_template_window').modal('closeModal');
        },
        // Load Profile button
        loadProfile: function () {
            // Get selected profile
            var profileId = $('#profile_name').val();
            if (profileId === '') {
                return;
            }

            // Loading indicator - start
            $('body').trigger('processStart');
            window.templateLoaded = false;
            window.destinationCreated = false;
            window.destinationsLoaded = false;
            var selectedProfile = window.availableProfiles[profileId];
            var feedUrl = $.mage.__('You did not select "Create feed folder"');

            // Load XSL Template
            $.ajax({
                type: 'GET',
                url: selectedProfile.xsl_template_download_url,
                data: 'source=Xtento_ProductExport&version=M2&demo=' + window.isDemoEnvironment,
                dataType: 'jsonp',
                success: function (responseData, textStatus, jqXHR) {
                    // Set XSL Template
                    window.editor.getSession().setValue(responseData);
                    window.templateLoaded = true;
                    // Extract filename from XSL Template
                    var xslFilename = responseData.match(/filename="(.*?)"/)[1];
                    // Create destination, if required
                    if ($('#create_folder').prop('checked')) {
                        $.ajax({
                            type: 'GET',
                            url: window.createFeedDestinationUrl,
                            data: 'filename=' + xslFilename,
                            dataType: 'json',
                            success: function (destinationResponse, textStatus2, jqXHR2) {
                                if (!destinationResponse.success) {
                                    alert($.mage.__('There was a problem creating the export folder: ') + destinationResponse.warning);
                                }
                                var destinationId = destinationResponse.destination_id;
                                feedUrl = destinationResponse.feed_url;
                                window.destinationCreated = true;
                                // Remove destination checkboxes first, then reload grid
                                try {
                                    if (typeof xtento_productexport_destination_gridJsObject !== 'undefined') {
                                        $('#xtento_productexport_destination_grid_table tbody input').prop('disabled', true);
                                        xtento_productexport_destination_gridJsObject.resetFilter();
                                    } else {
                                        $('#profile_tabs_destination').first().click();
                                    }
                                } catch (err) {}
                                // Wait for destinations to be loaded, then select it
                                var destinationInterval = setInterval(function () {
                                    var destinationCheckbox = $('input[name="col_destinations"][value="' + destinationId + '"]');
                                    if (typeof destinationCheckbox[0] !== 'undefined') {
                                        if (!destinationCheckbox.prop('checked')) {
                                            //destinationCheckbox.prop('checked', true);
                                            destinationCheckbox.trigger('click');
                                        }
                                        window.destinationsLoaded = true;
                                        clearInterval(destinationInterval);
                                    }
                                }, 500);
                            },
                            error: function (responseData2, textStatus2, errorThrown2) {
                                console.warn(responseData2, textStatus2, errorThrown2);
                                alert($.mage.__('There was a problem creating the export destination.'));
                                window.destinationCreated = true;
                                window.destinationsLoaded = true;
                            }
                        });
                    } else {
                        window.destinationCreated = true;
                        window.destinationsLoaded = true;
                    }
                },
                error: function (responseData, textStatus, errorThrown) {
                    console.warn(responseData, textStatus, errorThrown);
                    $('body').trigger('processStop');
                    alert($.mage.__('There was a problem downloading the profile template. Please try again later or contact us if this issue persists.'));
                }
            });

            // Make profile settings
            $.each(selectedProfile.profile_settings, function (field, value) {
                $('#' + field).val(value);
            });

            // Wait for all tasks to finish
            var checkInterval = setInterval(function () {
                if (window.templateLoaded && window.destinationCreated && window.destinationsLoaded) {
                    // Close modal, show instructions
                    $('#messages .messages').first().html('<div class="message message-success success"><div>' + selectedProfile.profile_instructions.replace(/%FEED_URL%/, feedUrl) + '</div></div>');
                    this.close();
                    $('#profile_tabs_general').click();
                    $('body').trigger('processStop');
                    clearInterval(checkInterval);
                }
            }.bind(this), 250);
        }
    };

    $(document).ready(function () {
        $('#load_default_template_window').modal({
            title: '',
            type: 'slide',
            buttons: []
        });
        $('#load_default_template_window').show();

        if ($('#entity').val() == 'product' && $('#xsl_template').val() == '' && $('#output_type') != 'xml') {
            // New profile, open onboarding popup
            window.defaultTemplateModal.open();
        }
    });
});