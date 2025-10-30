# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a gold shop (Kedai Emas) web application for displaying and managing gold prices with role-based access control. The application allows the shop owner to display dynamically updating gold prices with custom margins for different user roles (Admin, Staff, Agent).

## Technology Stack

- **Frontend**: HTML, CSS, Bootstrap 5
- **Backend**: PHP (MVC architecture) - *Not yet implemented*
- **Database**: MySQL with PDO
- **External API**: Metal Price API for live gold pricing

## Database Schema

The application uses the following database structure (defined in `draw-sql.sql`):

- **users**: User accounts with roles and profit margins
- **roles**: Role definitions (Admin, Agent, Staff)
- **profit_margin**: Profit margin rates for different user tiers
- **product_data**: Gold product information and stock
- **contact_submission**: Contact form submissions

Note: There's a schema bug in `draw-sql.sql:39` - the foreign key references `roles(name)` but should reference `roles(id)`.

## Gold Price Calculation Formula

The application uses the following conversion and margin logic:

**Base Conversion:**
- 1 Troy Ounce = 31 grams
- RM/gram = (RM for 1 Troy Ounce) / 31 grams

**Profit Margins by Role:**
- Staff: +0.5%
- Agent Tier A: +0.02%
- Agent Tier B: +0.05%
- Agent Tier C: +0.10%
- Agent (general): +1%
- Customer: +5%

## Current Implementation Status

**Completed:**
- Database schema design
- Dashboard UI mockup (`dashboard.html`) with Bootstrap 5
- Basic authentication pages (login, register, index)

**Pending:**
- Backend PHP implementation (MVC structure)
- Database connection and PDO setup
- Authentication and RBAC logic
- API integration with Metal Price API
- Gold calculator functionality
- Automatic price updates

## File Structure

- `dashboard.html` - Main admin dashboard with sidebar navigation, widgets, and Chart.js price chart
- `login.html`, `register.html`, `index.html` - Authentication pages (placeholder implementations)
- `draw-sql.sql` - Database schema SQL script
- `readme.md` - Project documentation and requirements

## Development Notes

- The dashboard uses Bootstrap 5.3.3 and Chart.js for visualization
- Price chart displays 7-day gold price history
- Dashboard includes sections for: price changes, stock count, current gold price, and price chart
- Navigation includes: Dashboard, Perubahan Harga, Harga Emas Semasa, Stock Count, and Inbox
