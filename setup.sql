-- ================================================================
-- BAMBA ADVENTURES — Complete Database Schema
-- Run this in phpMyAdmin after creating your database
-- ================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------
-- 1. ADMINS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    role ENUM('super','editor') DEFAULT 'editor',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 2. SETTINGS (key-value store for site config)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 3. DESTINATIONS (Regions)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    long_content TEXT,
    image VARCHAR(255),
    hero_image VARCHAR(255),
    meta_title VARCHAR(200),
    meta_description TEXT,
    featured ENUM('yes','no') DEFAULT 'no',
    status ENUM('active','draft') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 4. SERVICES
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'fas fa-star',
    description TEXT,
    long_content TEXT,
    image VARCHAR(255),
    meta_title VARCHAR(200),
    meta_description TEXT,
    featured ENUM('yes','no') DEFAULT 'no',
    status ENUM('active','draft') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 5. COUNTRIES (Subcategories under destinations)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active','draft') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------
-- 6. TOURS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    destination_id INT,
    country_id INT,
    duration VARCHAR(50),
    price DECIMAL(10,2),
    price_note VARCHAR(100),
    max_group_size INT,
    description TEXT,
    itinerary TEXT,
    inclusions TEXT,
    exclusions TEXT,
    highlights TEXT,
    images JSON,
    featured ENUM('yes','no') DEFAULT 'no',
    category VARCHAR(50) DEFAULT 'tour',
    status ENUM('active','draft') DEFAULT 'active',
    meta_title VARCHAR(200),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- 6. TOUR CATEGORIES (many-to-many with tours)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tour_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active','draft') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tour_category_links (
    tour_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (tour_id, category_id),
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES tour_categories(id) ON DELETE CASCADE
);

-- Default tour categories
INSERT IGNORE INTO tour_categories (name, slug, description, sort_order) VALUES
('Kenyan', 'kenyan', 'Tours based in Kenya and East Africa', 1),
('International', 'international', 'Tours outside of Africa', 2),
('Volunteer Package', 'volunteer', 'Meaningful volunteer and community engagement trips', 3),
('Safari', 'safari', 'Wildlife and safari focused experiences', 4),
('Beach', 'beach', 'Coastal and island getaways', 5),
('Honeymoon', 'honeymoon', 'Romantic packages for couples', 6);

