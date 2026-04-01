document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;
    let errorMsg = document.getElementById("error-msg");

    // Demo login (change later with PHP + DB)
    if (username === "admin" && password === "1234") {
        alert("Login Successful!");
        window.location.href = "dashboard.html"; // redirect
    } else {
        errorMsg.textContent = "Invalid Username or Password";
    }
    window.addEventListener("scroll", () => {
        document.querySelectorAll(".fade-in").forEach(el => {
            if (el.getBoundingClientRect().top < window.innerHeight - 100) {
                el.classList.add("active");
            }
        });
    });
});