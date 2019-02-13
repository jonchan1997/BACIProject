// Function to make IE9+ support forEach:
(function() {
    if (typeof NodeList.prototype.forEach === "function")
        return false;
    else
        NodeList.prototype.forEach = Array.prototype.forEach;
})();

window.onclick = function(event) {
    let modals = document.querySelectorAll("div[id$='Modal']");
    modals.forEach(function(modal) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
};