-- ---------------------------------------------------------------
-- 8. BOOKINGS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    tour_id INT,
    travel_date DATE,
    adults INT DEFAULT 1,
    children INT DEFAULT 0,
    message TEXT,
    status ENUM('new','contacted','confirmed','cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- 9. INQUIRIES (Contact form submissions)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    page_url VARCHAR(255),
    status ENUM('new','read','replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 10. TESTIMONIALS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    text TEXT NOT NULL,
    avatar VARCHAR(255),
    rating INT DEFAULT 5,
    tour_id INT,
    status ENUM('active','hidden') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- 11. BLOG POSTS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    image VARCHAR(255),
    author VARCHAR(100),
    category VARCHAR(50),
    status ENUM('published','draft') DEFAULT 'draft',
    meta_title VARCHAR(200),
    meta_description TEXT,
    published_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 12. TEAM MEMBERS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    bio TEXT,
    image VARCHAR(255),
    status ENUM('active','hidden') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 13. FAQS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50),
    status ENUM('active','hidden') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 14. CAREERS (Job postings)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS careers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    department VARCHAR(100),
    location VARCHAR(100),
    job_type ENUM('full-time','part-time','contract','internship') DEFAULT 'full-time',
    description TEXT,
    requirements TEXT,
    status ENUM('active','closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 15. CAREER APPLICATIONS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS career_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    career_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    resume_path VARCHAR(255),
    cover_letter TEXT,
    status ENUM('new','reviewed','shortlisted','rejected') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- 16. VOLUNTEER APPLICATIONS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS volunteer_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    motivation TEXT,
    availability VARCHAR(200),
    skills TEXT,
    status ENUM('new','reviewed','accepted','rejected') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 17. SUBSCRIBERS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('active','unsubscribed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 18. GALLERY
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    category VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- 19. HOMEPAGE SECTIONS (for ordering/selection)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS homepage_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(50) NOT NULL UNIQUE,
    section_label VARCHAR(100),
    is_visible ENUM('yes','no') DEFAULT 'yes',
    sort_order INT DEFAULT 0
);

-- ---------------------------------------------------------------
-- INSERT DEFAULT DATA
-- ---------------------------------------------------------------

-- Default admin account. The password is set by admin/index.php on first
-- visit and displayed once - it is never stored in this file.
INSERT IGNORE INTO admins (username, password_hash, name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super');

-- Default settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'Bamba Adventures'),
('site_tagline', 'Premium Tours & Safaris'),
('contact_email', 'info@bambaadventures.com'),
('contact_phone', '+254 700 000 000'),
('contact_address', 'Nairobi, Kenya'),
('contact_whatsapp', '254700000000'),
('contact_hours', 'Mon – Sat, 8am – 6pm'),
('social_facebook', ''),
('social_instagram', ''),
('social_twitter', ''),
('social_linkedin', ''),
('social_youtube', ''),
('social_tiktok', ''),
('hero_title', 'Discover the World\'s Most Extraordinary Places'),
('hero_subtitle', 'Curated luxury tours, safaris & adventures across 7 continents'),
('hero_search_placeholder', 'Where do you want to go?'),
('hero_image', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1920'),
('cta_title', 'Ready for Your Next Adventure?'),
('cta_subtitle', 'Let our travel experts craft your perfect journey. From luxury safaris to Antarctic expeditions.'),
('cta_button_text', 'Start Planning'),
('cta_button_link', '/pages/book.php'),
('footer_copyright', '© 2025 Bamba Adventures. All rights reserved.'),
('footer_about_text', 'Bamba Adventures is Kenya\'s premier luxury tour operator, crafting extraordinary journeys across all seven continents.'),
('ga_id', ''),
('meta_title_default', 'Bamba Adventures | Premium Tours & Safaris'),
('meta_description_default', 'Discover luxury tours, safaris and adventures across all seven continents with Bamba Adventures.'),
('announcement_bar', '');

-- Default destinations
INSERT IGNORE INTO destinations (name, slug, description, image, sort_order) VALUES
('Africa', 'africa', 'The birthplace of safari. Experience the Big Five, ancient cultures, and landscapes that take your breath away.', 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=900', 1),
('Europe', 'europe', 'From Mediterranean coastlines to Nordic fjords. History, cuisine, and culture at every turn.', 'https://images.unsplash.com/photo-1467269204594-9661b134dd2b?w=900', 2),
('Asia', 'asia', 'Ancient temples, bustling markets, and serene landscapes. A continent of contrasts and wonders.', 'https://images.unsplash.com/photo-1464817739973-0128fe77aaa1?w=900', 3),
('North America', 'north-america', 'From Alaska\'s wilderness to the Caribbean\'s turquoise waters. Adventure on a grand scale.', 'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=900', 4),
('South America', 'south-america', 'Amazon rainforests, Andean peaks, and vibrant cultures. The spirit of adventure lives here.', 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=900', 5),
('Middle East', 'middle-east', 'Where ancient history meets modern luxury. Deserts, souks, and architectural marvels.', 'https://images.unsplash.com/photo-1542259679-45448f07g6c6?w=900', 6),
('Oceania', 'oceania', 'Australia\'s outback, New Zealand\'s fjords, and the paradise islands of the Pacific.', 'https://images.unsplash.com/photo-1523482580672-f109ba8cb9be?w=900', 7),
('Antarctica', 'antarctica', 'The last true wilderness. Penguins, icebergs, and landscapes from another planet.', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900', 8);

-- Default countries (subcategories under destinations)
INSERT IGNORE INTO countries (destination_id, name, slug, description, image, sort_order) VALUES
(1, 'Kenya', 'kenya', 'The birthplace of safari. Witness the Big Five, the Great Migration, and vibrant Maasai culture.', 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=900', 1),
(1, 'Tanzania', 'tanzania', 'Serengeti plains, Ngorongoro Crater, and the spice island of Zanzibar await.', 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=900', 2),
(1, 'South Africa', 'south-africa', 'Cape Town, Kruger National Park, and the Garden Route � a world in one country.', 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=900', 3),
(1, 'Rwanda', 'rwanda', 'Gorilla trekking in the misty mountains of Volcanoes National Park.', 'https://images.unsplash.com/photo-1504221507732-5246c045949b?w=900', 4),
(1, 'Egypt', 'egypt', 'Pyramids, Nile cruises, and Red Sea diving in the land of the pharaohs.', 'https://images.unsplash.com/photo-1539650116574-8efeb43e2750?w=900', 5),
(2, 'France', 'france', 'From Parisian elegance to Provencal charm and Alpine adventure.', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=900', 1),
(2, 'Italy', 'italy', 'Renaissance art, Tuscan vineyards, Amalfi Coastline, and Roman history.', 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=900', 2),
(2, 'Spain', 'spain', 'Flamenco, tapas, Gaudi architecture, and sun-drenched Mediterranean beaches.', 'https://images.unsplash.com/photo-1543783207-ec64e4d95325?w=900', 3),
(2, 'Greece', 'greece', 'Ancient ruins, island hopping, and Mediterranean sunsets.', 'https://images.unsplash.com/photo-1613395877344-13d4c280d288?w=900', 4),
(3, 'India', 'india', 'Taj Mahal, Rajasthan palaces, Kerala backwaters, and Himalayan treks.', 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?w=900', 1),
(3, 'Thailand', 'thailand', 'Bangkok temples, Chiang Mai mountains, and Phuket beaches.', 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=900', 2),
(3, 'Japan', 'japan', 'Ancient temples, neon cities, cherry blossoms, and Mount Fuji.', 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=900', 3),
(3, 'Bali', 'bali', 'Rice terraces, sacred temples, and tropical beaches in the Island of the Gods.', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=900', 4),
(3, 'Jordan', 'jordan', 'Petra, Wadi Rum desert, and the Dead Sea.', 'https://images.unsplash.com/photo-1579606038836-a8d9d2e0c4c3?w=900', 5),
(3, 'UAE / Dubai', 'uae-dubai', 'Futuristic skyscrapers, desert safaris, and luxury shopping.', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900', 6),
(3, 'Turkey', 'turkey', 'Cappadocia hot air balloons, Istanbul bazaars, and Mediterranean coast.', 'https://images.unsplash.com/photo-1527838832700-5059252407fa?w=900', 7),
(4, 'USA', 'usa', 'National parks, iconic cities, and diverse landscapes from coast to coast.', 'https://images.unsplash.com/photo-1485738422979-f5c462d49f74?w=900', 1),
(4, 'Canada', 'canada', 'Rocky Mountains, Niagara Falls, and vibrant multicultural cities.', 'https://images.unsplash.com/photo-1503614472-8c93d56e92ce?w=900', 2),
(4, 'Mexico', 'mexico', 'Mayan ruins, Caribbean beaches, and world-class cuisine.', 'https://images.unsplash.com/photo-1518105779142-d975f22f1b0a?w=900', 3),
(4, 'Costa Rica', 'costa-rica', 'Rainforest ziplines, volcano hikes, and Pacific surf.', 'https://images.unsplash.com/photo-1518259102261-b40117eabbc9?w=900', 4),
(5, 'Peru', 'peru', 'Machu Picchu, Sacred Valley, and Amazon rainforest adventures.', 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=900', 1),
(5, 'Brazil', 'brazil', 'Rio de Janeiro, Iguazu Falls, and the Amazon basin.', 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=900', 2),
(5, 'Argentina', 'argentina', 'Patagonia glaciers, Buenos Aires tango, and Mendoza wine country.', 'https://images.unsplash.com/photo-1589909202802-8f4aadce1849?w=900', 3),
(5, 'Colombia', 'colombia', 'Cartagena charm, coffee region, and Caribbean coast.', 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=900', 4),
(6, 'Egypt', 'egypt', 'Pyramids, Nile cruises, and Red Sea diving.', 'https://images.unsplash.com/photo-1539650116574-8efeb43e2750?w=900', 1),
(6, 'Jordan', 'jordan', 'Petra, Wadi Rum, and the Dead Sea.', 'https://images.unsplash.com/photo-1579606038836-a8d9d2e0c4c3?w=900', 2),
(6, 'UAE / Dubai', 'uae-dubai', 'Luxury, desert adventures, and modern wonders.', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900', 3),
(7, 'Australia', 'australia', 'Great Barrier Reef, Outback, and iconic cities.', 'https://images.unsplash.com/photo-1523482580672-f109ba8cb9be?w=900', 1),
(7, 'New Zealand', 'new-zealand', 'Fiords, glaciers, Maori culture, and adventure sports.', 'https://images.unsplash.com/photo-1469521669194-babb45599def?w=900', 2),
(7, 'Fiji', 'fiji', 'Tropical paradise with coral reefs and island hospitality.', 'https://images.unsplash.com/photo-1559128010-7c1ad6e1b6a5?w=900', 3),
(7, 'French Polynesia', 'french-polynesia', 'Bora Bora overwater bungalows and turquoise lagoons.', 'https://images.unsplash.com/photo-1532408840957-031d8034aeef?w=900', 4),
(8, 'Antarctic Peninsula', 'antarctic-peninsula', 'The classic Antarctic experience with penguins and icebergs.', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900', 1),
(8, 'South Georgia', 'south-georgia', 'Wildlife paradise with king penguins and elephant seals.', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900', 2),
(8, 'Falkland Islands', 'falkland-islands', 'Remote islands with unique birdlife and rugged beauty.', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900', 3);

-- Default services
INSERT IGNORE INTO services (name, slug, icon, description, sort_order) VALUES
('Air Ticketing', 'air-ticketing', 'fas fa-plane-departure', 'Domestic and international flight bookings at competitive rates.', 1),
('Hotel Bookings', 'hotel-bookings', 'fas fa-hotel', 'From boutique lodges to luxury resorts worldwide.', 2),
('Airport Transfers', 'airport-transfers', 'fas fa-shuttle-van', 'Seamless pickups and drop-offs in any destination.', 3),
('Honeymoon Packages', 'honeymoon-packages', 'fas fa-heart', 'Romantic getaways crafted for unforgettable memories.', 4),
('Hikes & Day Trips', 'hikes-day-trips', 'fas fa-hiking', 'Guided adventures for every fitness level.', 5),
('Corporate Team Building', 'corporate-team-building', 'fas fa-users', 'Custom corporate retreats and team experiences.', 6),
('Bush Safari Packages', 'bush-safari-packages', 'fas fa-paw', 'Kenya and East Africa\'s finest wildlife experiences.', 7),
('Beach Packages', 'beach-packages', 'fas fa-umbrella-beach', 'Coastal escapes to Diani, Zanzibar, and beyond.', 8),
('International Holiday', 'international-holiday', 'fas fa-globe', 'Full-service holiday planning to any destination.', 9);

-- Default homepage sections
INSERT IGNORE INTO homepage_sections (section_key, section_label, is_visible, sort_order) VALUES
('hero', 'Hero Banner', 'yes', 1),
('search', 'Search Bar', 'yes', 2),
('featured_tours', 'Featured Tours', 'yes', 3),
('destinations', 'Destinations Grid', 'yes', 4),
('why_choose', 'Why Choose Us', 'yes', 5),
('stats', 'Stats Counter', 'yes', 6),
('testimonials', 'Testimonials', 'yes', 7),
('blog', 'Blog Preview', 'yes', 8),
('cta', 'CTA Strip', 'yes', 9),
('partners', 'Partners Logos', 'yes', 10);

SET FOREIGN_KEY_CHECKS = 1;


-- ---------------------------------------------------------------
-- 20. CMS PAGES (About, FAQ, Terms, Contact, etc.)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    content LONGTEXT,
    icon VARCHAR(50) DEFAULT 'fas fa-file',
    show_in_nav ENUM('yes','no') DEFAULT 'yes',
    meta_title VARCHAR(200),
    meta_description TEXT,
    status ENUM('active','draft') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default CMS pages
INSERT IGNORE INTO pages (title, slug, icon, show_in_nav, meta_title, content, sort_order) VALUES
('About Us', 'about', 'fas fa-info-circle', 'yes', 'About Us | Bamba Adventures', '<h2>Our Story</h2><p>Bamba Adventures is Kenya\'s premier luxury tour operator, crafting extraordinary journeys across all seven continents.</p>', 1),
('FAQ', 'faq', 'fas fa-question-circle', 'yes', 'FAQ | Bamba Adventures', '<h2>Frequently Asked Questions</h2><p>Find answers to common questions about our tours, bookings, and policies.</p>', 2),
('Terms & Conditions', 'terms', 'fas fa-file-contract', 'no', 'Terms & Conditions | Bamba Adventures', '<h2>Terms of Service</h2><p>Please read these terms carefully before booking.</p>', 3),
('Contact', 'contact', 'fas fa-phone', 'yes', 'Contact Us | Bamba Adventures', '<h2>Get in Touch</h2><p>We would love to hear from you. Reach out to plan your next adventure.</p>', 4),
('Careers', 'careers', 'fas fa-briefcase', 'yes', 'Careers | Bamba Adventures', '<h2>Join Our Team</h2><p>We are always looking for passionate travel professionals.</p>', 5);


-- ===============================================================
-- MIGRATIONS FOR EXISTING DATABASES
-- Run these manually if your database already exists
-- ===============================================================

-- Add country_id to tours (for existing DBs)
-- ALTER TABLE tours ADD COLUMN country_id INT AFTER destination_id;
-- ALTER TABLE tours ADD FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL;

-- Add category to tours (for existing DBs created before this field)
-- ALTER TABLE tours ADD COLUMN category VARCHAR(50) DEFAULT 'tour' AFTER featured;

-- ================================================================
-- SECURITY SUPPORT TABLES
-- ================================================================
-- Both tables are also created on demand by includes/functions.php, so they
-- appear automatically on an existing install. They are declared here so a
-- fresh database has them from the start.
--   login_attempts - throttles the admin login (5 failures / 15 min / IP+user)
--   rate_limits    - throttles the public API and booking form

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip` VARCHAR(45) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `attempted_at` DATETIME NOT NULL,
  INDEX `idx_ip_time` (`ip`, `attempted_at`),
  INDEX `idx_username_time` (`username`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `rl_key` VARCHAR(190) NOT NULL,
  `created_at` DATETIME NOT NULL,
  INDEX `idx_key_time` (`rl_key`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
