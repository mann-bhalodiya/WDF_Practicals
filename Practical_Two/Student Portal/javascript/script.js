const menuToggle = document.getElementById("menu-toggle");
const navLinks = document.getElementById("nav-links");

if (navLinks) {
    const currentPage = window.location.pathname.split("/").pop() || "index.html";
    navLinks.querySelectorAll("a").forEach((link) => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
        }
    });
}

if (menuToggle && navLinks) {
    menuToggle.type = "button";
    menuToggle.setAttribute("aria-controls", "nav-links");
    menuToggle.setAttribute("aria-expanded", "false");
    menuToggle.setAttribute("aria-label", "Open navigation menu");

    const setMenuState = (isOpen) => {
        navLinks.classList.toggle("active", isOpen);
        menuToggle.setAttribute("aria-expanded", String(isOpen));
        menuToggle.setAttribute("aria-label", isOpen ? "Close navigation menu" : "Open navigation menu");
        menuToggle.textContent = isOpen ? "×" : "☰";
    };

    menuToggle.addEventListener("click", () => {
        setMenuState(!navLinks.classList.contains("active"));
    });

    const links = navLinks.querySelectorAll("a");

    links.forEach((link) => {
        link.addEventListener("click", () => {
            setMenuState(false);
        });
    });

    document.addEventListener("click", (event) => {
        if (!event.target.closest("nav") && navLinks.classList.contains("active")) {
            setMenuState(false);
        }
    });

    window.addEventListener("resize", () => {
        if (window.innerWidth > 1140) {
            setMenuState(false);
        }
    });
}

document.querySelectorAll(".faq-question").forEach((question, index) => {
    const answer = question.nextElementSibling;
    if (!answer) return;

    const answerId = answer.id || `faq-answer-${index + 1}`;
    answer.id = answerId;
    question.setAttribute("role", "button");
    question.setAttribute("tabindex", "0");
    question.setAttribute("aria-controls", answerId);
    question.setAttribute("aria-expanded", "false");
    answer.hidden = true;
    answer.style.display = "none";

    const toggleAnswer = () => {
        const isOpen = question.getAttribute("aria-expanded") === "true";
        question.setAttribute("aria-expanded", String(!isOpen));
        answer.hidden = isOpen;
        answer.style.display = isOpen ? "none" : "block";
        question.closest(".card")?.classList.toggle("is-open", !isOpen);
    };

    question.addEventListener("click", toggleAnswer);
    question.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            toggleAnswer();
        }
    });
});

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.style.display = "block";
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
    const closeButton = modal.querySelector(".close");
    if (closeButton) closeButton.focus();
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.style.display = "none";
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
}

document.querySelectorAll(".modal").forEach((modal) => {
    modal.setAttribute("role", "dialog");
    modal.setAttribute("aria-modal", "true");
    modal.setAttribute("aria-hidden", "true");
    const closeButton = modal.querySelector(".close");
    if (closeButton) {
        closeButton.setAttribute("role", "button");
        closeButton.setAttribute("tabindex", "0");
        closeButton.setAttribute("aria-label", "Close dialog");
        closeButton.addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                closeModal(modal.id);
            }
        });
    }
    modal.addEventListener("click", (event) => {
        if (event.target === modal) closeModal(modal.id);
    });
});

document.querySelectorAll(".card[onclick^='openModal']").forEach((card) => {
    card.setAttribute("role", "button");
    card.setAttribute("tabindex", "0");
    card.setAttribute("aria-haspopup", "dialog");
    card.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            card.click();
        }
    });
});

document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;
    document.querySelectorAll(".modal[aria-hidden='false']").forEach((modal) => closeModal(modal.id));
});

const slides = document.querySelectorAll(".slide");
const nextBtn = document.querySelector(".next");
const prevBtn = document.querySelector(".prev");
let currentSlide = 0;
let sliderTimer;

function showSlide(index) {
    if (!slides.length) return;
    currentSlide = (index + slides.length) % slides.length;
    slides.forEach((slide, slideIndex) => {
        slide.classList.toggle("active", slideIndex === currentSlide);
        slide.setAttribute("aria-hidden", String(slideIndex !== currentSlide));
    });
}

function startSlider() {
    if (slides.length > 1) {
        sliderTimer = window.setInterval(() => showSlide(currentSlide + 1), 5000);
    }
}

function resetSliderTimer() {
    window.clearInterval(sliderTimer);
    startSlider();
}

if (nextBtn && prevBtn && slides.length) {
    nextBtn.type = "button";
    prevBtn.type = "button";
    nextBtn.setAttribute("aria-label", "Show next campus journal image");
    prevBtn.setAttribute("aria-label", "Show previous campus journal image");
    showSlide(0);
    nextBtn.addEventListener("click", () => {
        showSlide(currentSlide + 1);
        resetSliderTimer();
    });
    prevBtn.addEventListener("click", () => {
        showSlide(currentSlide - 1);
        resetSliderTimer();
    });
    startSlider();
}

