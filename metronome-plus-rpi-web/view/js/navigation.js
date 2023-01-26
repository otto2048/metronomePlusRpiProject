//set up active link on pages
window.onload = preparePage();

function preparePage() {
    var linkSel = document.getElementsByName("selectedLink") [0];
    var className = linkSel.getAttribute("class");
    
    if (className)
    {
        addSelected(className);
    }
}

function addSelected (page) {
    var link = document.getElementsByClassName("nav-link");
    for (var i=0; i<link.length; i++)
    {
        console.log(link[i].textContent);
        if (link[i].getAttribute("href") == page) {
            link[i].classList.add("active");
            link[i].setAttribute("aria-current", "page");
            break;
        }
    }
}