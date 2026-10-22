
# CAE-CDTrack
### A Document Tracking and Management System for UM CAE Dean's Office Counter

CAE-CDTrack is a web-based document tracking and management system developed for the **College of Accounting Education (CAE) Dean's Office Counter** at the **University of Mindanao – Matina Campus**.

The system aims to improve how document requests are registered, tracked, routed, filed, and released by providing a centralized platform for requestors, Student Training Assistants (STAs), and authorized administrative personnel.

> **Project Scope:** CAE-CDTrack focuses specifically on document tracking and management at the CAE Dean's Office Counter. It does not cover the entire Dean's Office or replace the physical handling of documents.

---

## Project Overview

The existing document management process at the CAE Dean's Office Counter involves handwritten claim stubs, manually assigned tracking codes, physical filing, and repeated status inquiries.

CAE-CDTrack addresses these challenges by introducing digital request registration, automated tracking references, structured data entry, document status monitoring, visual drawer mapping, and transaction history.

The system follows four main stages:

**Register → Process → Locate → Release**

---

## Problems Identified

1. **Manual Document Intake** – Handwritten claim stubs and manually cut tracking codes.
2. **Inconsistent Request Information** – Unclear or incomplete handwritten document details.
3. **Filing and Retrieval Challenges** – Manual searching within the existing 20-drawer filing cabinet.
4. **Status Monitoring and Release** – Repeated inquiries, manual record lookups, and document release checks.

---

## Project Objectives

CAE-CDTrack aims to:

1. Generate unique tracking references and digital claim stubs for document requests.
2. Provide structured document type, faculty, and purpose fields.
3. Support physical document filing through visual drawer mapping for the existing 20-drawer cabinet.
4. Provide email notifications for document status changes when properly configured.
5. Support document lookup, status monitoring, and release history.

---

## Key Features

### Requestor Interface
- Document request submission
- Structured request forms
- Tracking reference generation
- Digital claim stub
- Document status monitoring
- Request history

### STA Counter Interface
- Request management
- Document status updates
- Faculty routing information
- Visual drawer mapping (Drawers 1–20)
- Document filing records
- Claiming and release records

### Administrator Interface
- Administrative dashboard
- User account management
- Faculty list management
- Document type management
- Transaction history
- Audit and activity records
- Monitoring and reporting features

### Additional Features
- Role-based interfaces and access
- Centralized document tracking records
- Email notifications (subject to SMTP configuration)
- Feedback management

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| Back-end Framework | Laravel |
| Programming Language | PHP |
| Front-end Templates | Laravel Blade |
| UI Styling | Tailwind CSS |
| Interactive Components | Livewire |
| Database | MySQL-compatible relational database |
| Application Type | Web-based |

---

## System Workflow

### 1. Register
The requestor submits a document request using structured fields. The system records the request and generates a tracking reference.

### 2. Process
The STA reviews the request, updates its status, and records the relevant faculty routing information.

### 3. Locate
The STA uses the drawer mapping feature to record or identify the location of the physical document in the existing 20-drawer filing cabinet.

### 4. Release
Once the physical document is ready and claimed, the STA records its release, maintaining a transaction history.

---


## System Users

| User Role | Main Responsibilities |
|-----------|-----------------------|
| Requestor | Submit requests and monitor document status |
| STA | Manage requests, update statuses, record routing, filing, and release |
| Administrator | Manage users, records, catalogs, and administrative functions |

---


## Project Limitations

- The system focuses only on document tracking and management at the CAE Dean's Office Counter.
- Physical document handling, faculty checking, signing, and filing remain manual.
- Accurate status and drawer-location information depend on authorized personnel maintaining the records.
- Email notification delivery requires proper configuration.
- Online availability depends on hosting and internet connectivity.
- Official institutional adoption requires approval from the University of Mindanao.

---

## Future Enhancements

- Performance optimization for older office computers
- Enhanced reporting and dashboard analytics
- Additional notification options
- Regular database backup and maintenance procedures
- Improved user orientation and administrative management

---

## Project Developers

- **Jannelle Jean R. Dingal** (Assistant)
- **Rumel Francis M. Siano** (Main Programmer)

---

## Academic Purpose

CAE-CDTrack was developed as an academic project to demonstrate the application of web development, database management, and information systems concepts to a real-world document tracking workflow.

The system is intended to support the CAE Dean's Office Counter and is not presented as an officially adopted University of Mindanao system.

---

**CAE-CDTrack — From Paper Claim Stubs to Organized Document Tracking.**
