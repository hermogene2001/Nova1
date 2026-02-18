<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Paper Arts - Subscription Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --secondary: #6366f1;
            --accent: #f59e0b;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --text: #334155;
            --text-light: #64748b;
            --bg: #f8fafc;
            --surface: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated gradient background */
        .gradient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -2;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glass morphism overlay */
        .glass-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(100px);
            z-index: -1;
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float-particle 20s infinite;
        }

        .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; left: 30%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 100px; height: 100px; left: 50%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 70px; height: 70px; left: 70%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 90px; height: 90px; left: 85%; animation-delay: 8s; }

        @keyframes float-particle {
            0%, 100% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-100px) scale(1); }
        }

        /* Main content wrapper */
        .container-fluid {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .content-wrapper {
            max-width: 1200px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 32px;
            padding: 80px 60px;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }

        .content-header {
            margin-bottom: 40px;
        }

        .content-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--dark), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .content-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: linear-gradient(135deg, #f8fafc, white);
            padding: 32px 24px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
            border-color: var(--primary);
        }

        .feature-card i {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            display: block;
        }

        .feature-card h4 {
            color: var(--dark);
            margin-bottom: 12px;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .feature-card p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            padding: 0 32px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(16, 185, 129, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-secondary {
            height: 56px;
            background: linear-gradient(135deg, var(--secondary), #4f46e5);
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            padding: 0 32px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(99, 102, 241, 0.4);
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 40px 24px;
                margin: 20px;
            }
            
            .content-title {
                font-size: 2rem;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="gradient-bg"></div>
    <div class="glass-overlay"></div>
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container-fluid">
        <div class="content-wrapper">
            <div class="content-header">
                <h1 class="content-title">Welcome to Recycling Paper Arts</h1>
                <p class="content-subtitle">Join our community of creative paper art enthusiasts and discover amazing projects that transform everyday materials into beautiful art pieces.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-book-open"></i>
                    <h4>Tutorials & Guides</h4>
                    <p>Step-by-step instructions for creating stunning paper art projects from beginners to advanced techniques.</p>
                </div>
                
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h4>Community Forum</h4>
                    <p>Connect with fellow artists, share your creations, and get inspired by the community.</p>
                </div>
                
                <div class="feature-card">
                    <i class="fas fa-shopping-cart"></i>
                    <h4>Shop Materials</h4>
                    <p>Find high-quality recycled paper and tools needed for your next creative project.</p>
                </div>
                
                <div class="feature-card">
                    <i class="fas fa-calendar-check"></i>
                    <h4>Workshops</h4>
                    <p>Join live virtual workshops led by expert paper artists from around the world.</p>
                </div>
            </div>
            
            <div class="cta-buttons">
                <a href="views/register.php" class="btn btn-primary">Subscribe Now</a>
                <a href="index.php" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add entrance animations
            const animateElements = document.querySelectorAll('.content-wrapper');
            animateElements.forEach((el, index) => {
                el.style.animation = `slideIn 0.6s ease ${index * 0.1}s both`;
            });
            
            // Add hover effects to feature cards
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>
    </body>
</html>