CREATE DATABASE IF NOT EXISTS i_can_hear_you_full
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE i_can_hear_you_full;


-- =====================================================
-- ROLES
-- =====================================================

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO roles (name, description) VALUES
('admin', 'System administrator'),
('counsellor', 'Counsellor or mentor'),
('client', 'Client receiving support'),
('coordinator', 'Program coordinator'),
('supervisor', 'Counsellor supervisor');


-- =====================================================
-- USERS
-- =====================================================

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,

    password VARCHAR(255) NOT NULL,

    status ENUM(
        'active',
        'pending',
        'inactive',
        'suspended'
    ) DEFAULT 'active',

    email_verified_at TIMESTAMP NULL,

    last_login_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


-- =====================================================
-- CLIENT PROFILES
-- =====================================================

CREATE TABLE client_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL UNIQUE,

    client_type ENUM(
        'youth',
        'parent',
        'teacher',
        'organization_rep'
    ) NOT NULL,

    preferred_language ENUM(
        'english',
        'kinyarwanda',
        'french'
    ) DEFAULT 'english',

    display_name VARCHAR(100) NULL,

    wellness_information TEXT NULL,

    consent_status ENUM(
        'pending',
        'accepted',
        'withdrawn'
    ) DEFAULT 'pending',

    anonymous_profile BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_client_profile_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- =====================================================
-- COUNSELLOR PROFILES
-- =====================================================

CREATE TABLE counselor_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL UNIQUE,

    qualification VARCHAR(255) NULL,

    specialization VARCHAR(255) NULL,

    years_experience INT DEFAULT 0,

    languages VARCHAR(255) NULL,

    bio TEXT NULL,

    availability VARCHAR(255) NULL,

    status ENUM(
        'active',
        'pending',
        'inactive'
    ) DEFAULT 'pending',

    session_types VARCHAR(100) DEFAULT 'video,chat',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_counselor_profile_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- =====================================================
-- SERVICES
-- =====================================================

CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    description TEXT NULL,

    service_type ENUM(
        'counselling',
        'mentorship',
        'wellness',
        'career',
        'family',
        'youth'
    ) DEFAULT 'counselling',

    duration_minutes INT DEFAULT 60,

    price DECIMAL(10,2) DEFAULT 0.00,

    status ENUM(
        'active',
        'inactive'
    ) DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


INSERT INTO services
(name, description, service_type, duration_minutes, price)
VALUES
(
    'Individual Counselling',
    'Private one-to-one counselling session.',
    'counselling',
    60,
    0.00
),
(
    'Youth Counselling',
    'Counselling support for young people.',
    'youth',
    60,
    0.00
),
(
    'Family Counselling',
    'Counselling support for families.',
    'family',
    60,
    0.00
),
(
    'Career Mentorship',
    'Career guidance and mentorship.',
    'career',
    60,
    0.00
);


-- =====================================================
-- BOOKINGS
-- =====================================================

CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    client_id INT UNSIGNED NOT NULL,

    counselor_id INT UNSIGNED NOT NULL,

    service_id INT UNSIGNED NOT NULL,

    session_type ENUM(
        'video',
        'chat'
    ) NOT NULL,

    booking_date DATE NOT NULL,

    start_time TIME NOT NULL,

    end_time TIME NOT NULL,

    reason TEXT NULL,

    status ENUM(
        'pending',
        'confirmed',
        'completed',
        'cancelled',
        'declined'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_booking_client
        FOREIGN KEY (client_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_booking_counselor
        FOREIGN KEY (counselor_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_booking_service
        FOREIGN KEY (service_id)
        REFERENCES services(id)
        ON DELETE RESTRICT
);


-- =====================================================
-- SESSIONS
-- =====================================================

CREATE TABLE sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    booking_id INT UNSIGNED NOT NULL UNIQUE,

    session_type ENUM(
        'video',
        'chat'
    ) NOT NULL,

    meeting_type ENUM(
        'video_call',
        'chat_room'
    ) NOT NULL,

    meeting_link VARCHAR(500) NULL,

    status ENUM(
        'scheduled',
        'active',
        'completed',
        'cancelled'
    ) DEFAULT 'scheduled',

    started_at TIMESTAMP NULL,

    ended_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_session_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(id)
        ON DELETE CASCADE
);


-- =====================================================
-- CONVERSATIONS
-- =====================================================

CREATE TABLE conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    client_id INT UNSIGNED NOT NULL,

    counselor_id INT UNSIGNED NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_conversation_client
        FOREIGN KEY (client_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_conversation_counselor
        FOREIGN KEY (counselor_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =====================================================
-- MESSAGES
-- =====================================================

CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    conversation_id INT UNSIGNED NOT NULL,

    sender_id INT UNSIGNED NOT NULL,

    receiver_id INT UNSIGNED NOT NULL,

    message TEXT NOT NULL,

    attachment_path VARCHAR(500) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    read_at TIMESTAMP NULL,

    CONSTRAINT fk_message_conversation
        FOREIGN KEY (conversation_id)
        REFERENCES conversations(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_message_sender
        FOREIGN KEY (sender_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_message_receiver
        FOREIGN KEY (receiver_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =====================================================
-- SESSION NOTES
-- =====================================================

CREATE TABLE session_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    session_id INT UNSIGNED NOT NULL,

    counselor_id INT UNSIGNED NOT NULL,

    discussion TEXT NULL,

    tools_used TEXT NULL,

    progress TEXT NULL,

    next_steps TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_note_session
        FOREIGN KEY (session_id)
        REFERENCES sessions(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_note_counselor
        FOREIGN KEY (counselor_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =====================================================
-- RESOURCES
-- =====================================================

CREATE TABLE resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255) NOT NULL,

    description TEXT NULL,

    file_path VARCHAR(500) NULL,

    type ENUM(
        'article',
        'guide',
        'video',
        'exercise'
    ) NOT NULL,

    category VARCHAR(100) NULL,

    uploaded_by INT UNSIGNED NOT NULL,

    status ENUM(
        'published',
        'draft',
        'archived',
        'pending'
    ) DEFAULT 'draft',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_resource_uploader
        FOREIGN KEY (uploaded_by)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =====================================================
-- FEEDBACK
-- =====================================================

CREATE TABLE feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    session_id INT UNSIGNED NOT NULL,

    client_id INT UNSIGNED NOT NULL,

    rating TINYINT UNSIGNED NOT NULL,

    comment TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_feedback_rating
        CHECK (rating BETWEEN 1 AND 5),

    CONSTRAINT fk_feedback_session
        FOREIGN KEY (session_id)
        REFERENCES sessions(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_feedback_client
        FOREIGN KEY (client_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =====================================================
-- PROGRAMS
-- =====================================================

CREATE TABLE programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(255) NOT NULL,

    program_type VARCHAR(100) NULL,

    description TEXT NULL,

    start_date DATE NULL,

    end_date DATE NULL,

    status ENUM(
        'upcoming',
        'active',
        'completed',
        'archived'
    ) DEFAULT 'upcoming',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =====================================================
-- ORGANIZATIONS
-- =====================================================

CREATE TABLE organizations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(255) NOT NULL,

    type VARCHAR(100) NULL,

    location VARCHAR(255) NULL,

    contact_person VARCHAR(150) NULL,

    phone VARCHAR(30) NULL,

    email VARCHAR(150) NULL,

    website VARCHAR(255) NULL,

    contact_information TEXT NULL,

    status ENUM(
        'active',
        'pending',
        'inactive'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =====================================================
-- SCHOOLS
-- =====================================================

CREATE TABLE schools (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(255) NOT NULL,

    type VARCHAR(100) NULL,

    location VARCHAR(255) NULL,

    contact_person VARCHAR(150) NULL,

    phone VARCHAR(30) NULL,

    email VARCHAR(150) NULL,

    student_count INT DEFAULT 0,

    status ENUM(
        'active',
        'pending',
        'inactive'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =====================================================
-- SCHOOL PROGRAMS
-- =====================================================

CREATE TABLE school_programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    school_id INT UNSIGNED NOT NULL,

    program_id INT UNSIGNED NOT NULL,

    start_date DATE NULL,

    end_date DATE NULL,

    status ENUM(
        'active',
        'completed',
        'cancelled'
    ) DEFAULT 'active',

    CONSTRAINT fk_school_program_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_school_program_program
        FOREIGN KEY (program_id)
        REFERENCES programs(id)
        ON DELETE CASCADE
);


-- =====================================================
-- ORGANIZATION PROGRAMS
-- =====================================================

CREATE TABLE organization_programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    organization_id INT UNSIGNED NOT NULL,

    program_id INT UNSIGNED NOT NULL,

    start_date DATE NULL,

    end_date DATE NULL,

    status ENUM(
        'active',
        'completed',
        'cancelled'
    ) DEFAULT 'active',

    CONSTRAINT fk_org_program_org
        FOREIGN KEY (organization_id)
        REFERENCES organizations(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_org_program_program
        FOREIGN KEY (program_id)
        REFERENCES programs(id)
        ON DELETE CASCADE
);


-- =====================================================
-- NOTIFICATIONS
-- =====================================================

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    title VARCHAR(255) NOT NULL,

    message TEXT NOT NULL,

    type ENUM(
        'booking',
        'message',
        'system',
        'announcement',
        'security'
    ) DEFAULT 'system',

    audience VARCHAR(50) NULL,

    status ENUM(
        'sent',
        'scheduled',
        'draft'
    ) DEFAULT 'sent',

    scheduled_at TIMESTAMP NULL,

    read_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notification_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =====================================================
-- AUDIT LOGS
-- =====================================================

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NULL,

    action VARCHAR(255) NOT NULL,

    ip_address VARCHAR(45) NULL,

    user_agent TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
);


-- =====================================================
-- SETTINGS
-- =====================================================

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    setting_key VARCHAR(150) NOT NULL UNIQUE,

    setting_value TEXT NULL,

    setting_group VARCHAR(100) NOT NULL,

    updated_by INT UNSIGNED NULL,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_setting_user
        FOREIGN KEY (updated_by)
        REFERENCES users(id)
        ON DELETE SET NULL
);


-- =====================================================
-- INITIAL SETTINGS
-- =====================================================

INSERT INTO settings
(setting_key, setting_value, setting_group)
VALUES
('platform_name', 'I Can Hear You Rwanda', 'general'),
('support_email', 'support@icanhearyou.rw', 'general'),
('support_phone', '+250 788 000 000', 'general'),
('default_language', 'English', 'general'),
('timezone', 'Africa/Kigali', 'general'),
('date_format', 'DD/MM/YYYY', 'general'),
('maintenance_mode', '0', 'general'),
('allow_registration', '1', 'users'),
('require_email_verification', '1', 'users'),
('anonymous_profiles', '1', 'users'),
('counsellor_admin_approval', '1', 'users'),
('password_min_length', '8', 'security'),
('session_timeout', '30', 'security'),
('login_attempt_limit', '5', 'security'),
('admin_2fa', '1', 'security'),
('audit_logging', '1', 'security'),
('email_notifications', '1', 'notifications'),
('booking_notifications', '1', 'notifications'),
('message_notifications', '1', 'notifications'),
('session_reminders', '1', 'notifications'),
('default_session_duration', '60', 'sessions'),
('default_break_time', '15', 'sessions'),
('allow_video_sessions', '1', 'sessions'),
('allow_chat_sessions', '1', 'sessions'),
('auto_meeting_links', '1', 'sessions');


-- =====================================================
-- INDEXES
-- =====================================================

CREATE INDEX idx_users_role
ON users(role_id);

CREATE INDEX idx_users_status
ON users(status);

CREATE INDEX idx_bookings_client
ON bookings(client_id);

CREATE INDEX idx_bookings_counselor
ON bookings(counselor_id);

CREATE INDEX idx_bookings_date
ON bookings(booking_date);

CREATE INDEX idx_messages_conversation
ON messages(conversation_id);

CREATE INDEX idx_messages_sender
ON messages(sender_id);

CREATE INDEX idx_notifications_user
ON notifications(user_id);

CREATE INDEX idx_notifications_read
ON notifications(read_at);

CREATE INDEX idx_audit_user
ON audit_logs(user_id);

CREATE INDEX idx_audit_created
ON audit_logs(created_at);