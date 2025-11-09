</main>
  <footer class="site-footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>Band Management</h3>
          <p>Your complete solution for managing bands, members, and performances.</p>
        </div>
        <div class="footer-section">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="bands.php">Bands</a></li>
            <li><a href="members.php">Members</a></li>
            <li><a href="performances.php">Performances</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Connect</h4>
          <p>Stay updated with the latest news and updates from your favorite bands.</p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© <?=date('Y')?> Band Management. All rights reserved.</p>
        <p class="footer-tagline">Made with 🎵 for music lovers</p>
      </div>
    </div>
  </footer>

  <style>
    .site-footer {
      background: linear-gradient(135deg, #F8E4D8 0%, #DFB6B2 50%, #BFACE2 100%);
      color: #2B124C;
      padding: 50px 20px 30px;
      margin-top: 60px;
      border-top: 4px solid rgba(191, 172, 226, 0.6);
      box-shadow: 0 -8px 32px rgba(191, 172, 226, 0.3);
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
      padding-bottom: 30px;
      border-bottom: 2px solid rgba(43, 18, 76, 0.2);
    }

    .footer-section h3 {
      color: #2B124C;
      font-size: 24px;
      margin: 0 0 15px 0;
      font-weight: 800;
      background: linear-gradient(135deg, #2B124C, #522B5B, #854F6C);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .footer-section h4 {
      color: #522B5B;
      font-size: 18px;
      margin: 0 0 15px 0;
      font-weight: 700;
    }

    .footer-section p {
      color: #522B5B;
      line-height: 1.6;
      margin: 0;
      font-size: 15px;
    }

    .footer-links {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-links li {
      margin-bottom: 10px;
      background: none;
      padding: 0;
      border: none;
    }

    .footer-links a {
      color: #522B5B;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-block;
      padding: 5px 0;
      position: relative;
    }

    .footer-links a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #854F6C, #A084CA);
      transition: width 0.3s ease;
    }

    .footer-links a:hover {
      color: #854F6C;
      transform: translateX(5px);
    }

    .footer-links a:hover::after {
      width: 100%;
    }

    .footer-bottom {
      text-align: center;
      padding-top: 20px;
    }

    .footer-bottom p {
      color: #522B5B;
      margin: 8px 0;
      font-size: 14px;
    }

    .footer-tagline {
      font-weight: 600;
      background: linear-gradient(135deg, #854F6C, #A084CA, #BFACE2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-size: 15px !important;
    }

    @media (max-width: 768px) {
      .footer-content {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      
      .site-footer {
        padding: 40px 20px 25px;
      }
    }
  </style>
</body>
</html>