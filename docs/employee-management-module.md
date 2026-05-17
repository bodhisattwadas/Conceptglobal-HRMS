# Employee Management Module - Horilla HRMS (OpenHRMS)

**Module Overview**  
The **Employee Management Module** (also referred to as the Employees app) serves as the central hub for managing the entire employee lifecycle in Horilla HRMS. It provides a comprehensive employee database, detailed individual profiles, self-service features, administrative tools, and integrations with other modules like Attendance, Leave, Payroll, Assets, Performance, and Offboarding.

<grok-card data-id="5b4617" data-type="image_card" data-plain-type="render_searched_image"  data-arg-image_id="fNDFH"  data-arg-size="LARGE" ></grok-card>



<grok-card data-id="946c22" data-type="image_card" data-plain-type="render_searched_image"  data-arg-image_id="BrwIJ"  data-arg-size="LARGE" ></grok-card>


Key capabilities include:
- Centralized employee records (personal, work, bank, contract details).
- Self-service portal for employees (requests, documents, profile views).
- Administrative tools for bulk operations, approvals, and reporting.
- Kanban/List/Card views with advanced filtering, grouping, and search.
- Smart buttons and quick links to related records (contracts, payslips, time off, etc.).

---

## 1. Employee Directory / List View

**Access**: Employees → Employees (main sidebar).

**Views Available**:
- **Card View** (default): Grid of employee cards with photo, name, job title, department, status (Online/Offline), and quick actions.

<grok-card data-id="c4f80a" data-type="image_card" data-plain-type="render_searched_image"  data-arg-image_id="fNDFH"  data-arg-size="LARGE" ></grok-card>

- **List/Table View**: Tabular format with customizable columns.

**Features**:
- Search bar + Filters + Group By.
- Quick filters: Online / Offline.
- Bulk Actions (via checkbox selection or "Select all"): Import/Export, Archive/Unarchive, Update, Delete.
- Per-employee actions: Send Mail, Edit, Archive/Unarchive, Delete.
- Create new employee via prominent **+ Create** button.

**Creating/Editing an Employee**:
- Basic info: Name, contact details, department, job position, manager, work email, etc.
- Additional tabs/sections: Personal, Work, Bank, Contract, etc.
- Smart buttons on employee form: Contracts, Time Off, Payslips, etc.

---

## 2. Detailed Employee Profile View

**Access**: Click any employee card/name from directory (or self-view for logged-in users).

**Customizable Tabs** (toggle visibility via 3-dot menu):

### 2.1 About
- Personal Information (name, email, phone, address, etc.).
- Work Information (job title, department, manager, company).
- Contract Details (type, start/end dates, salary structure).
- Bank Information (account details for payroll).

<grok-card data-id="7aedf4" data-type="image_card" data-plain-type="render_searched_image"  data-arg-image_id="xBEyp"  data-arg-size="LARGE" ></grok-card>


### 2.2 Work Type & Shift
- Current shift/work type.
- **Shift Requests**: Create requests for temporary or permanent changes (start/end date, description).
- **Rotating Shift/Work Type** history.
- **Reallocate Shift**: Request temporary swap with another employee (approval workflow involving both parties + admin).

### 2.3 Attendance
- Tabular view of all attendance records.
- Click row for detailed view.
- Overtime tracking.
- Attendance requests (corrections).

### 2.4 Leave
- Available leave balances by type.
- Leave request history with status.
- Create new leave request directly from profile.

### 2.5 Payroll
- Payslip list (click for details; PDF download).
- Allowances & Deductions (view/create if admin).
- Penalty Account (LOP days, penalties).

### 2.6 Assets
- Assigned assets list.
- Request new assets.
- Return assets.

### 2.7 Performance
- Feedback entries.
- Objectives/OKRs.
- 360-degree feedback participation.

### 2.8 Documents / Notes
- Upload personal documents.
- View admin-requested documents.
- Add notes.

### 2.9 Bonus Points
- View/redeem bonus points (converts to payslip amount).
- Admin can award points.

### 2.10 Resignation
- Submit resignation request (joining date, notice period, reason, type).
- Approval workflow.
- Approved resignations move to Offboarding.

### 2.11 Other Tabs
- History (audit trail of changes).
- Groups & Permissions (assign roles/CRUD access).
- Mail Log.
- Disciplinary Actions (if enabled).

---

## 3. Departments

**Access**: Employees → Departments.

- Kanban view of departments with stats (employees, time-off requests, absents).
- Create: Name, Parent Department, Manager, Company.
- Useful for hierarchical organization.

---

## 4. Contracts

**Access**: Employees → Contracts (or via smart button on employee).

- List of contracts with hire details, status.
- Default filter: Active Employees.
- Kanban view available.
- Detailed contract form per employee.

---

## 5. Document Requests (Admin/Manager)

**Access**: Employees → Document Requests.

- Create batch requests (title, employees, format, max size, description, expiry).
- Track upload status per employee.
- Approve/Reject uploaded documents.
- Preview, re-upload, expiry notifications.

---

## 6. Shift Requests

**Access**: Employees → Shift Requests.

- List of all shift change requests (own + subordinates for managers).
- Filters, bulk approve/reject.
- Create form: Employee, Shift, Dates, Permanent flag, Description.
- Comments thread.
- Allocated/Reallocation requests with availability responses.

---

## 7. Legal Actions

**Access**: Employees → Legal Actions.

- Manage legal cases involving employees, customers, suppliers.
- Create: Reference #, Party, Person, etc.
- States: Draft → Process → Won/Lost.

---

## 8. Resignation Management

- Employee self-request.
- Manager approval.
- Integration with Offboarding.

---

## 9. Organization Chart

- Visual hierarchy view.
- Filter by manager.

---

## 10. Additional Features & Integrations

- **Self-Service**: Employees see only their data + limited requests.
- **Permissions**: Granular per module (CRUD).
- **Notifications**: Email + in-app for approvals, requests, expiries.
- **Mobile-Friendly**: Responsive design.
- **Integrations**:
  - Attendance & Leave.
  - Payroll (payslips, allowances, penalties).
  - Assets & Performance.
  - Onboarding/Offboarding.
  - Recruitment (new hire flow).

**Best Practices**:
- Use bulk operations for efficiency.
- Enable history tracking for key fields in Settings.
- Regularly review document expiries and shift allocations.
- Leverage smart buttons for quick navigation across modules.

This module forms the foundation of Horilla HRMS, ensuring all employee data is accurate, accessible, and actionable while empowering both HR and employees through self-service features.