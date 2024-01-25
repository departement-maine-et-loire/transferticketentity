function displayValue() {
    if (document.querySelector('.allow_transfer').value == 0) {
        document.querySelector('#allow_entity_only_transfer').style.display = 'none'
        document.querySelector('#allow_entity_only_transfer').firstChild.childNodes[1][0].selected = true
        document.querySelector('#justification_transfer').style.display = 'none'
        document.querySelector('#justification_transfer').firstChild.childNodes[1][0].selected = true
    } else {
        document.querySelector('#allow_entity_only_transfer').style.display = ''
        document.querySelector('#justification_transfer').style.display = ''
    }
}

displayValue();

$('.transferticketentity').on('change', function(event) {
    displayValue();
})