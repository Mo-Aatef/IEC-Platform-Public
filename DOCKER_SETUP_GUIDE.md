
# IEC Platform - Ops Guide

## 🚀 Quick Commands

### Start Everything

```bash
ansible-playbook ansible/start-services.yml
```

### Stop Everything
```bash
ansible-playbook ansible/stop-services.yml
```

---

## 🌐 Services Dashboard

| Service | URL | Credentials | Description |
|---------|-----|-------------|-------------|
| **Web App** | [http://iec.test:8080](http://iec.test:8080)      | (user: password)       | The main e-learning platform. |
| **phpMyAdmin** | [http://localhost:8082](http://localhost:8082) | (root: rootpass123)    | Database management interface. |
| **Nagios** | [http://localhost:8081](http://localhost:8081)     | (nagiosadmin: nagios)  | System monitoring dashboard. |

> **Note:** If `http://iec.test:8080` doesn't load, use [http://localhost:8080](http://localhost:8080).

---

## 🛠️ Service Details

- **Web Server (Apache/PHP)**: Host the main application code from `Apache/www`.
- **Database (MariaDB)**: Stores all user and platform data.
- **DNS Server (BIND)**: Allows using `iec.test` domain locally.
- **Nagios**: Monitors container health and uptime.