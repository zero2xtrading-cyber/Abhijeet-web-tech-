# Apex Institute of Technology & Management - Admission Portal

An interactive, responsive online application and admission enrollment portal built with **HTML5, CSS3, JavaScript, jQuery, and PHP**.

Upon form submission (button click), user data is validated client-side with jQuery, submitted asynchronously to PHP via AJAX, validated and sanitized on the server, and displayed dynamically as a **formatted, stylized provisional admission acknowledgement voucher** directly in the browser with print and PDF export capabilities.

---

## 🎨 Theme & Highlights

- **Branding**: Apex Institute of Technology & Management (AITM).
- **Design System**: Emerald / Teal and Amber Gold accents with custom segmented controls.
- **Client-Side Validation (jQuery)**: Email regex check, 10-digit mobile number, 6-digit postal PIN code, dynamic error display, and live feedback.
- **Backend (PHP)**: Input sanitization (`trim`, `stripslashes`, `htmlspecialchars`), email validation (`filter_var`), unique enrolment reference number generation (`AITM-2026-XXXXX`), and dual response format (JSON for AJAX and HTML fallback).
- **Print Optimization**: Dedicated `@media print` CSS rules to format the official admission voucher onto standard paper/PDF while removing navigation buttons.

---

## 📁 File Structure

```text
web-tech-abhijeet/
│
├── index.html       # Candidate enrollment form & voucher layout
├── style.css        # Teal/Amber responsive styling & print layout
├── script.js        # jQuery client-side validation & AJAX handler
├── process.php      # PHP backend (validation, sanitization, ID generator)
├── server.js        # Built-in zero-dependency Node.js test server
├── Dockerfile       # Production PHP 8.2 + Apache container definition
├── render.yaml      # Blueprint for 1-click cloud deploy on Render
├── vercel.json      # Configuration for Vercel
└── README.md        # Documentation and deployment instructions
```

---

## 🚀 Running Locally

```bash
node server.js
```
Then visit: 👉 `http://localhost:3000`

---

## ☁️ Free Cloud Deployment on Render.com

1. Sign in to [Render.com](https://render.com) with GitHub.
2. Click **New +** -> **Web Service**.
3. Select this repository (`Abhijeet-web-tech-`).
4. Select **Docker** environment and **Free** tier.
5. Click **Create Web Service** to launch your live site!
