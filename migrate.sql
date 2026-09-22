-- ===============================================================
-- BAMBA ADVENTURES DATABASE MIGRATION
-- Run this in phpMyAdmin (SQL tab) to upgrade an existing database
-- Safe to re-run: uses IF NOT EXISTS everywhere
-- ===============================================================

-- ---------------------------------------------------------------
-- 1. CREATE COUNTRIES TABLE
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- 2. ADD country_id TO tours
-- ---------------------------------------------------------------
SET @exist := (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_name = 'tours' AND column_name = 'country_id' AND table_schema = DATABASE());
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE tours ADD COLUMN country_id INT AFTER destination_id', 'SELECT "country_id already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key only if not exists
SET @fkexist := (SELECT COUNT(*) FROM information_schema.table_constraints 
    WHERE constraint_type = 'FOREIGN KEY' AND table_name = 'tours' 
    AND constraint_name = 'tours_country_id_foreign' AND table_schema = DATABASE());
SET @fksql := IF(@fkexist = 0, 
    'ALTER TABLE tours ADD CONSTRAINT tours_country_id_foreign FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL',
    'SELECT "FK already exists"');
PREPARE stmt FROM @fksql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 3. ADD featured TO destinations & services
-- ---------------------------------------------------------------
SET @destfeat := (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_name = 'destinations' AND column_name = 'featured' AND table_schema = DATABASE());
SET @destsql := IF(@destfeat = 0, 'ALTER TABLE destinations ADD COLUMN featured ENUM("yes","no") DEFAULT "no" AFTER hero_image', 'SELECT "destinations.featured already exists"');
PREPARE stmt FROM @destsql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @svcfeat := (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_name = 'services' AND column_name = 'featured' AND table_schema = DATABASE());
SET @svcsql := IF(@svcfeat = 0, 'ALTER TABLE services ADD COLUMN featured ENUM("yes","no") DEFAULT "no" AFTER image', 'SELECT "services.featured already exists"');
PREPARE stmt FROM @svcsql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. CREATE tour_categories & tour_category_links (if missing)
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

INSERT IGNORE INTO tour_categories (name, slug, description, sort_order) VALUES
('Kenyan', 'kenyan', 'Tours based in Kenya and East Africa', 1),
('International', 'international', 'Tours outside of Africa', 2),
('Volunteer Package', 'volunteer', 'Meaningful volunteer and community engagement trips', 3),
('Safari', 'safari', 'Wildlife and safari focused experiences', 4),
('Beach', 'beach', 'Coastal and island getaways', 5),
('Honeymoon', 'honeymoon', 'Romantic packages for couples', 6);

