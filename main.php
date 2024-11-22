<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <title>AW-K9 Petshop & Grooming Station</title>
</head>
<body>
    <div class="header">
        <h1>
            <img src="css/header-bg.png" alt="AW-K9"> 
            Petshop & Grooming Station
        </h1>
        <div class="buttons">
            <a href="#about" class="btn">About Us</a>
            <a href="#services" class="btn">What We Do</a>
            <a href="#contact" class="btn">Find Us</a>
            <a href="login.php" class="btn">Sign In</a>
            <a href="register.php" class="btn">Join Now</a>
        </div>
    </div>
    <div class="container">
        <div class="content">
            <h2>Less hassle, more happiness!</h2>
            <p>Serving tails and tales in BF Resorts Las Pinas since '07. Your fur babies are family here!</p>
            <div class="services-list">
                <div class="service-item">
                    <i class="fas fa-cut fa-2x"></i>
                    <p>Professional Grooming</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-hotel fa-2x"></i>
                    <p>Pet Hotel Services</p>
                </div>
            </div>
        </div>
    </div>

    <div class="about-container" id="about">
        <div class="about-content">
            <h2>Who We Are</h2>
            <p>Hey there! We're the AW-K9 crew, and we've been making pets look fabulous since 2007. 
            Located in the heart of BF Resorts Las Pinas, we're that friendly neighborhood spot where 
            your pets come to get pampered.</p>
            
            <p>Our team? Just a bunch of pet-obsessed professionals who treat every furry visitor like 
            our own. Whether your pet needs a stylish trim or a cozy place to crash, we've got you covered!</p>
        </div>
    </div>

    <div class="services-container" id="services">
        <div class="services-content">
            <h2>Our Services</h2>
            <p>We offer a comprehensive range of pet care services to keep your furry friends happy and healthy</p>
            
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-cut"></i>
                    <h3>Professional Grooming</h3>
                    <p>Expert grooming services including bath, haircut, nail trimming, and ear cleaning for all breeds.</p>
                </div>
                
                <div class="service-card">
                    <i class="fas fa-hotel"></i>
                    <h3>Pet Hotel</h3>
                    <p>Comfortable and safe boarding facilities for your pets when you're away.</p>
                </div>
                
                <div class="service-card">
                    <i class="fas fa-store"></i>
                    <h3>Pet Supplies</h3>
                    <p>Quality pet food, accessories, and supplies for all your pet care needs.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="contact-container" id="contact">
        <div class="contact-content">
            <h2>Contact Us</h2>
            <p>Get in touch with us for all your pet care needs</p>
            
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Location</h3>
                        <p>Unit C CPSY Bldg. BF Resorts, Brgy. Talon Dos, Las Piñas, Philippines</p>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <h3>Phone</h3>
                        <p>0917 166 2959</p>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <h3>Email</h3>
                        <p>info@awk9petshop.com</p>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <h3>Business Hours</h3>
                        <p>Monday - Sunday: 8:00 AM - 5:00 PM</p>
                    </div>
                </div>
                
                <div class="map-container">
                    <img src="css/location-map.jpg" alt="AW-K9 Location Map" class="location-map">
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="socials">
            <center>
                <a href="https://www.facebook.com/awk9laspinas" class="social-link">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <p>&copy; 2024 AW-K9 Petshop & Grooming Station</p>
                <p>All rights reserved.</p>
            </center>
        </div>
    </div>
    <script>
        document.querySelector('a[href="#about"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('#about').scrollIntoView({
                behavior: 'smooth'
            });
        });

        document.querySelector('a[href="#services"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('#services').scrollIntoView({
                behavior: 'smooth'
            });
        });

        document.querySelector('a[href="#contact"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('#contact').scrollIntoView({
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
