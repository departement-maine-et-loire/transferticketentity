let activeRight = document.getElementsByName('_plugin_transferticketentity_use[31_0]')[1];
let bypassRight = document.getElementsByName('_plugin_transferticketentity_use[128_0]')[1];

activeRight.addEventListener('click', function(event) {
    if (!activeRight.checked) {
        bypassRight.checked = false;
    }
})

bypassRight.addEventListener('click', function(event) {
    if (bypassRight.checked) {
        activeRight.checked = true;
    }
})