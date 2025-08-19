# ⚙️ ToolFlow Leoni — Symfony 6.4.*

[![Symfony](https://img.shields.io/badge/Symfony-v6.4-black?logo=symfony)](https://symfony.com/)
[![PHP](https://img.shields.io/badge/PHP-%5E8.2-blue?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?logo=mysql)](https://www.mysql.com/)
[![XAMPP](https://img.shields.io/badge/XAMPP-Server-lightgrey?logo=xampp)](https://www.apachefriends.org/)

---

## 📌 Description
**ToolFlow** est une solution numérique conçue et développée avec **Symfony v6.4.\*** dans un environnement **XAMPP**.  
Elle est dédiée à la **traçabilité des mouvements d’outils (entrées et sorties)** au sein du service de maintenance.

Cette application assure :  
- 📡 Un suivi en temps réel des outils  
- 📦 Une optimisation de la gestion des stocks d’outillage  
- 🛠️ Une meilleure organisation des opérations de maintenance  
- ✅ Une fiabilité renforcée des processus  
- 🔍 Une transparence des flux logistiques  
- ⚡ Une réactivité accrue des équipes techniques  

---

## 🚀 Modules Fonctionnels
- 🔐 **Authentification et Sécurité** 
- 👥 **Gestion des Utilisateurs**  
- 🛠️ **Gestion des Outils**  
- 📏 **Gestion des Mesures**  
- 🔄 **Gestion des Entrées/Sorties**  
- 📊 **Suivi du Trafic Entrées / Sorties**  
- 📝 **Gestion des Réclamations**  
- 📈 **Tableaux de Bord**  

---

## ⚙️ Installation et Configuration

### 🔧 Prérequis
- PHP **^8.2**  
- Symfony CLI  
- XAMPP (Apache + MySQL)  
- Composer  

### 1️⃣ Cloner le projet
```bash
git clone https://github.com/ramichatti/Leoni-ToolFlow.git
cd Leoni-ToolFlow
```

### 2️⃣ Configurez ensuite votre base MySQL (via XAMPP) :

Dans le fichier .env.local :
```ini
DATABASE_URL="mysql://root:@127.0.0.1:3306/app_leoni"
```

### 3️⃣ Créer la base et exécuter les migrations
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```


### 4️⃣ Lancer le serveur Symfony
```bash
symfony server:start
```



