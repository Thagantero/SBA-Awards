<footer>

        <button onclick="topFunction()" id="backToTopBtn" class="back-to-top" title="Go to top">
        <i class="fas fa-arrow-up"></i> Top
    </button>
    <div class="footer-content">
        <p>© <?php echo date("Y"); ?> SBA Awards. All rights reserved.</p>
        <div class="social-icons">
            <a href="your-twitter-link" target="_blank"><i class="fab fa-x-twitter"></i></a>
            <a href="your-facebook-link" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="your-instagram-link" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="your-linkedin-link" target="_blank"><i class="fab fa-linkedin-in"></i></a>
            <a href="your-youtube-link" target="_blank"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
</footer>
<html>
<!-- Optional JavaScript -->

<script>
    // Get the button
    var mybutton = document.getElementById("backToTopBtn");

    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    }
</script>

<script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="styles.css">
 <style>
        /* Your existing CSS styles */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: gold;
            color: black;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            display: none;
        }

        .back-to-top:hover {
            background-color: #f7d32e;
        }
    </style>
</body>
</html>