function addNotificationBanner() {
    if (document.querySelector(".notification-banner")) return;
    const banner = document.createElement("aside");
    banner.className = "notification-banner";
    banner.setAttribute("role", "status");
    banner.innerHTML = '<span class="notification-icon" aria-hidden="true">✦</span><p><strong>New update:</strong> WDF Practical 01 submission deadline is July 23.</p><button type="button" class="notification-close" aria-label="Dismiss notification">×</button>';
    document.body.prepend(banner);
    banner.querySelector(".notification-close").addEventListener("click", () => {
        banner.classList.add("is-dismissed");
        window.setTimeout(() => banner.remove(), 300);
    });
}

addNotificationBanner();

function addThemeToggle() {
    const nav = document.querySelector("nav");
    if (!nav || document.querySelector(".theme-toggle")) return;

    const button = document.createElement("button");
    button.type = "button";
    button.className = "theme-toggle";
    button.setAttribute("aria-pressed", "false");
    button.setAttribute("aria-label", "Toggle light and dark theme");
    button.setAttribute("title", "Toggle light and dark theme");

    button.innerHTML = '<span class="toggle-icon toggle-sun" aria-hidden="true">☀</span><span class="toggle-track" aria-hidden="true"><span class="toggle-knob"></span></span><span class="toggle-icon toggle-moon" aria-hidden="true">☾</span>';

    const setState = (isDark) => {
        button.classList.toggle("on", isDark);
        button.setAttribute("aria-pressed", isDark ? "true" : "false");
        button.setAttribute("aria-label", isDark ? "Switch to light theme" : "Switch to dark theme");
        button.setAttribute("title", isDark ? "Switch to light theme" : "Switch to dark theme");
    };

    const applyTheme = (theme) => {
        const isDark = theme === "dark";
        document.documentElement.classList.toggle("dark-theme", isDark);
        setState(isDark);
    };

    const storedTheme = localStorage.getItem("eduPortalTheme") || "light";
    applyTheme(storedTheme);

    button.addEventListener("click", () => {
        const isDark = document.documentElement.classList.toggle("dark-theme");
        localStorage.setItem("eduPortalTheme", isDark ? "dark" : "light");
        setState(isDark);
    });

    // Keep the control in the viewport instead of tying it to a page-specific layout.
    document.body.appendChild(button);
}

addThemeToggle();

function startHeroClock() {
    const clock = document.getElementById("hero-clock");
    if (!clock) return;

    const updateClock = () => {
        clock.textContent = new Intl.DateTimeFormat(undefined, {
            weekday: "short",
            hour: "numeric",
            minute: "2-digit"
        }).format(new Date());
    };

    updateClock();
    window.setInterval(updateClock, 30000);
}

startHeroClock();

function showError(input, errorElement, message) {
    if (input) {
        input.classList.add("error");
    }

    if (errorElement) {
        errorElement.textContent = message;
    }
}

function clearError(input, errorElement) {
    if (input) {
        input.classList.remove("error");
    }

    if (errorElement) {
        errorElement.textContent = "";
    }
}

const loginForm = document.getElementById("loginForm");

if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
        const enrollmentInput = document.getElementById("enrollment");
        const passwordInput = document.getElementById("password");
        const enrollmentError = document.getElementById("enrollmentError");
        const passwordError = document.getElementById("passwordError");
        const enrollment = enrollmentInput ? enrollmentInput.value.trim() : "";
        const password = passwordInput ? passwordInput.value.trim() : "";

        clearError(enrollmentInput, enrollmentError);
        clearError(passwordInput, passwordError);

        let isValid = true;

        if (enrollment === "") {
            showError(enrollmentInput, enrollmentError, "Enrollment number is required.");
            isValid = false;
        } else if (!/^\d+$/.test(enrollment)) {
            showError(enrollmentInput, enrollmentError, "Enrollment number must contain only digits.");
            isValid = false;
        } else if (enrollment.length !== 10) {
            showError(enrollmentInput, enrollmentError, "Enrollment number must be exactly 10 digits.");
            isValid = false;
        }

        if (password === "") {
            showError(passwordInput, passwordError, "Password is required.");
            isValid = false;
        } else if (password.length < 6) {
            showError(passwordInput, passwordError, "Password must contain at least 6 characters.");
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
}

const registrationForm = document.getElementById("registrationForm");

