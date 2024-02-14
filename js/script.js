$.ajax({
    url: CFG_GLPI.root_doc + '/' + GLPI_PLUGINS_PATH.transferticketentity + '/ajax/getEntitiesRights.php',
    method: "GET",
    success: function (data) {
        data = JSON.parse(data);      

        // Check if the form is there
        if (document.querySelector('.tt_entity_choice') != null) {
            $('#entity_choice').select2();
            $('#group_choice').select2();

            document.querySelector('#tt_gest_error').style.display='none';
            document.querySelector('.form_transfert').style.display='block';
            document.querySelector('#nogroupfound').style.display = 'none';

            let entity_choice = document.querySelector('#entity_choice')
            let tt_group_choice = document.querySelector('.tt_group_choice')
            let tt_btn_open_modal_form = document.querySelector('#tt_btn_open_modal_form')
            
            const clone_all_groups = document.querySelectorAll('#group_choice option')
            let all_groups = []

            // Remove all groups not chosen
            let all_groups_unchoice = document.querySelectorAll('#group_choice option')
            all_groups_unchoice.forEach(function(all_group_unchoice) {
                all_group_unchoice.remove()
            })

            $('#entity_choice').on('change', function (event) {
                // if value is empty, hide groups
                if (entity_choice.value == '') {
                    tt_group_choice.style.display = 'none'
                    tt_btn_open_modal_form.disabled = true
                    tt_btn_open_modal_form.style.backgroundColor = '#D3D3D3'
                    tt_btn_open_modal_form.style.color = '#FFFFFF'
                    tt_btn_open_modal_form.style.cursor = 'not-allowed'
                } else {
                // if not, show them
                    tt_group_choice.style.display = 'flex'
                    document.querySelector('#div_confirmation').style.display = 'block'
                    tt_btn_open_modal_form.disabled = true
                    tt_btn_open_modal_form.style.backgroundColor = '#D3D3D3'
                    tt_btn_open_modal_form.style.color = '#FFFFFF'
                    tt_btn_open_modal_form.style.cursor = 'not-allowed'
                }
            })

            $('#entity_choice').on('change', function (event) {
                all_groups = []
                all_groups = clone_all_groups

                // Field is required or not depending on given rights
                let entityRights = data.filter(e => e.entities_id == entity_choice.value)
                let justificationRight = entityRights[0]['justification_transfer']
                let groupRight = entityRights[0]['allow_entity_only_transfer']
                let categoryRight = entityRights[0]['keep_category']

                if (categoryRight) {
                    document.querySelector('.adv-msg').style.display='';
                } else {
                    document.querySelector('.adv-msg').style.display='none';
                }

                if (justificationRight == 1) {
                    document.querySelector('#justification').required = true;
                } else {
                    document.querySelector('#justification').required = false;
                }

                all_groups.forEach(function(all_group) {
                    if (groupRight == 1 && all_group.id != 'tt_none') {
                        all_group.remove();
                    }
                    // Add groups of selected entity
                    if ('tt_plugin_entity_' + entity_choice.value == all_group.className || all_group.value == '') {
                        if (groupRight == 1 && all_group.id != 'tt_none') {
                            document.querySelector('#group_choice').appendChild(all_group)
                        } else if (groupRight == 0) {
                            document.querySelector('#group_choice').appendChild(all_group)
                        } else {
                            all_group.remove();
                        }
                    } else {
                    // Delete previous groups
                        all_group.remove()
                    }
                })

                if (groupRight == 0) {
                    document.querySelector('#nogroupfound').style.display = 'none';
                    document.querySelector('.tt_flex').style.display = '';
                } else {
                    if (document.querySelector('#group_choice')[1].id == 'tt_none'){
                        document.querySelector('#nogroupfound').style.display = '';
                        document.querySelector('.tt_flex').style.display = 'none';
                    }
                }

                // if another entity is chosen, reset the selected group
                document.querySelector('#no_select').selected = true
            })

            $('#group_choice').on('change', function (event) {
                // if no group selected, disable the confirm button
                if(document.querySelector('#no_select') !== null) {
                    if (document.querySelector('#no_select').selected == true) {
                        tt_btn_open_modal_form.disabled = true
                        tt_btn_open_modal_form.style.backgroundColor = '#D3D3D3'
                        tt_btn_open_modal_form.style.color = '#FFFFFF'
                        tt_btn_open_modal_form.style.cursor = 'not-allowed'
                    } else {
                    // else, enable it
                        tt_btn_open_modal_form.disabled = false
                        tt_btn_open_modal_form.style.backgroundColor = '#80cead'
                        tt_btn_open_modal_form.style.color = '#1e293b'
                        tt_btn_open_modal_form.style.cursor = 'pointer'
                    }
                }
            })

            let modal_form_adder = document.getElementById('tt_modal_form_adder')

            // Open modal
            document.querySelector('#canceltransfert').addEventListener('click', function(event){
                event.preventDefault()
                modal_form_adder.close();
            });

            // Close modal
            tt_btn_open_modal_form.addEventListener('click', function(event){
                event.preventDefault()
                modal_form_adder.showModal();
            });
        }
    }, 
    error: function (data) {
        console.log(data);
    }
});