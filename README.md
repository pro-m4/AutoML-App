# AutoML Full-Stack Web Platform

An automated machine learning (AutoML) web application that enables users to upload datasets, trigger automated model training, and perform real-time inferences through a clean, modern dashboard.

The platform decouples the web user interface (PHP/JavaScript) from the heavy machine learning workloads (Python async workers), supporting state-of-the-art AutoML libraries like FLAML, H2O, and MLJAR.

## Architecture & Tech Stack
* **Front-end:** HTML5, CSS3, JavaScript (ES6+), Bootstrap, jQuery
* **Web API (Back-end):** PHP
* **Database:** MySQL
* **ML Core:** Python (FLAML, H2O, MLJAR, Scikit-learn, joblib)

## Project Structure
```text
+-- pages/               # PHP Webpages
+-- models/              # Saved trained machine learning models
+-- src/                 # Client-side assets (JS & CSS)
+-- uploads/             # User file repository (datasets & inference)
+-- server/              # Backend Processing & dbconnect.php
¦   +-- php/             # PHP API endpoints 
¦   +-- python/          # Core Python ML & AutoML scripts