if (registrationForm) {
    registrationForm.addEventListener("submit", function (event) {
        const fullNameInput = document.getElementById("fullName");
        const enrollmentInput = document.getElementById("enrollment");
        const emailInput = document.getElementById("email");
        const phoneInput = document.getElementById("phone");
        const courseInput = document.getElementById("course");
        const passwordInput = document.getElementById("password");
        const confirmPasswordInput = document.getElementById("confirmPassword");

        const fullNameError = document.getElementById("fullNameError");
        const enrollmentError = document.getElementById("enrollmentError");
        const emailError = document.getElementById("emailError");
        const phoneError = document.getElementById("phoneError");
        const courseError = document.getElementById("courseError");
        const passwordError = document.getElementById("passwordError");
        const confirmPasswordError = document.getElementById("confirmPasswordError");
        const successMessage = document.getElementById("registrationSuccess");

        const fullName = fullNameInput ? fullNameInput.value.trim() : "";
        const enrollment = enrollmentInput ? enrollmentInput.value.trim() : "";
        const email = emailInput ? emailInput.value.trim() : "";
        const phone = phoneInput ? phoneInput.value.trim() : "";
        const course = courseInput ? courseInput.value : "";
        const password = passwordInput ? passwordInput.value : "";
        const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : "";

        clearError(fullNameInput, fullNameError);
        clearError(enrollmentInput, enrollmentError);
        clearError(emailInput, emailError);
        clearError(phoneInput, phoneError);
        clearError(courseInput, courseError);
        clearError(passwordInput, passwordError);
        clearError(confirmPasswordInput, confirmPasswordError);

        if (successMessage) {
            successMessage.style.display = "none";
            successMessage.textContent = "";
        }

        let isValid = true;

        if (fullName === "") {
            showError(fullNameInput, fullNameError, "Full name is required.");
            isValid = false;
        } else if (!/^[A-Za-z ]{3,70}$/.test(fullName)) {
            showError(fullNameInput, fullNameError, "Enter a valid name with letters and spaces only.");
            isValid = false;
        }

        if (enrollment === "") {
            showError(enrollmentInput, enrollmentError, "Enrollment number is required.");
            isValid = false;
        } else if (!/^\d{10}$/.test(enrollment)) {
            showError(enrollmentInput, enrollmentError, "Enrollment number must be exactly 10 digits.");
            isValid = false;
        }

        if (email === "") {
            showError(emailInput, emailError, "Email address is required.");
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError(emailInput, emailError, "Please enter a valid email address.");
            isValid = false;
        }

        if (phone === "") {
            showError(phoneInput, phoneError, "Phone number is required.");
            isValid = false;
        } else if (!/^\d{10}$/.test(phone)) {
            showError(phoneInput, phoneError, "Phone number must be exactly 10 digits.");
            isValid = false;
        }

        if (course === "") {
            showError(courseInput, courseError, "Please select your course.");
            isValid = false;
        }

        if (password === "") {
            showError(passwordInput, passwordError, "Password is required.");
            isValid = false;
        } else if (password.length < 6) {
            showError(passwordInput, passwordError, "Password must contain at least 6 characters.");
            isValid = false;
        }

        if (confirmPassword === "") {
            showError(confirmPasswordInput, confirmPasswordError, "Please confirm your password.");
            isValid = false;
        } else if (password !== confirmPassword) {
            showError(confirmPasswordInput, confirmPasswordError, "Passwords do not match.");
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        if (successMessage) {
            successMessage.textContent = "Registration successful! You can now login to your account.";
            successMessage.style.display = "block";
        }
        registrationForm.reset();
    });
}

const adminLoginForm = document.getElementById("adminLoginForm");

if (adminLoginForm) {
    adminLoginForm.addEventListener("submit", function (event) {
        const adminIdInput = document.getElementById("admin-id");
        const adminKeyInput = document.getElementById("admin-key");
        const adminIdError = document.getElementById("adminIdError");
        const adminKeyError = document.getElementById("adminKeyError");
        const adminId = adminIdInput ? adminIdInput.value.trim() : "";
        const adminKey = adminKeyInput ? adminKeyInput.value.trim() : "";

        clearError(adminIdInput, adminIdError);
        clearError(adminKeyInput, adminKeyError);

        let isValid = true;

        if (adminId === "") {
            showError(adminIdInput, adminIdError, "Email or ID is required.");
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(adminId) && adminId.length < 3) {
            showError(adminIdInput, adminIdError, "Please enter a valid email or ID.");
            isValid = false;
        }

        if (adminKey === "") {
            showError(adminKeyInput, adminKeyError, "Security key is required.");
            isValid = false;
        } else if (adminKey.length < 6) {
            showError(adminKeyInput, adminKeyError, "Security key must contain at least 6 characters.");
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
}

const contactForm = document.getElementById("contactForm");

if (contactForm) {
    contactForm.addEventListener("submit", function (event) {
        const subjectInput = document.getElementById("subject");
        const messageInput = document.getElementById("msg");
        const subjectError = document.getElementById("subjectError");
        const messageError = document.getElementById("messageError");
        const subject = subjectInput ? subjectInput.value.trim() : "";
        const message = messageInput ? messageInput.value.trim() : "";

        clearError(subjectInput, subjectError);
        clearError(messageInput, messageError);

        let isValid = true;

        if (subject === "") {
            showError(subjectInput, subjectError, "Please select an issue category.");
            isValid = false;
        }

        if (message === "") {
            showError(messageInput, messageError, "Please enter a message.");
            isValid = false;
        } else if (message.length < 10) {
            showError(messageInput, messageError, "Message should be at least 10 characters long.");
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
}
