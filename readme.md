# Kedai Emas Web Application

## Project Overview

Create a web application for a gold shop (Kedai Emas).

## User Story v1

- User (Owner Kedai) wants to display gold prices dynamically and update automatically every few minutes
- User wants to add margin to gold prices
- User has their own formula for calculating gold prices. Refer to Bank Negara Malaysia
- User wants to display prices for agents and staff

## Development Plan

- CRUD functionality
- API calls
- Frontend: HTML, CSS, and Bootstrap
- Backend: PHP (MVC architecture)

## Features

- Separate dashboard components for Staff and Agent
- Gold calculator
- Authentication and Role Based Access Control (RBAC)
- Contact page (HTML)
- Live gold price display

## Action Development Plan

### Phase 1: Database Setup
- [✓] Draw SQL schema (drawsql.app/teams/wnh/diagrams/web-emas)
- [✓] Fix foreign key bug in draw-sql.sql line 39 (should reference roles.id not roles.name)
- [✓] Create database and run SQL script
- [✓] Create seed data script for initial roles (Admin, Staff, Agent) and profit_margin entries

### Phase 2: Backend Structure (PHP MVC)
- [ ] Set up folder structure:
  ```
  /app
    /models
    /views
    /controllers
    /config
    /middleware
    /services
  /public
    /css
    /js
    /images
  /vendor
  ```
- [✓] Create Database.php class with PDO connection
- [ ] Create base Model.php class for database operations
- [ ] Create base Controller.php class
- [ ] Create Router.php for URL routing
- [ ] Set up autoloading and configuration files

### Phase 3: Authentication & RBAC 
- [ ] Create User model with methods: create, read, update, delete, findByEmail
- [ ] Implement password hashing (password_hash/password_verify)
- [ ] Build AuthController with login, logout, register methods
- [ ] Implement session management for authenticated users
- [ ] Create RBAC middleware to check user roles
- [ ] Complete login.html form with validation
- [ ] Complete register.html form with validation
- [ ] Create logout functionality

### Phase 4: Gold Price API Integration
- [ ] Research Metal Price API documentation and get API key
- [ ] Create GoldPriceService.php for API calls
- [ ] Implement fetchCurrentGoldPrice() method
- [ ] Create conversion formula: Troy Ounce (31g) to RM/gram
- [ ] Build calculatePriceWithMargin() method based on user role:
  - Staff: +0.5%
  - Agent Tier A: +0.02%
  - Agent Tier B: +0.05%
  - Agent Tier C: +0.10%
  - Agent (general): +1%
  - Customer: +5%
- [ ] Create GoldPrice model to store historical prices
- [ ] Set up scheduled task (cron) for automatic price updates every few minutes
- [ ] Create API endpoint to fetch current gold prices for frontend

### Phase 5: Gold Calculator
- [ ] Create GoldCalculator.php service class
- [ ] Implement calculate() method with custom formula support
- [ ] Add calculator UI component to dashboard
- [ ] Create AJAX endpoint for calculator operations
- [ ] Add real-time calculation on frontend

### Phase 6: Dashboard Development
- [ ] Create DashboardController with index method
- [ ] Build separate dashboard views:
  - admin_dashboard.php
  - staff_dashboard.php
  - agent_dashboard.php
- [ ] Implement gold price table with live data from database
- [ ] Add "Perubahan Harga" (price change) widget with percentage calculation
- [ ] Add "Stock Count" widget pulling from product_data table
- [ ] Add "Harga Emas Semasa" (current gold price) widget
- [ ] Integrate Chart.js with real database data for 7-day price history
- [ ] Create order placement button and form modal
- [ ] Add role-based navigation (hide/show menu items based on user role)

### Phase 7: Product & Stock Management
- [ ] Create Product model with CRUD operations
- [ ] Build ProductController with methods: index, create, update, delete
- [ ] Create product management interface for admin
- [ ] Implement stock count tracking and updates
- [ ] Add low stock alerts

### Phase 8: Contact & Inbox
- [ ] Create contact.html page with Bootstrap form
- [ ] Create ContactSubmission model
- [ ] Build ContactController to handle form submissions
- [ ] Store submissions in contact_submission table with timestamp
- [ ] Create inbox page (admin only) to view all submissions
- [ ] Add unread message counter badge on navigation

### Phase 9: Testing & Polish
- [ ] Test all authentication flows (login, logout, register)
- [ ] Verify RBAC permissions for all roles
- [ ] Test gold price API integration and calculations
- [ ] Verify automatic price updates are working
- [ ] Test calculator with various scenarios
- [ ] Check responsive design on mobile, tablet, desktop
- [ ] Add proper error handling and validation messages
- [ ] Implement CSRF protection
- [ ] Add input sanitization for security

### Phase 10: Deployment
- [ ] Create .env file for configuration (database, API keys)
- [ ] Set up production database
- [ ] Configure web server (Apache/Nginx)
- [ ] Set up SSL certificate for HTTPS
- [ ] Configure cron job for price updates
- [ ] Create deployment documentation
- [ ] Perform final security audit

## Original Development Tasks

- Draw SQL schema [✓]
    -- https://drawsql.app/teams/wnh/diagrams/web-emas
- Setup Frontend (UI), HTML, CSS and Bootstrap
  - Dashboard [✓ mockup created]
    - Gold price table
    - Button for order placement
  - Login & Logout [partial - placeholders exist]
  - Contact
- Setup Backend
  - Setup SQL script (PDO)
  - Setup function/formula for calculator
  - Setup authentication logic

## Data Structure

### Gold Conversion Factor (Metal Price API)

- 1 Troy Ounce = 31 gram
- RM/gram = RM for 1 TO / 31 gram
- Margin for Staff: + 0.5%
- Margin for Agent: + 1%
- Margin for Customer: +5%

## Roles:
- Admin / Agent / Staff

### Agent Tiers
- Agent Tier A = 0.02%
- Agent Tier B = 0.05%
- Agent Tier C = 0.10%


# MDO-Kedai-emas
