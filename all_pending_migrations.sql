-- -----------------------------------------------------------------------------
-- 1. Expand User Roles ENUM (add HRMS roles)
-- -----------------------------------------------------------------------------
ALTER TABLE users MODIFY COLUMN role ENUM(
    'superadmin','client','hr_manager','recruitment_manager',
    'project_manager','team_leader','permanent_employee','intern','finance_manager'
) DEFAULT 'client';

-- -----------------------------------------------------------------------------
-- 2. Create Employees Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED,
    employee_id VARCHAR(20) UNIQUE,
    department_id INT UNSIGNED,
    designation_id INT UNSIGNED,
    employment_type ENUM('permanent','full_time','part_time','contract','probation','internship') DEFAULT 'permanent',
    employment_status ENUM('draft','active','on_leave','probation','notice_period','resigned','terminated','inactive') DEFAULT 'draft',
    joining_date DATE,
    probation_end_date DATE,
    termination_date DATE,
    last_working_date DATE,
    profile_photo VARCHAR(255),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relation VARCHAR(50),
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    country VARCHAR(100) DEFAULT 'India',
    blood_group VARCHAR(5),
    marital_status VARCHAR(50),
    nationality VARCHAR(50) DEFAULT 'Indian',
    pan_number VARCHAR(15),
    aadhar_number VARCHAR(20) UNIQUE,
    bank_name VARCHAR(150),
    bank_account_number VARCHAR(50),
    bank_ifsc VARCHAR(15),
    designation_at_joining VARCHAR(100),
    department_at_joining VARCHAR(100),
    resignation_reason TEXT,
    notes TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_employees_user_id (user_id),
    KEY idx_employees_department_id (department_id),
    KEY idx_employees_designation_id (designation_id),
    KEY idx_employees_employment_status (employment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 3. Create Employee Departments Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employee_departments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED,
    name VARCHAR(100),
    code VARCHAR(20),
    head_id INT UNSIGNED,
    description TEXT,
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_employee_departments_client_id (client_id),
    KEY idx_employee_departments_head_id (head_id),
    KEY idx_employee_departments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 4. Create Employee Designations Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employee_designations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED,
    title VARCHAR(100),
    code VARCHAR(20),
    department_id INT UNSIGNED,
    reporting_manager_title VARCHAR(100),
    employment_category VARCHAR(50),
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_employee_designations_client_id (client_id),
    KEY idx_employee_designations_department_id (department_id),
    KEY idx_employee_designations_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Foreign key to clients removed: referential integrity handled by application layer

-- -----------------------------------------------------------------------------
-- 5. Create Internship Programmes Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS internship_programs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200),
    description LONGTEXT,
    duration_months INT,
    start_date DATE,
    end_date DATE,
    mode ENUM('remote','hybrid','office') DEFAULT 'remote',
    weekly_hours INT,
    compensation_category VARCHAR(100),
    eligibility_criteria TEXT,
    batch_size_min INT DEFAULT 3,
    batch_size_max INT DEFAULT 5,
    application_deadline DATE,
    status ENUM('draft','active','closed','completed') DEFAULT 'active',
    public_application_url VARCHAR(255),
    created_by INT UNSIGNED,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_internship_programs_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 6. Create Internship Positions Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS internship_positions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    program_id INT UNSIGNED,
    title VARCHAR(200),
    description TEXT,
    required_skills TEXT,
    education_requirement VARCHAR(100),
    experience_years DECIMAL(3,1) DEFAULT 0,
    stipend_category VARCHAR(50),
    working_hours_per_week INT,
    location VARCHAR(200),
    status ENUM('open','closed','filled') DEFAULT 'open',
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_internship_positions_program_id (program_id),
    KEY idx_internship_positions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 7. Create Internship Applications Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS internship_applications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    program_id INT UNSIGNED,
    applicant_name VARCHAR(100),
    applicant_email VARCHAR(150),
    applicant_mobile VARCHAR(20),
    applicant_city_state VARCHAR(150),
    education_qualification VARCHAR(100),
    college_university VARCHAR(200),
    graduation_year YEAR,
    preferred_role VARCHAR(100),
    technical_skills TEXT,
    github_profile VARCHAR(255),
    portfolio_url VARCHAR(255),
    resume_file_path VARCHAR(500),
    profile_photograph VARCHAR(255),
    laptop_available TINYINT DEFAULT 0,
    internet_available TINYINT DEFAULT 1,
    weekly_availability VARCHAR(50),
    motivation_statement LONGTEXT,
    consent_privacy TINYINT DEFAULT 1,
    applied_at DATETIME,
    status ENUM(
        'applied','under_review','shortlisted','assessment_scheduled',
        'assessment_completed','interview_scheduled','interview_completed',
        'selected','offer_sent','offer_accepted','onboarding','joined',
        'rejected','withdrawn','on_hold'
    ) DEFAULT 'applied',
    reference_number VARCHAR(20) UNIQUE,
    source VARCHAR(50),
    notes TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_internship_applications_program_id (program_id),
    KEY idx_internship_applications_status (status),
    KEY idx_internship_applications_applicant_email (applicant_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 8. Create Application Status History Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS application_status_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED,
    previous_status ENUM(
        'applied','under_review','shortlisted','assessment_scheduled',
        'assessment_completed','interview_scheduled','interview_completed',
        'selected','offer_sent','offer_accepted','onboarding','joined',
        'rejected','withdrawn','on_hold'
    ),
    new_status ENUM(
        'applied','under_review','shortlisted','assessment_scheduled',
        'assessment_completed','interview_scheduled','interview_completed',
        'selected','offer_sent','offer_accepted','onboarding','joined',
        'rejected','withdrawn','on_hold'
    ) NOT NULL,
    changed_by INT UNSIGNED,
    changed_by_name VARCHAR(100),
    change_reason VARCHAR(100),
    notes TEXT,
    changed_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_application_status_history_application_id (application_id),
    KEY idx_application_status_history_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 9. Create Candidate Assessments Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS candidate_assessments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED,
    technical_knowledge DECIMAL(3,1),
    problem_solving DECIMAL(3,1),
    communication DECIMAL(3,1),
    learning_attitude DECIMAL(3,1),
    logical_thinking DECIMAL(3,1),
    availability DECIMAL(3,1),
    overall_score DECIMAL(3,1),
    strengths TEXT,
    improvement_areas TEXT,
    recommendation ENUM('strongly_recommended','recommended','hold','not_recommended'),
    assessed_by INT UNSIGNED,
    assessed_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_candidate_assessments_application_id (application_id),
    KEY idx_candidate_assessments_recommendation (recommendation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 10. Create Interviews Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS interviews (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED,
    interview_round INT DEFAULT 1,
    interviewer_id INT UNSIGNED,
    interviewer_name VARCHAR(100),
    interview_date DATE,
    start_time TIME,
    end_time TIME,
    timezone VARCHAR(50) DEFAULT 'UTC',
    interview_type ENUM('technical','hr','culture_add','technical_pair','panel') DEFAULT 'technical',
    meeting_link VARCHAR(500),
    instructions TEXT,
    internal_notes TEXT,
    interview_status ENUM('scheduled','completed','cancelled','rescheduled') DEFAULT 'scheduled',
    evaluation_score DECIMAL(3,1),
    final_recommendation ENUM('strongly_recommended','recommended','hold','not_recommended'),
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_interviews_application_id (application_id),
    KEY idx_interviews_interview_date (interview_date),
    KEY idx_interviews_interview_status (interview_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 11. Create Interview Feedback Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS interview_feedback (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    interview_id BIGINT UNSIGNED,
    reviewer_id INT UNSIGNED,
    reviewer_name VARCHAR(100),
    technical_knowledge DECIMAL(3,1),
    problem_solving DECIMAL(3,1),
    communication DECIMAL(3,1),
    learning_attitude DECIMAL(3,1),
    logical_thinking DECIMAL(3,1),
    availability DECIMAL(3,1),
    overall_score DECIMAL(3,1),
    strengths TEXT,
    improvement_areas TEXT,
    recommendation ENUM('strongly_recommended','recommended','hold','not_recommended'),
    created_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_interview_feedback_interview_id (interview_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 12. Create Offer Letters Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS offer_letters (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED,
    candidate_name VARCHAR(100),
    candidate_address TEXT,
    position VARCHAR(100),
    program_name VARCHAR(200),
    duration VARCHAR(100),
    start_date DATE,
    end_date DATE,
    work_mode ENUM('remote','hybrid','office') DEFAULT 'remote',
    weekly_commitment VARCHAR(50),
    compensation_disclosure TEXT,
    learning_objectives LONGTEXT,
    terms_conditions LONGTEXT,
    authorized_signatory VARCHAR(100),
    company_branding TEXT,
    offer_reference VARCHAR(50),
    issue_date DATE,
    acceptance_deadline DATE,
    status ENUM('draft','issued','accepted','rejected','joined') DEFAULT 'draft',
    pdf_path VARCHAR(500),
    joined_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_offer_letters_application_id (application_id),
    KEY idx_offer_letters_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 13. Create Offer Acceptances Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS offer_acceptances (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    offer_letter_id BIGINT UNSIGNED,
    candidate_name VARCHAR(100),
    candidate_id INT UNSIGNED,
    accepted_at DATETIME,
    acceptance_type ENUM('accepted','rejected'),
    response_notes TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_offer_acceptances_offer_letter_id (offer_letter_id),
    KEY idx_offer_acceptances_accepted_at (accepted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 14. Create Onboarding Checklists Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS onboarding_checklists (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id INT UNSIGNED,
    project_id INT UNSIGNED,
    user_id INT UNSIGNED,
    checklist_item VARCHAR(200) NOT NULL,
    is_completed TINYINT DEFAULT 0,
    completed_at DATETIME,
    completed_by INT UNSIGNED,
    order_index INT DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_onboarding_checklists_employee_id (employee_id),
    KEY idx_onboarding_checklists_project_id (project_id),
    KEY idx_onboarding_checklists_is_completed (is_completed),
    KEY idx_onboarding_checklists_order_index (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 15. Create Onboarding Documents Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS onboarding_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id INT UNSIGNED,
    document_name VARCHAR(200),
    file_path VARCHAR(500),
    file_type VARCHAR(100),
    document_category VARCHAR(50),
    uploaded_at DATETIME,
    uploaded_by INT UNSIGNED,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    PRIMARY KEY (id),
    KEY idx_onboarding_documents_employee_id (employee_id),
    KEY idx_onboarding_documents_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 16. Create Payroll Periods Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payroll_periods (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50),
    period VARCHAR(20),          -- e.g., 'January 2024'
    start_date DATE,
    end_date DATE,
    status ENUM('draft','processing','finalized','paid') DEFAULT 'draft',
    processed_by INT UNSIGNED,
    processed_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 17. Create Payroll Components Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payroll_components (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    component_name VARCHAR(100),        -- Basic, HRA, Conveyance, etc.
    component_type ENUM('earning','deduction') DEFAULT 'earning',
    is_statutory TINYINT DEFAULT 0,
    configurable_rate DECIMAL(5,2) DEFAULT 0,
    default_rate DECIMAL(5,2) DEFAULT 0,
    applicable_threshold DECIMAL(10,2),
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 18. Create Employee Salary Components Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employee_salary_components (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id INT UNSIGNED,
    payroll_period_id INT UNSIGNED,
    component_id INT UNSIGNED,
    value DECIMAL(10,2),
    effective_from DATE,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_employee_salary_components_employee_id (employee_id),
    KEY idx_employee_salary_components_payroll_period_id (payroll_period_id),
    KEY idx_employee_salary_components_component_id (component_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 19. Create Payroll Records Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payroll_records (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payroll_period_id INT UNSIGNED,
    employee_id INT UNSIGNED,
    gross_salary DECIMAL(10,2),
    net_salary DECIMAL(10,2),
    gross_earnings DECIMAL(10,2),
    net_deductions DECIMAL(10,2),
    payment_date DATE,
    payment_method VARCHAR(50),      -- bank_transfer, check, cash, etc.
    payment_reference VARCHAR(100),
    status ENUM('pending','processing','paid','failed') DEFAULT 'pending',
    approved_by INT UNSIGNED,
    approved_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_payroll_records_employee_id (employee_id),
    KEY idx_payroll_records_payroll_period_id (payroll_period_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 20. Create Payslips Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payslips (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payroll_record_id INT UNSIGNED,
    employee_id INT UNSIGNED,
    payroll_period_id INT UNSIGNED,
    employee_name VARCHAR(100),
    employee_id_display VARCHAR(20),
    designation VARCHAR(100),
    department VARCHAR(100),
    salary_month VARCHAR(20),          -- e.g., 'January 2024'
    paid_days DECIMAL(5,2),
    basic_salary DECIMAL(10,2),
    hra DECIMAL(10,2),
    conveyance DECIMAL(10,2),
    special_allowance DECIMAL(10,2),
    other_allowances DECIMAL(10,2),
    incentives DECIMAL(10,2),
    bonus DECIMAL(10,2),
    gross_earnings DECIMAL(10,2),
    pf DECIMAL(10,2),
    ESI DECIMAL(10,2),
    professional_tax DECIMAL(10,2),
    tds DECIMAL(10,2),
    other_deductions DECIMAL(10,2),
    leave_with_pay DECIMAL(10,2),
    gross_deductions DECIMAL(10,2),
    net_salary DECIMAL(10,2),
    payment_date DATE,
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    status ENUM('draft','generated','approved','paid') DEFAULT 'draft',
    generated_at DATETIME,
    approved_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_payslips_employee_id (employee_id),
    KEY idx_payslips_payroll_period_id (payroll_period_id),
    KEY idx_payslips_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 20. Create Payroll Approvals Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payroll_approvals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payroll_record_id INT UNSIGNED,
    employee_id INT UNSIGNED,
    approver_id INT UNSIGNED,
    approver_name VARCHAR(100),
    approved_at DATETIME,
    approval_type ENUM('initial','final') DEFAULT 'initial',
    approval_status ENUM('pending','approved','rejected') DEFAULT 'pending',
    approval_notes TEXT,
    created_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_payroll_approvals_payroll_record_id (payroll_record_id),
    KEY idx_payroll_approvals_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 21. Create Salary Payments Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS salary_payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payroll_record_id INT UNSIGNED,
    employee_id INT UNSIGNED,
    payment_date DATE,
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    amount DECIMAL(10,2),
    status ENUM('pending','processing','completed','failed','refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_salary_payments_employee_id (employee_id),
    KEY idx_salary_payments_payroll_record_id (payroll_record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 22. Create Attendance Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED,
    check_in DATETIME,
    check_out DATETIME,
    work_from_home TINYINT DEFAULT 0,
    work_from_home_notes TEXT,
    attendance_date DATE NOT NULL,
    day_type ENUM('working','holiday','weekly_off') DEFAULT 'working',
    status ENUM('present','absent','half_day','leave','work_from_home') DEFAULT 'present',
    hours_worked DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    UNIQUE KEY uq_employee_date (employee_id, attendance_date),
    KEY idx_attendance_employee_id (employee_id),
    KEY idx_attendance_date (attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 22. Create Certificates Table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS certificates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    internship_application_id BIGINT UNSIGNED,
    certificate_number VARCHAR(20) UNIQUE,
    student_name VARCHAR(100),
    internship_program VARCHAR(200),
    duration_months INT,
    final_evaluation DECIMAL(3,1),
    mentor_approval TINYINT DEFAULT 0,
    hr_approval TINYINT DEFAULT 0,
    issued_at DATETIME,
    revoked TINYINT DEFAULT 0,
    revoked_at DATETIME,
    revocation_reason TEXT,
    verification_hash VARCHAR(64),
    qr_code_path VARCHAR(500),
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_certificates_internship_application_id (internship_application_id),
    KEY idx_certificates_certificate_number (certificate_number),
    KEY idx_certificates_verification_hash (verification_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 23. Create Checklists Table (for Task checklists)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS checklists (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    task_id BIGINT UNSIGNED,
    item_text VARCHAR(200) NOT NULL,
    completed TINYINT DEFAULT 0,
    completed_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY (id),
    KEY idx_checklists_task_id (task_id),
    KEY idx_checklists_completed (completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

