function proceedingOn(){
    let span = document.getElementById("proceeding")
    let submit = document.getElementById("submit")
    span.style.display = "initial"
    //submit.disabled = true
    console.log("on")
}

function proceedingOff(){
    let span = document.getElementById("proceeding")
    let submit = document.getElementById("submit")
    //submit.disabled = false
    span.style.display = "none"
}