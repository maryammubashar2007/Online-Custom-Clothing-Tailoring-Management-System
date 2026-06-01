USE master;
GO
IF EXISTS (SELECT name FROM sys.databases WHERE name = 'VelvaraDB')
BEGIN
    ALTER DATABASE VelvaraDB SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
    DROP DATABASE VelvaraDB;
END
GO
CREATE DATABASE VelvaraDB;
GO
USE VelvaraDB;
GO
CREATE TABLE houses (
    house_id      INT          NOT NULL IDENTITY(1,1),
    house_slug    VARCHAR(50)  NOT NULL,
    house_name    VARCHAR(100) NOT NULL,
    tagline       VARCHAR(255) NULL,
    accent_color  VARCHAR(10)  NULL,
    page_url      VARCHAR(100) NULL,
    display_order TINYINT      NOT NULL DEFAULT 1,
    is_active     BIT          NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT GETDATE(),
    CONSTRAINT pk_houses      PRIMARY KEY (house_id),
    CONSTRAINT uq_houses_slug UNIQUE (house_slug)
);
GO
INSERT INTO houses (house_slug, house_name, tagline, accent_color, page_url, display_order) VALUES
('men',      'Velvara Men',    'Where Every Man Wears His Stature',              '#1E5BFF', 'men.html',      1),
('women',    'Velvara Women',  'Where Every Woman Wears Her Story',              '#C9586E', 'women.html',    2),
('children', 'Velvara Petite', 'Miniature Couture Built for Movement and Memory','#3F7A4F', 'children.html', 3);
GO
CREATE TABLE atelier_locations (
    location_id      INT           NOT NULL IDENTITY(1,1),
    location_name    VARCHAR(150)  NOT NULL,
    address_line1    VARCHAR(255)  NULL,
    address_line2    VARCHAR(255)  NULL,
    city             VARCHAR(100)  NULL,
    country          VARCHAR(100)  NOT NULL DEFAULT 'Pakistan',
    phone            VARCHAR(50)   NULL,
    email            VARCHAR(150)  NULL,
    open_days        VARCHAR(100)  NULL,
    open_hours       VARCHAR(100)  NULL,
    appointment_only BIT           NOT NULL DEFAULT 1,
    google_maps_url  VARCHAR(500)  NULL,
    is_active        BIT           NOT NULL DEFAULT 1,
    created_at       DATETIME      NOT NULL DEFAULT GETDATE(),
    CONSTRAINT pk_atelier_locations PRIMARY KEY (location_id)
);
GO
INSERT INTO atelier_locations (location_name, address_line1, city, country, phone, email, open_days, open_hours) VALUES
('Flagship Atelier', 'Centaurus Mall, Blue Area', 'Islamabad', 'Pakistan',
 '+92-51-1234567', 'atelier@velvara.pk', 'Tue - Sat', '11:00 - 19:00');
