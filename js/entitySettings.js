function displayValue() {
    if (document.querySelector('.allow_transfer').value == 0) {
        document.querySelector('#allow_entity_only_transfer').style.display = 'none'
        document.querySelector('#allow_entity_only_transfer').firstChild.childNodes[1][0].selected = true
        document.querySelector('#justification_transfer').style.display = 'none'
        document.querySelector('#justification_transfer').firstChild.childNodes[1][0].selected = true
        document.querySelector('#keep_category').style.display = 'none'
        document.querySelector('#keep_category').firstChild.childNodes[1][0].selected = true
        document.querySelector('#itilcategories_id').style.display = 'none'
    } else {
        document.querySelector('#allow_entity_only_transfer').style.display = ''
        document.querySelector('#justification_transfer').style.display = ''
        document.querySelector('#keep_category').style.display = ''
        if (document.querySelector('#keep_category').firstChild.childNodes[1][0].selected) {
            document.querySelector('#itilcategories_id').style.display = ''
        } else {
            document.querySelector('#itilcategories_id').style.display = 'none'
        }
    }
}

displayValue();

$('.transferticketentity').on('change', function(event) {
    displayValue();
})