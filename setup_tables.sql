-- Setup NGWebD ERP development database tables
-- Run these commands manually or via spark migrate

-- Create employees table
CREATE TABLE IF NOT EXISTS employees (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned,
    employee_id varchar(20) UNIQUE,
    department_id int unsigned,
    designation_id int unsigned,
    employment_type enum('permanent','full_time','part_time','contract','probation','internship') default 'permanent',
    employment_status enum('draft','active','on_leave','probation','notice_period','resigned','terminated','inactive') default 'draft',
    joining_date DATE,
    probation_end_date DATE,
    termination_date DATE,
    last_working_date DATE,
    profile_photo varchar(255),
    emergency_contact_name varchar(100),
    emergency_contact_phone varchar(20),
    emergency_contact_relation varchar(50),
    address_line1 varchar(255),
    address_line2 varchar(255),
    city varchar(100),
    state varchar(100),
    pincode varchar(10),
    country varchar(100) default 'India',
    blood_group varchar(5),
    marital_status varchar(50),
    nationality varchar(50) default 'Indian',
    pan_number varchar(15),
    aadhar_number varchar(20) unique,
    bank_name varchar(150),
    bank_account_number varchar(50),
    bank_ifsc varchar(15),
    designation_at_joining varchar(100),
    department_at_joining varchar(100),
    resignation_reason text,
    notes text,
    created_by int unsigned,
    created_at datetime,
    updated_at datetime,
    PRIMARY KEY (id),
    KEY idx_employees_user_id (user_id),
    KEY idx_employees_department_id (department_id),
    KEY idx_employees_designation_id (designation_id),
    KEY idx_employees_employment_status (employment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create employee_departments table
CREATE TABLE IF NOT EXISTS employee_departments (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    client_id int unsigned,
    name varchar(100),
    code varchar(20),
    head_id int unsigned,
    description text,
    status enum('active','inactive') default 'active',
    sort_order int default 0,
    created_by int unsigned,
    created_at datetime,
    updated_at datetime,
    PRIMARY KEY (id),
    KEY idx_employee_departments_client_id (client_id),
    KEY idx_employee_departments_head_id (head_id),
    KEY idx_employee_departments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create employee_designations table
CREATE TABLE IF NOT EXISTS employee_designations (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    client_id int unsigned,
    title varchar(100),
    code varchar(20),
    department_id int unsigned,
    reporting_manager_title varchar(100),
    employment_category varchar(50),
    status enum('active','inactive') default 'active',
    sort_order int default 0,
    created_by int unsigned,
    created_at datetime,
    updated_at datetime,
    PRIMARY KEY (id),
    KEY idx_employee_designations_client_id (client_id),
    KEY idx_employee_designations_department_id (department_id),
    KEY idx_employee_designations_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create internship_programs table
CREATE TABLE IF NOT EXISTS internship_programs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    title varchar(200),
    description LONGTEXT,
    duration_months int,
    start_date DATE,
    end_date DATE,
    mode enum('remote','hybrid','office') default 'remote',
    weekly_hours int,
    compensation_category varchar(100),
    eligibility_criteria text,
    batch_size_min int default 3,
    batch_size_max int default 5,
    application_deadline DATE,
    status enum('draft','active','closed','completed') default 'active',
    public_application_url varchar(255),
    created_by int unsigned,
    created_at datetime,
    updated_at datetime,
    PRIMARY KEY (id),
    KEY idx_internship_programs_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create internship_positions table
CREATE TABLE IF NOT EXISTS internship_positions (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    program_id int unsigned,
    title varchar(200),
    description text,
    required_skills text,
    education_requirement varchar(100),
    experience_years decimal(3,1) default 0,
    stipend_category varchar(50),
    working_hours_per_week int,
    location varchar(200),
    status enum('open','closed','filled') default 'open',
    created_at datetime,
    updated_at datetime,
    PRIMARY KEY (id),
    KEY idx_internship_positions_program_id (program_id),
    KEY idx_internship_positions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create application_status_history table
CREATE TABLE IF NOT EXISTS application_status_history (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    application_id bigint unsigned,
    previous_status enum('applied','under_review','shortlisted','assessment_scheduled','assessment_completed','interview_scheduled','interview_completed','selected','offer_sent','offer_accepted','onboarding','joined','rejected','withdrawn','on_hold'),
    new_status enum('applied','under_review','shortlisted','assessment_scheduled,'assessment_completed,'interview_scheduled,'interview_completed,'selected,'offer_sent,'offer_accepted,'onboarding,'joined,'rejected,'withdrawn,'on_hold') not null,
    changed_by int unsigned,
    changed_by_name varchar(100),
    change_reason varchar(100),
    notes text,
    changed_at datetime,
    PRIMARY KEY (id),
    KEY idx_application_status_history_application_id (application_id),
    KEY idx_application_status_history_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;