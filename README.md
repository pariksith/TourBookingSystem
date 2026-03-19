<div align="center">

<img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
<img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
<img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" />
<img src="https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" />
<img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" />

# ✈️ InteleTour Booking System

### A full-featured, production-ready travel & tour booking web application
### built with PHP, MySQL, Bootstrap 5, and vanilla JavaScript.

[🌐 Live Demo](#) · [📸 Screenshots](#screenshots) · [🚀 Quick Start](#quick-start) · [📖 Docs](#documentation)

---

![InteleTour Banner](images/paris.jpg)

</div>

---

## 📌 Overview

**InteleTour** is a complete travel booking platform that allows users to browse tour packages,
make bookings, process payments, write reviews, and manage their trips — all from a clean,
modern, mobile-responsive interface. Admins get a powerful dashboard to manage packages,
users, bookings, coupons, and analytics.

---

## ✨ Features

### 👤 User Side
- 🔐 **Authentication** — Register, Login, Logout with session-based security
- 🌍 **Browse Packages** — Filter by category, destination, price, duration, difficulty
- 🔍 **Live Search** — AJAX-powered real-time package search with keyboard navigation
- 🛒 **Booking System** — Multi-step booking with room type, meal plan, travel date
- 🏷️ **Coupon Codes** — AJAX coupon validation with real-time price recalculation
- 💳 **Payment Gateway** — Credit/Debit Card, UPI, Net Banking, PayPal simulation
- 🧾 **Receipt Generation** — Printable booking receipt with full trip summary
- ❤️ **Wishlist** — Save favourite packages with one click
- ⭐ **Reviews & Ratings** — Leave star ratings and reviews after completed trips
- 🔔 **Notifications** — Real-time booking status and payment notifications
- 📊 **My Dashboard** — View bookings, cancel trips, track spending
- 🤖 **AI Travel Assistant** — Intelligent chatbot for destination recommendations
- 📱 **PWA Support** — Installable Progressive Web App

### 🛡️ Admin Side
- 📈 **Overview Dashboard** — Revenue charts, booking stats, top packages
- 📦 **Package Management** — Full CRUD with image upload, featured toggle
- 👥 **User Management** — Activate/suspend users, change roles
- 📋 **Booking Management** — Update booking status, view all bookings
- 🏷️ **Coupon Management** — Create/edit/delete discount coupons
- ⭐ **Review Moderation** — Approve or delete user reviews
- 🗂️ **Category Management** — Add/edit tour categories
- 📣 **Broadcast Notifications** — Send announcements to all users
- 📊 **Analytics Tab** — Revenue trends and booking breakdowns

---

## 🖥️ Screenshots

| Home Page | Package Listing | Booking Form |
|-----------|----------------|--------------|
| ![Home](images/paris.jpg) | ![Packages](images/bali.jpg) | ![Booking](images/maldives.jpg) |

| Admin Dashboard | Payment Page | Receipt |
|----------------|-------------|---------|
| ![Admin](images/dubai.jpg) | ![Payment](images/tokyo.jpg) | ![Receipt](images/santorini.jpg) |

---

## 🗂️ Project Structure

intele-tour/
│
├── 📄 index.php # Home page — hero, featured packages, destinations
├── 📄 packages.php # Package listing with filters & search
├── 📄 booking.php # Multi-step booking form
├── 📄 payment.php # Payment processing + receipt generation
├── 📄 my_bookings.php # User dashboard — bookings, wishlist, profile
├── 📄 review.php # Submit/view reviews
├── 📄 ai_assistant.php # AI-powered travel chatbot
├── 📄 admin.php # Full admin control panel
├── 📄 login.php # User login
├── 📄 register.php # User registration
├── 📄 logout.php # Session destroy
├── 📄 database.php # DB class, helpers, security, session manager
├── 🎨 style.css # Complete custom stylesheet
├── ⚡ script.js # Global JavaScript — all interactions
├── 📄 sw.js # Service Worker (PWA)
├── 📄 manifest.json # PWA manifest
├── 🗄️ intele_tour.sql # Complete database schema + seed data
└── 🖼️ images/ # Package images folder

text

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Apache/Nginx (XAMPP / WAMP / Laragon recommended)
- Composer *(optional)*