-- 5. ADD category TO tours (if missing from earlier migration)
-- ---------------------------------------------------------------
SET @catexist := (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_name = 'tours' AND column_name = 'category' AND table_schema = DATABASE());
SET @catsql := IF(@catexist = 0, 'ALTER TABLE tours ADD COLUMN category VARCHAR(50) DEFAULT "tour" AFTER featured', 'SELECT "category already exists"');
PREPARE stmt FROM @catsql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 6. CREATE pages TABLE (CMS pages module)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- 7. INSERT DEFAULT COUNTRIES
-- ---------------------------------------------------------------
INSERT IGNORE INTO countries (destination_id, name, slug, description, image, sort_order) VALUES
-- Africa (destination_id = 1)
(1, 'Kenya', 'kenya', 'The birthplace of safari. Witness the Big Five, the Great Migration, and vibrant Maasai culture.', 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=900', 1),
(1, 'Tanzania', 'tanzania', 'Serengeti plains, Ngorongoro Crater, and the spice island of Zanzibar await.', 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=900', 2),
(1, 'South Africa', 'south-africa', 'Cape Town, Kruger National Park, and the Garden Route — a world in one country.', 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=900', 3),
(1, 'Rwanda', 'rwanda', 'Gorilla trekking in the misty mountains of Volcanoes National Park.', 'https://images.unsplash.com/photo-1504221507732-5246c045949b?w=900', 4),
(1, 'Egypt', 'egypt', 'Pyramids, Nile cruises, and Red Sea diving in the land of the pharaohs.', 'https://images.unsplash.com/photo-1539650116574-8efeb43e2750?w=900', 5),
-- Europe (destination_id = 2)
(2, 'France', 'france', 'From Parisian elegance to Provencal charm and Alpine adventure.', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=900', 1),
(2, 'Italy', 'italy', 'Renaissance art, Tuscan vineyards, Amalfi Coastline, and Roman history.', 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=900', 2),
(2, 'Spain', 'spain', 'Flamenco, tapas, Gaudi architecture, and sun-drenched Mediterranean beaches.', 'https://images.unsplash.com/photo-1543783207-ec64e4d95325?w=900', 3),
(2, 'Greece', 'greece', 'Ancient ruins, island hopping, and Mediterranean sunsets.', 'https://images.unsplash.com/photo-1613395877344-13d4c280d288?w=900', 4),
-- Asia (destination_id = 3)
(3, 'India', 'india', 'Taj Mahal, Rajasthan palaces, Kerala backwaters, and Himalayan treks.', 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?w=900', 1),
(3, 'Thailand', 'thailand', 'Bangkok temples, Chiang Mai mountains, and Phuket beaches.', 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=900', 2),
(3, 'Japan', 'japan', 'Ancient temples, neon cities, cherry blossoms, and Mount Fuji.', 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=900', 3),
(3, 'Bali', 'bali', 'Rice terraces, sacred temples, and tropical beaches in the Island of the Gods.', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=900', 4),
(3, 'Jordan', 'jordan', 'Petra, Wadi Rum desert, and the Dead Sea.', 'https://images.unsplash.com/photo-1579606038836-a8d9d2e0c4c3?w=900', 5),
(3, 'UAE / Dubai', 'uae-dubai', 'Futuristic skyscrapers, desert safaris, and luxury shopping.', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900', 6),
(3, 'Turkey', 'turkey', 'Cappadocia hot air balloons, Istanbul bazaars, and Mediterranean coast.', 'https://images.unsplash.com/photo-1527838832700-5059252407fa?w=900', 7),
-- North America (destination_id = 4)
(4, 'USA', 'usa', 'National parks, iconic cities, and diverse landscapes from coast to coast.', 'https://images.unsplash.com/photo-1485738422979-f5c462d49f74?w=900', 1),
(4, 'Canada', 'canada', 'Rocky Mountains, Niagara Falls, and vibrant multicultural cities.', 'https://images.unsplash.com/photo-1503614472-8c93d56e92ce?w=900', 2),
(4, 'Mexico', 'mexico', 'Mayan ruins, Caribbean beaches, and world-class cuisine.', 'https://images.unsplash.com/photo-1518105779142-d975f22f1b0a?w=900', 3),
(4, 'Costa Rica', 'costa-rica', 'Rainforest ziplines, volcano hikes, and Pacific surf.', 'https://images.unsplash.com/photo-1518259102261-b40117eabbc9?w=900', 4),
-- South America (destination_id = 5)
(5, 'Peru', 'peru', 'Machu Picchu, Sacred Valley, and Amazon rainforest adventures.', 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=900', 1),
(5, 'Brazil', 'brazil', 'Rio de Janeiro, Iguazu Falls, and the Amazon basin.', 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=900', 2),
(5, 'Argentina', 'argentina', 'Patagonia glaciers, Buenos Aires tango, and Mendoza wine country.', 'https://images.unsplash.com/photo-1589909202802-8f4aadce1849?w=900', 3),
(5, 'Colombia', 'colombia', 'Cartagena charm, coffee region, and Caribbean coast.', 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=900', 4),
-- Middle East (destination_id = 6)
(6, 'Egypt', 'egypt', 'Pyramids, Nile cruises, and Red Sea diving.', 'https://images.unsplash.com/photo-1539650116574-8efeb43e2750?w=900', 1),
(6, 'Jordan', 'jordan', 'Petra, Wadi Rum, and the Dead Sea.', 'https://images.unsplash.com/photo-1579606038836-a8d9d2e0c4c3?w=900', 2),
(6, 'UAE / Dubai', 'uae-dubai', 'Luxury, desert adventures, and modern wonders.', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900', 3),
-- Oceania (destination_id = 7)
(7, 'Australia', 'australia', 'Great Barrier Reef, Outback, and iconic cities.', 'https://images.unsplash.com/photo-1523482580672-f109ba8cb9be?w=900', 1),
(7, 'New Zealand', 'new-zealand', 'Fiords, glaciers, Maori culture, and adventure sports.', 'https://images.unsplash.com/photo-1469521669194-babb45599def?w=900', 2),
(7, 'Fiji', 'fiji', 'Tropical paradise with coral reefs and island hospitality.', 'https://images.unsplash.com/photo-1559128010-7c1ad6e1b6a5?w=900', 3),
(7, 'French Polynesia', 'french-polynesia', 'Bora Bora overwater bungalows and turquoise lagoons.', 'https://images.unsplash.com/photo-1532408840957-031d8034aeef?w=900', 4),
-- Antarctica (destination_id = 8)
(8, 'Antarctic Peninsula', 'antarctic-peninsula', 'The classic Antarctic experience with penguins and icebergs.', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900', 1),
(8, 'South Georgia', 'south-georgia', 'Wildlife paradise with king penguins and elephant seals.', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900', 2),
(8, 'Falkland Islands', 'falkland-islands', 'Remote islands with unique birdlife and rugged beauty.', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900', 3);

-- ---------------------------------------------------------------
-- 8. INSERT DEFAULT CMS PAGES
-- ---------------------------------------------------------------
INSERT IGNORE INTO pages (title, slug, icon, show_in_nav, meta_title, content, sort_order) VALUES
('About Us', 'about', 'fas fa-info-circle', 'yes', 'About Us | Bamba Adventures', '<h2>Our Story</h2><p>Bamba Adventures is Kenya\'s premier luxury tour operator, crafting extraordinary journeys across all seven continents.</p>', 1),
('FAQ', 'faq', 'fas fa-question-circle', 'yes', 'FAQ | Bamba Adventures', '<h2>Frequently Asked Questions</h2><p>Find answers to common questions about our tours, bookings, and policies.</p>', 2),
('Terms & Conditions', 'terms', 'fas fa-file-contract', 'no', 'Terms & Conditions | Bamba Adventures', '<h2>Terms of Service</h2><p>Please read these terms carefully before booking.</p>', 3),
('Contact', 'contact', 'fas fa-phone', 'yes', 'Contact Us | Bamba Adventures', '<h2>Get in Touch</h2><p>We would love to hear from you. Reach out to plan your next adventure.</p>', 4),
('Careers', 'careers', 'fas fa-briefcase', 'yes', 'Careers | Bamba Adventures', '<h2>Join Our Team</h2><p>We are always looking for passionate travel professionals.</p>', 5),
('Volunteer', 'volunteer', 'fas fa-hands-helping', 'yes', 'Volunteer | Bamba Adventures', '<h2>Volunteer With Us</h2><p>Make a difference while exploring the world. Our volunteer packages combine meaningful work with unforgettable travel experiences.</p>', 6);

-- ---------------------------------------------------------------
-- 9. DONE
-- ---------------------------------------------------------------
SELECT 'Migration completed successfully!' AS status;