GO
CREATE TABLE categories (
    category_id      INT          NOT NULL IDENTITY(1,1),
    house_id         INT          NOT NULL,
    category_slug    VARCHAR(80)  NOT NULL,
    category_name    VARCHAR(100) NOT NULL,
    badge_color      VARCHAR(10)  NULL,
    badge_text_color VARCHAR(10)  NULL,
    display_order    TINYINT      NOT NULL DEFAULT 1,
    is_active        BIT          NOT NULL DEFAULT 1,
    CONSTRAINT pk_categories            PRIMARY KEY (category_id),
    CONSTRAINT uq_categories_house_slug UNIQUE (house_id, category_slug),
    CONSTRAINT fk_categories_house      FOREIGN KEY (house_id)
        REFERENCES houses(house_id) ON DELETE NO ACTION ON UPDATE NO ACTION
);
GO
INSERT INTO categories (house_id, category_slug, category_name, badge_color, badge_text_color, display_order) VALUES
(1, 'wedding',  'Wedding',  '#0A2A6B', '#FFFFFF', 1),
(1, 'formal',   'Formal',   '#1E5BFF', '#FFFFFF', 2),
(1, 'casual',   'Casual',   '#B3D1FF', '#001A3D', 3),
(2, 'bridal',   'Bridal',   '#C9586E', '#FFFFFF', 1),
(2, 'formal',   'Formal',   '#8A3A4A', '#FFFFFF', 2),
(2, 'casual',   'Casual',   '#F5D0D8', '#3D0010', 3),
(3, 'ceremony', 'Ceremony', '#3F7A4F', '#FFFFFF', 1),
(3, 'festive',  'Festive',  '#2A5C38', '#FFFFFF', 2),
(3, 'casual',   'Casual',   '#B3E0C0', '#003A15', 3);
GO
CREATE TABLE fabric_options (
    fabric_id     INT          NOT NULL IDENTITY(1,1),
    fabric_name   VARCHAR(100) NOT NULL,
    fabric_origin VARCHAR(100) NULL,
    description   VARCHAR(500) NULL,
    is_premium    BIT          NOT NULL DEFAULT 0,
    CONSTRAINT pk_fabric_options PRIMARY KEY (fabric_id),
    CONSTRAINT uq_fabric_name    UNIQUE (fabric_name)
);
GO
INSERT INTO fabric_options (fabric_name, fabric_origin, is_premium) VALUES
('Italian Wool',    'Italy',    1),
('Egyptian Cotton', 'Egypt',    1),
('Raw Silk',        'India',    1),
('Jamawar',         'Kashmir',  1),
('Brocade',         'India',    1),
('Lawn',            'Pakistan', 0),
('Cotton',          'Pakistan', 0),
('Linen',           'Various',  0),
('Satin',           'Various',  0),
('Pure Wool',       'Various',  1);
GO
CREATE TABLE products (
    product_id      INT           NOT NULL IDENTITY(1,1),
    house_id        INT           NOT NULL,
    category_id     INT           NOT NULL,
    product_slug    VARCHAR(150)  NOT NULL,
    product_name    VARCHAR(200)  NOT NULL,
    short_desc      VARCHAR(500)  NULL,
    long_desc       VARCHAR(2000) NULL,
    price_min       DECIMAL(10,2) NOT NULL,
    price_max       DECIMAL(10,2) NOT NULL,
    currency        CHAR(3)       NOT NULL DEFAULT 'PKR',
    lead_time_weeks VARCHAR(50)   NULL,
    is_bespoke      BIT           NOT NULL DEFAULT 1,
    is_featured     BIT           NOT NULL DEFAULT 0,
    display_order   TINYINT       NOT NULL DEFAULT 1,
    is_active       BIT           NOT NULL DEFAULT 1,
    created_at      DATETIME      NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME      NOT NULL DEFAULT GETDATE(),
    CONSTRAINT pk_products          PRIMARY KEY (product_id),
    CONSTRAINT uq_product_slug      UNIQUE (product_slug),
    CONSTRAINT fk_products_house    FOREIGN KEY (house_id)
        REFERENCES houses(house_id)        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories(category_id) ON DELETE NO ACTION ON UPDATE NO ACTION
);
GO
INSERT INTO products (house_id, category_id, product_slug, product_name, short_desc, price_min, price_max, lead_time_weeks, is_featured) VALUES
(1, 1, 'men-wedding-sherwani',        'Wedding Sherwani',         'Hand-embroidered sherwanis with zardozi, dabka, and intricate thread work.',        65000, 280000, '8 - 12 weeks',  1),
(1, 1, 'men-three-piece-tuxedo',      'Three-Piece Tuxedo',       'Classic tuxedos in pure wool with satin lapels engineered for the reception.',      55000, 165000, '6 - 10 weeks',  0),
(1, 1, 'men-prince-coat',             'Prince Coat',              'Regal prince coats in silk and brocade with hand-stitched tonal embroidery.',       45000, 140000, '6 - 10 weeks',  0),
(1, 2, 'men-two-piece-suit',          'Two-Piece Suit',           'Bespoke business suits in Italian wool built for the boardroom.',                   28000,  85000, '4 - 8 weeks',   0),
(1, 2, 'men-waistcoat-trouser-set',   'Waistcoat Trouser Set',    'Sharp waistcoats paired with trousers for engagements and formal evenings.',        18000,  55000, '4 - 6 weeks',   0),
(1, 2, 'men-premium-shalwar-kameez',  'Premium Shalwar Kameez',   'Tailored shalwar kameez in fine fabrics where tradition meets precision cutting.',   8500,  28000, '3 - 5 weeks',   0),
(1, 3, 'men-designer-kurta-pajama',   'Designer Kurta Pajama',    'Lightweight kurtas in lawn and cotton. Breathable everyday luxury.',                 4500,  14000, '2 - 4 weeks',   0),
(1, 3, 'men-casual-blazer',           'Casual Blazer',            'Unstructured blazers in linen and cotton. Easy elegance for any occasion.',         14000,  42000, '3 - 5 weeks',   0),
(1, 3, 'men-tailored-shirt',          'Tailored Shirt',           'Made-to-measure shirts in Egyptian cotton cut to your specification.',               3500,   9500, '2 - 3 weeks',   0),
(2, 4, 'women-bridal-lehenga',        'Bridal Lehenga',           'Opulent hand-embroidered lehengas in silk and organza for the grandest entrance.', 120000, 450000, '10 - 14 weeks', 1),
(2, 4, 'women-bridal-gharara',        'Bridal Gharara',           'Heritage gharara sets with fine zardozi embroidery steeped in tradition.',          95000, 320000, '10 - 14 weeks', 0),
(2, 4, 'women-nikkah-angrakha',       'Nikkah Angrakha',          'Ethereal angrakha silhouettes in chiffon and net for the nikkah ceremony.',         75000, 220000, '8 - 12 weeks',  0),
(2, 5, 'women-couture-saree',         'Couture Saree',            'Bespoke draped sarees in Banarasi silk with hand-woven borders.',                   45000, 180000, '6 - 10 weeks',  0),
(2, 5, 'women-formal-sharara',        'Formal Sharara Set',       'Elegant sharara sets tailored for walimas, engagements, and formal ceremonies.',    35000, 110000, '5 - 8 weeks',   0),
(2, 6, 'women-pret-kurta-set',        'Pret Kurta Set',           'Refined everyday kurta sets in fine lawn and khaddar.',                              6500,  22000, '2 - 4 weeks',   0),
(2, 6, 'women-bespoke-evening-gown',  'Bespoke Evening Gown',     'Custom evening gowns for galas, dinners, and black-tie affairs.',                   55000, 175000, '6 - 10 weeks',  0),
(3, 7, 'children-miniature-sherwani', 'Miniature Sherwani',       'Heritage sherwanis scaled to young frames. Hand-embroidered for the wedding day.',  18000,  65000, '5 - 8 weeks',   1),
(3, 7, 'children-flower-girl-lehenga','Flower Girl Lehenga',      'Dreamy lehengas with delicate floral embroidery for the flower girl.',              22000,  75000, '5 - 8 weeks',   0),
(3, 7, 'children-ceremonial-gown',    'Ceremonial Gown',          'Heirloom-quality gowns in silk organza built for movement and memory.',             28000,  90000, '6 - 8 weeks',   0),
(3, 8, 'children-eid-festive-set',    'Eid Festive Set',          'Vibrant festive sets in soft cotton and silk blends for long celebrations.',         5500,  18000, '2 - 4 weeks',   0),
(3, 9, 'children-everyday-kurta',     'Everyday Kurta',           'Breathable, playful kurtas for daily wear with thoughtful construction.',            2500,   7500, '1 - 3 weeks',   0);
GO
CREATE TABLE product_images (
    image_id      INT          NOT NULL IDENTITY(1,1),
    product_id    INT          NOT NULL,
    image_url     VARCHAR(500) NOT NULL,
    alt_text      VARCHAR(255) NULL,
    is_primary    BIT          NOT NULL DEFAULT 0,
    display_order TINYINT      NOT NULL DEFAULT 1,
    CONSTRAINT pk_product_images         PRIMARY KEY (image_id),
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id)
        REFERENCES products(product_id) ON DELETE NO ACTION ON UPDATE NO ACTION
);
GO
INSERT INTO product_images (product_id, image_url, alt_text, is_primary, display_order) VALUES
(1,  'https://www.ismailfarid.com/cdn/shop/files/SRW268-1_533x.jpg',                 'Wedding Sherwani',        1, 1),
(2,  'https://images.unsplash.com/photo-1593032465175-481ac7f401a0?w=800',            'Three-Piece Tuxedo',      1, 1),
(3,  'https://images.unsplash.com/photo-1598808503746-f34c53b9323e?w=800',            'Prince Coat',             1, 1),
(4,  'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=800',            'Two-Piece Suit',          1, 1),
(5,  'https://www.muraqshman.com/cdn/shop/files/DSC00508.jpg',                        'Waistcoat Trouser Set',   1, 1),
(7,  'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800',            'Designer Kurta Pajama',   1, 1),
(9,  'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800',            'Tailored Shirt',          1, 1),
(10, 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=800',            'Bridal Lehenga',          1, 1),
(17, 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=800',               'Miniature Sherwani',      1, 1),
(18, 'https://images.unsplash.com/photo-1519741497674-611481863552?w=800',            'Flower Girl Lehenga',     1, 1);
GO
CREATE TABLE product_fabric_options (
    product_id INT NOT NULL,
    fabric_id  INT NOT NULL,
    CONSTRAINT pk_product_fabric PRIMARY KEY (product_id, fabric_id),
    CONSTRAINT fk_pfo_product    FOREIGN KEY (product_id)
        REFERENCES products(product_id)      ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_pfo_fabric     FOREIGN KEY (fabric_id)
        REFERENCES fabric_options(fabric_id) ON DELETE NO ACTION ON UPDATE NO ACTION
);
GO
INSERT INTO product_fabric_options (product_id, fabric_id) VALUES
(1,3),(1,5),(2,1),(2,9),(3,3),(3,5),(4,1),(5,1),(5,3),
(6,6),(6,7),(7,6),(7,7),(8,8),(9,2),
(10,3),(10,4),(11,3),(11,5),(12,3),(12,9),
(13,3),(13,4),(14,3),(15,6),
(17,3),(17,5),(18,3),(18,9),(19,3),(19,9);
GO
CREATE TABLE customers (
    customer_id   INT          NOT NULL IDENTITY(1,1),
    first_name    VARCHAR(100) NOT NULL,
    last_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(200) NOT NULL,
    phone         VARCHAR(50)  NULL,
    city          VARCHAR(100) NULL,
    country       VARCHAR(100) NOT NULL DEFAULT 'Pakistan',
    date_of_birth DATE         NULL,
    gender        VARCHAR(10)  NULL,
    notes         VARCHAR(500) NULL,
    created_at    DATETIME     NOT NULL DEFAULT GETDATE(),
    updated_at    DATETIME     NOT NULL DEFAULT GETDATE(),
    CONSTRAINT pk_customers        PRIMARY KEY (customer_id),
    CONSTRAINT uq_customer_email   UNIQUE (email),
    CONSTRAINT chk_customer_gender CHECK (gender IN ('Male', 'Female', 'Other'))
);
GO
INSERT INTO customers (first_name, last_name, email, phone, city, country, date_of_birth, gender) VALUES
('Aanya',   'Mehra',    'aanya.mehra@gmail.com',     '+92-300-1234567', 'Islamabad',  'Pakistan', '1995-03-14', 'Female'),
('Farhan',  'Siddiqui', 'farhan.siddiqui@gmail.com', '+92-321-9876543', 'Lahore',     'Pakistan', '1990-07-22', 'Male'),
('Zara',    'Khan',     'zara.khan@hotmail.com',      '+92-333-4567890', 'Karachi',    'Pakistan', '1998-11-05', 'Female'),
('Ali',     'Hassan',   'ali.hassan@outlook.com',     '+92-345-1122334', 'Islamabad',  'Pakistan', '1988-01-30', 'Male'),
('Sana',    'Bukhari',  'sana.bukhari@gmail.com',     '+92-311-2233445', 'Rawalpindi', 'Pakistan', '1993-06-18', 'Female'),
('Imran',   'Qureshi',  'imran.qureshi@gmail.com',    '+92-312-3344556', 'Lahore',     'Pakistan', '1985-09-12', 'Male'),
('Mariam',  'Tahir',    'mariam.tahir@gmail.com',     '+92-315-5566778', 'Islamabad',  'Pakistan', '2000-02-27', 'Female'),
('Bilal',   'Chaudhry', 'bilal.chaudhry@yahoo.com',   '+92-316-6677889', 'Faisalabad', 'Pakistan', '1992-04-03', 'Male'),
('Hina',    'Nawaz',    'hina.nawaz@gmail.com',       '+92-317-7788990', 'Multan',     'Pakistan', '1996-08-15', 'Female'),
('Usman',   'Riaz',     'usman.riaz@gmail.com',       '+92-318-8899001', 'Islamabad',  'Pakistan', '1987-12-20', 'Male'),
('Nadia',   'Akhtar',   'nadia.akhtar@gmail.com',     '+92-319-9900112', 'Peshawar',   'Pakistan', '1994-05-09', 'Female'),
('Hamza',   'Malik',    'hamza.malik@outlook.com',    '+92-320-0011223', 'Karachi',    'Pakistan', '1991-10-25', 'Male'),
('Fareeha', 'Aziz',     'fareeha.aziz@gmail.com',     '+92-322-1122334', 'Lahore',     'Pakistan', '1997-07-07', 'Female'),
('Saad',    'Javed',    'saad.javed@gmail.com',       '+92-323-2233445', 'Islamabad',  'Pakistan', '1989-03-31', 'Male'),
('Amna',    'Zuberi',   'amna.zuberi@hotmail.com',    '+92-324-3344556', 'Karachi',    'Pakistan', '2001-01-14', 'Female');
GO
CREATE TABLE appointment_requests (
    appointment_id INT          NOT NULL IDENTITY(1,1),
    first_name     VARCHAR(100) NOT NULL,
    last_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(200) NOT NULL,
    phone          VARCHAR(50)  NULL,
    house_id       INT          NULL,
    preferred_date DATE         NULL,
    occasion_notes VARCHAR(500) NULL,
    status         VARCHAR(20)  NOT NULL DEFAULT 'pending',
    source_page    VARCHAR(50)  NULL,
    created_at     DATETIME     NOT NULL DEFAULT GETDATE(),
    updated_at     DATETIME     NOT NULL DEFAULT GETDATE(),
    CONSTRAINT pk_appointment_requests  PRIMARY KEY (appointment_id),
    CONSTRAINT chk_appointment_status   CHECK (status IN ('pending','confirmed','completed','cancelled')),
    CONSTRAINT fk_appointments_house    FOREIGN KEY (house_id)
        REFERENCES houses(house_id) ON DELETE NO ACTION ON UPDATE NO ACTION
);
GO
INSERT INTO appointment_requests (first_name, last_name, email, phone, house_id, preferred_date, occasion_notes, status, source_page) VALUES
('Aanya',   'Mehra',    'aanya.mehra@gmail.com',     '+92-300-1234567', 2, '2026-07-15', 'Bridal lehenga for wedding on 20 Jul 2026. Prefer dusty rose with gold embroidery.',  'confirmed', 'women'),
('Farhan',  'Siddiqui', 'farhan.siddiqui@gmail.com', '+92-321-9876543', 1, '2026-06-28', 'Wedding sherwani in deep navy with zardozi for reception and nikkah.',                'pending',   'men'),
('Zara',    'Khan',     'zara.khan@hotmail.com',      '+92-333-4567890', 3, '2026-08-10', 'Flower-girl lehenga for niece aged 6, pink and mint colour scheme.',                  'pending',   'children'),
('Ali',     'Hassan',   'ali.hassan@outlook.com',     '+92-345-1122334', 1, '2026-07-05', 'Three-piece suit for corporate event, charcoal grey, slim fit.',                      'confirmed', 'men'),
('Sana',    'Bukhari',  'sana.bukhari@gmail.com',     '+92-311-2233445', 2, '2026-07-20', 'Formal sharara set for walima ceremony, ivory and silver, fully embellished.',        'confirmed', 'women'),
('Imran',   'Qureshi',  'imran.qureshi@gmail.com',    '+92-312-3344556', 1, '2026-07-12', 'Prince coat for nikkah in forest green silk, matching kurta requested.',              'pending',   'men'),
('Mariam',  'Tahir',    'mariam.tahir@gmail.com',     '+92-315-5566778', 2, '2026-08-02', 'Nikkah angrakha in pastel peach chiffon with mirror work detailing.',                 'confirmed', 'women'),
('Bilal',   'Chaudhry', 'bilal.chaudhry@yahoo.com',   '+92-316-6677889', 1, '2026-06-30', 'Bespoke two-piece suit for engagement, midnight blue Italian wool.',                  'completed', 'men'),
('Hina',    'Nawaz',    'hina.nawaz@gmail.com',       '+92-317-7788990', 2, '2026-07-25', 'Bridal gharara in crimson and gold jamawar, full court train.',                       'confirmed', 'women'),
('Usman',   'Riaz',     'usman.riaz@gmail.com',       '+92-318-8899001', 1, '2026-08-18', 'Casual linen blazer in olive for a product launch event.',                            'pending',   'men'),
('Nadia',   'Akhtar',   'nadia.akhtar@gmail.com',     '+92-319-9900112', 2, '2026-09-05', 'Evening gown for charity gala, emerald green, floor length with cape.',               'pending',   'women'),
('Hamza',   'Malik',    'hamza.malik@outlook.com',    '+92-320-0011223', 1, '2026-07-08', 'Premium shalwar kameez set for Eid, three colours, lawn fabric.',                     'completed', 'men'),
('Fareeha', 'Aziz',     'fareeha.aziz@gmail.com',     '+92-322-1122334', 3, '2026-08-22', 'Ceremonial gown for daughter aged 8, ballet pink with tulle overlay.',               'pending',   'children'),
('Saad',    'Javed',    'saad.javed@gmail.com',       '+92-323-2233445', 1, '2026-06-20', 'Tailored shirts, 5 units in different colours, Egyptian cotton, point collar.',        'completed', 'men'),
('Amna',    'Zuberi',   'amna.zuberi@hotmail.com',    '+92-324-3344556', 2, '2026-09-15', 'Couture saree in Banarasi silk for reception, wine red with antique gold border.',     'pending',   'women');
GO
CREATE TABLE orders (
    order_id        INT           NOT NULL IDENTITY(1,1),
    customer_id     INT           NOT NULL,
    appointment_id  INT           NULL,
    house_id        INT           NULL,
    order_status    VARCHAR(20)   NOT NULL DEFAULT 'enquiry',
    order_date      DATE          NOT NULL,
    expected_ready  DATE          NULL,
    actual_ready    DATE          NULL,
    total_amount    DECIMAL(12,2) NULL,
    currency        CHAR(3)       NOT NULL DEFAULT 'PKR',
    payment_status  VARCHAR(10)   NOT NULL DEFAULT 'unpaid',
    special_notes   VARCHAR(500)  NULL,
    created_at      DATETIME      NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME      NOT NULL DEFAULT GETDATE(),
    CONSTRAINT pk_orders              PRIMARY KEY (order_id),
    CONSTRAINT chk_order_status       CHECK (order_status IN ('enquiry','in_design','sampling','fittings','ready','delivered','cancelled')),
    CONSTRAINT chk_payment_status     CHECK (payment_status IN ('unpaid','partial','paid')),
    CONSTRAINT fk_orders_customer     FOREIGN KEY (customer_id)
        REFERENCES customers(customer_id)               ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_orders_appointment  FOREIGN KEY (appointment_id)
        REFERENCES appointment_requests(appointment_id) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_orders_house        FOREIGN KEY (house_id)
        REFERENCES houses(house_id)                     ON DELETE NO ACTION ON UPDATE NO ACTION
);
GO
INSERT INTO orders (customer_id, appointment_id, house_id, order_status, order_date, expected_ready, actual_ready, total_amount, payment_status, special_notes) VALUES
(1,  1,  2, 'sampling',  '2026-05-10', '2026-07-10', NULL,         280000, 'partial', 'Dusty rose bridal lehenga with gold thread zardozi.'),
(2,  2,  1, 'in_design', '2026-06-01', '2026-08-15', NULL,         180000, 'unpaid',  'Deep navy wedding sherwani, zardozi motifs across chest.'),
(4,  4,  1, 'fittings',  '2026-04-20', '2026-06-25', NULL,          75000, 'partial', 'Charcoal grey three-piece suit, slim fit, peak lapel.'),
(5,  5,  2, 'in_design', '2026-05-25', '2026-07-20', NULL,         110000, 'partial', 'Ivory and silver sharara, fully hand-embellished.'),
(6,  6,  1, 'enquiry',   '2026-06-05', '2026-08-12', NULL,         140000, 'unpaid',  'Forest green silk prince coat for nikkah.'),
(7,  7,  2, 'sampling',  '2026-06-10', '2026-08-02', NULL,          85000, 'partial', 'Pastel peach angrakha, mirror work detailing.'),
(8,  8,  1, 'delivered', '2026-03-15', '2026-06-30', '2026-06-28',  65000, 'paid',    'Midnight blue engagement suit, Italian wool.'),
(9,  9,  2, 'sampling',  '2026-05-18', '2026-07-25', NULL,         320000, 'partial', 'Crimson and gold gharara, full court train.'),
(11, 11, 2, 'enquiry',   '2026-06-12', '2026-09-05', NULL,         175000, 'unpaid',  'Emerald green evening gown with cape sleeve.'),
(12, 12, 1, 'delivered', '2026-03-01', '2026-07-08', '2026-07-06',  24000, 'paid',    'Three lawn shalwar kameez sets for Eid.'),
(13, 13, 3, 'enquiry',   '2026-06-15', '2026-08-22', NULL,          55000, 'unpaid',  'Ballet pink ceremonial gown, tulle overlay, age 8.'),
(14, 14, 1, 'delivered', '2026-02-20', '2026-06-20', '2026-06-18',  47500, 'paid',    'Five tailored shirts in Egyptian cotton, point collar.'),
(15, 15, 2, 'enquiry',   '2026-06-18', '2026-09-15', NULL,         165000, 'unpaid',  'Wine red Banarasi saree, antique gold border.');
GO
CREATE TABLE order_items (
    item_id      INT           NOT NULL IDENTITY(1,1),
    order_id     INT           NOT NULL,
    product_id   INT           NOT NULL,
    fabric_id    INT           NULL,
    custom_color VARCHAR(100)  NULL,
    custom_notes VARCHAR(500)  NULL,
    quantity     TINYINT       NOT NULL DEFAULT 1,
    unit_price   DECIMAL(10,2) NOT NULL,
    CONSTRAINT pk_order_items         PRIMARY KEY (item_id),
    CONSTRAINT fk_order_items_order   FOREIGN KEY (order_id)
        REFERENCES orders(order_id)          ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id)
        REFERENCES products(product_id)      ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_order_items_fabric  FOREIGN KEY (fabric_id)
        REFERENCES fabric_options(fabric_id) ON DELETE NO ACTION ON UPDATE NO ACTION
);
GO
INSERT INTO order_items (order_id, product_id, fabric_id, custom_color, custom_notes, quantity, unit_price) VALUES
(1,  10, 3, 'Dusty Rose',       'Gold zardozi on neckline and hem, full flare skirt.',            1, 280000),
(2,   1, 5, 'Deep Navy',        'Dabka embroidery on chest, contrast ivory inner sherwani.',      1, 180000),
(3,   2, 1, 'Charcoal Grey',    'Slim fit, peak lapel, matching waistcoat, silver buttons.',      1,  75000),
(4,  14, 3, 'Ivory',            'Full silver hand-embellishment, cutdana details, wide palazzo.', 1, 110000),
(5,   3, 3, 'Forest Green',     'Tonal embroidery, khussa shoes to be coordinated.',              1, 140000),
(6,  12, 9, 'Pastel Peach',     'Mirror work on bodice, organza dupatta included.',               1,  85000),
(7,   4, 1, 'Midnight Blue',    'Slim fit, two-button single-breasted, peak lapel.',              1,  65000),
(8,  11, 5, 'Crimson and Gold', 'Full court train, jamawar skirt, net dupatta, gold border.',     1, 320000),
(9,   6, 6, 'White and Blue',   'Printed lawn three-piece set for Eid.',                          3,   8000),
(10, 16, 9, 'Emerald Green',    'Floor length, structured cape sleeve, invisible side zip.',      1, 175000),
(11,  6, 6, 'White Blue Sage',  'Five shirts: 2 white, 1 sky blue, 1 sage, 1 light pink.',       5,   9500),
(12, 19, 9, 'Ballet Pink',      'Tulle overlay on skirt, satin bodice with pearl buttons.',       1,  55000),
(13, 13, 3, 'Wine Red',         'Antique gold hand-woven border, unstitched blouse piece.',       1, 165000);
GO
CREATE TABLE newsletter_subscribers (
    subscriber_id INT          NOT NULL IDENTITY(1,1),
    email         VARCHAR(200) NOT NULL,
    subscribed_at DATETIME     NOT NULL DEFAULT GETDATE(),
    is_active     BIT          NOT NULL DEFAULT 1,
    CONSTRAINT pk_newsletter_subscribers PRIMARY KEY (subscriber_id),
    CONSTRAINT uq_subscriber_email       UNIQUE (email)
);
GO
INSERT INTO newsletter_subscribers (email) VALUES
('aanya.mehra@gmail.com'),
('zara.khan@hotmail.com'),
('sana.bukhari@gmail.com'),
('mariam.tahir@gmail.com'),
('hina.nawaz@gmail.com'),
('nadia.akhtar@gmail.com'),
('fareeha.aziz@gmail.com'),
('amna.zuberi@hotmail.com'),
('bilal.chaudhry@yahoo.com'),
('hamza.malik@outlook.com');
GO
CREATE TABLE admin_users (
    admin_id      INT          NOT NULL IDENTITY(1,1),
    full_name     VARCHAR(150) NOT NULL,
    email         VARCHAR(200) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(15)  NOT NULL DEFAULT 'staff',
    is_active     BIT          NOT NULL DEFAULT 1,
    last_login    DATETIME     NULL,
    created_at    DATETIME     NOT NULL DEFAULT GETDATE(),
    CONSTRAINT pk_admin_users  PRIMARY KEY (admin_id),
    CONSTRAINT uq_admin_email  UNIQUE (email),
    CONSTRAINT chk_admin_role  CHECK (role IN ('super_admin','manager','staff'))
);
GO
INSERT INTO admin_users (full_name, email, password_hash, role) VALUES
('Kamran Khalid', 'kamran@velvara.pk', '$2b$12$exampleHashA', 'super_admin'),
('Sara Noon',     'sara@velvara.pk',   '$2b$12$exampleHashB', 'manager'),
('Tariq Mahmood', 'tariq@velvara.pk',  '$2b$12$exampleHashC', 'staff');
GO
CREATE VIEW vw_products_full AS
SELECT
    p.product_id,
    h.house_name,
    h.house_slug,
    c.category_name,
    c.category_slug,
    p.product_name,
    p.short_desc,
    p.price_min,
    p.price_max,
    p.currency,
    p.lead_time_weeks,
    p.is_featured,
    p.is_active,
    i.image_url AS primary_image
FROM products p
JOIN      houses         h ON h.house_id    = p.house_id
JOIN      categories     c ON c.category_id = p.category_id
LEFT JOIN product_images i ON i.product_id  = p.product_id AND i.is_primary = 1;
GO
CREATE VIEW vw_appointments AS
SELECT
    a.appointment_id,
    CONCAT(a.first_name, ' ', a.last_name) AS customer_name,
    a.email,
    a.phone,
    h.house_name,
    a.preferred_date,
    a.occasion_notes,
    a.status,
    a.source_page,
    a.created_at
FROM appointment_requests a
LEFT JOIN houses h ON h.house_id = a.house_id;
GO
CREATE VIEW vw_orders_summary AS
SELECT
    o.order_id,
    CONCAT(cu.first_name, ' ', cu.last_name) AS customer_name,
    cu.email,
    cu.phone,
    h.house_name,
    o.order_status,
    o.order_date,
    o.expected_ready,
    o.actual_ready,
    o.total_amount,
    o.currency,
    o.payment_status
FROM orders o
JOIN      customers cu ON cu.customer_id = o.customer_id
LEFT JOIN houses    h  ON h.house_id     = o.house_id;
GO