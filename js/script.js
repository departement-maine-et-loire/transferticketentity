// Check if the form is there
if (document.querySelector('.tt_entity_choice') != null) {
    document.querySelector('#tt_gest_error').style.display='none'
    document.querySelector('.form_transfert').style.display='block'

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

    entity_choice.addEventListener('click', function (event) {
        // if value is empty, hide groups
        if (entity_choice.value == '') {
            tt_group_choice.style.display = 'none'
            tt_btn_open_modal_form.disabled = true
            tt_btn_open_modal_form.style.backgroundColor = '#D3D3D3'
            tt_btn_open_modal_form.style.color = '#FFFFFF'
            tt_btn_open_modal_form.style.cursor = 'not-allowed'
        } else {
        // if not, show them
            tt_group_choice.style.display = 'block'
            document.querySelector('#div_confirmation').style.display = 'block'
            document.querySelector('.tt_group_choice').style.display = 'block'
        }
    })

    entity_choice.addEventListener('change', function (event) {
        all_groups = []
        all_groups = clone_all_groups

        all_groups.forEach(function(all_group) {
            // Add groups of selected entity
            if ('tt_plugin_entity_' + entity_choice.value == all_group.className || all_group.value == '') {
                document.querySelector('#group_choice').appendChild(all_group)
            } else {
            // Delete previous groups
                all_group.remove()
            }
        })

        // if another entity is chosen, reset the selected group
        document.querySelector('#no_select').selected = true
    })

    document.querySelector('.form_transfert').addEventListener('click', function (event) {
        // if no group selected, disabled the confirm button
        if (document.querySelector('#group_choice').value == '') {
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

// function entityChange() {
//     if (entity_choice.value == '') {
//         tt_group_choice.style.display = 'none'
//         tt_btn_open_modal_form.disabled = true
//         tt_btn_open_modal_form.style.backgroundColor = '#D3D3D3'
//         tt_btn_open_modal_form.style.color = '#FFFFFF'
//         tt_btn_open_modal_form.style.cursor = 'not-allowed'
//         document.querySelector('#div_confirmation').style.display = ''
//         document.querySelector('.tt_group_choice').style.display = ''
//     } else {
//     // if not, show them
//         tt_group_choice.style.display = 'block'
//         document.querySelector('#div_confirmation').style.display = 'block'
//         document.querySelector('.tt_group_choice').style.display = 'block'
//     }

//     all_groups = []
//     all_groups = clone_all_groups

//     all_groups.forEach(function(all_group) {
//         // Add groups of selected entity
//         if ('tt_plugin_entity_' + entity_choice.value == all_group.className || all_group.value == '') {
//             document.querySelector('#group_choice').appendChild(all_group)
//         } else {
//         // Delete previous groups
//             all_group.remove()
//         }
//     })

//     // if another entity is chosen, reset the selected group
//     document.querySelector('#no_select').selected = true
// }