// Hamburger Functionality 
let hamburger = document.querySelector(".hamburger")
let navLinks = document.querySelector(".navLinks")

hamburger.addEventListener("click", ()=> {
    hamburger.classList.toggle("active")
    navLinks.classList.toggle("active")
})