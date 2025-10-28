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

## Development Tasks

- Draw SQL schema []
    -- https://drawsql.app/teams/wnh/diagrams/web-emas
- Setup Frontend (UI), HTML, CSS and Bootstrap
  - Dashboard
    - Gold price table
    - Button for order placement
  - Login & Logout
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
