document.addEventListener('DOMContentLoaded',function() {
    var prev = document.getElementById("leafer-button-left");
    var next = document.getElementById("leafer-button-right");
    new Tooltip(prev, {
        title: prev.getAttribute('data-tooltip'),
        trigger: "hover",
        placement: "right",
    });      
    new Tooltip(next, {
        title: next.getAttribute('data-tooltip'),
        trigger: "hover",
        placement: "left",
    });      
});