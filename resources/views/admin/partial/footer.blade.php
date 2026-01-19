<footer class="footer mt-auto py-3 bg-white text-center">
    <div class="container">
        <span class="text-muted"> Copyright © <span id="year"></span>  
            <a href="javascript:void(0);">
                <span class="fw-medium text-primary">Fast Ledger</span>
            </a> All rights reserved
        </span>
    </div>
</footer>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
 $(document).ready(function () {
    $(".nav-item.dropdown").hover(
        function () {
            $(this).find(".dropdown-menu").stop(true, true).addClass("show");
        },
        function () {
            $(this).find(".dropdown-menu").stop(true, true).removeClass("show");
        }
    );
});

    document.addEventListener("DOMContentLoaded", function () {
    let theme = localStorage.getItem("theme") || "light"; 
    document.body.classList.add(theme);

    // Example theme toggle button
    document.getElementById("theme-toggle").addEventListener("click", function () {
        document.body.classList.toggle("dark-mode");
        let newTheme = document.body.classList.contains("dark-mode") ? "dark" : "light";
        localStorage.setItem("theme", newTheme);
    });
});
document.addEventListener("DOMContentLoaded", function () {
    let navLinks = document.querySelectorAll(".nav-link");

    navLinks.forEach(link => {
        link.addEventListener("click", function () {
            navLinks.forEach(l => l.classList.remove("active"));
            this.classList.add("active");
        });
    });
});

</script>

<script>
    $(document).ready(function() {
        // Check if any of the dropdown items are active
        $('#reportsDropdown').on('click', function() {
            const dropdown = $(this).parent('.nav-item.dropdown'); // Get the dropdown li
            const hasActiveItem = dropdown.find('.dropdown-menu .active').length > 0;

            // Add active class to the parent link if any item inside the dropdown is active
            if (hasActiveItem) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });

        // Additional handling when the page is loaded
        // Check for active items when the page loads (in case the dropdown is already open)
        const dropdown = $('#reportsDropdown').parent('.nav-item.dropdown'); // Get the dropdown li
        const hasActiveItem = dropdown.find('.dropdown-menu .active').length > 0;

        if (hasActiveItem) {
            $('#reportsDropdown').addClass('active');
        }
    });
</script>
