# IEC Platform - Docker Setup Guide

Complete guide for setting up and running the IEC Platform with Docker, including the BIND DNS server for local domain resolution.

## Prerequisites

- **Docker Desktop** installed and running
  - [Download Docker Desktop](https://www.docker.com/products/docker-desktop/)
- **macOS, Windows, or Linux**
- **Ansible** installed (`brew install ansible` on macOS)

---

## Quick Start (Recommended)

### 1. Start All Services

```bash

cd /path/to/IEC-platform
ansible-playbook ansible/start-services.yml
```

This script will:
- ✓ Check Docker is running
- ✓ Create the shared network (`csn_net`)
- ✓ Start database server (MariaDB + phpMyAdmin)
- ✓ Start web server (Apache + PHP)
- ✓ Start DNS server (BIND9)
- ✓ Start Nagios (optional monitoring)

### 2. Configure DNS (One-Time Setup)

For `http://iec.test:8080` to work, configure your system DNS:

**macOS:**
1. System Settings → Network → Select your connection → Details
2. Click **DNS** tab → Click **+** button
3. Add: `127.0.0.1`
4. **Drag it to the TOP** of the DNS list
5. Click OK → Apply

**Windows:**
1. Control Panel → Network and Internet → Network Connections
2. Right-click your adapter → Properties
3. Select "Internet Protocol Version 4 (TCP/IPv4)" → Properties
4. Select "Use the following DNS server addresses"
5. Preferred DNS: `127.0.0.1`
6. Alternate DNS: `8.8.8.8`
7. Click OK

### 3. Access the Platform

Open your browser:
- **Web Application**: http://iec.test:8080 (with DNS) or http://localhost:8080
- **phpMyAdmin**: http://localhost:8082
- **Nagios**: http://localhost:8081

### 4. Stop All Services

```bash
ansible-playbook ansible/stop-services.yml
```

---

## Manual Setup (Step-by-Step)

If you prefer to start services individually:

### Step 1: Create Shared Network

All containers need a shared Docker network:

```bash
docker network create csn_net
```

### Step 2: Start Database Server

```bash
cd mariadb
docker-compose -f docker-compose.db.yml up -d
cd ..
```

**Access phpMyAdmin:**
- URL: http://localhost:8082
- Username: `root`
- Password: `rootpass123`

### Step 3: Start Web Server

```bash
cd Apache
docker-compose -f docker-compose.web.yml up -d
cd ..
```

**Access Web Application:**
- URL: http://localhost:8080

### Step 4: Start DNS Server

```bash
cd bind
docker-compose -f docker-compose.bind.yml up -d
cd ..
```

The DNS server resolves `iec.test` → `127.0.0.1`

### Step 5: Start Nagios (Optional)

```bash
cd nagios
docker-compose up -d
cd ..
```

**Access Nagios:**
- URL: http://localhost:8081
- Username: `nagiosadmin`
- Password: `nagios`

---

## DNS Server Configuration

### What is the DNS Server?

The BIND DNS server allows you to access your website using a custom domain (`iec.test`) instead of `localhost:8080`.

### Why Use `iec.test`?

- **`.test`**: RFC 6761 reserved for testing (works on all platforms)
- **`.local`**: Conflicts with macOS mDNS/Bonjour (not recommended)
- **`.dev`**: Requires HTTPS (not suitable for local development)

### Configuring Your System

#### macOS Configuration

1. **Open System Settings**
   - Click  → System Settings → Network

2. **Select Your Network**
   - Click your active connection (Wi-Fi or Ethernet with green dot)
   - Click **Details...** button

3. **Add DNS Server**
   - Click **DNS** tab
   - Click **+** button below DNS servers list
   - Type: `127.0.0.1`
   - **Important**: Drag `127.0.0.1` to the **top** of the list

4. **Apply Changes**
   - Click **OK** → Apply

5. **Flush DNS Cache**
   ```bash
   dscacheutil -flushcache
   ```

6. **Verify Configuration**
   ```bash
   scutil --dns | grep -A 3 "resolver #1"
   # Should show: nameserver[0] : 127.0.0.1
   ```

#### Windows Configuration

1. **Open Network Connections**
   - Control Panel → Network and Internet → Network Connections

2. **Configure Adapter**
   - Right-click your network adapter → Properties
   - Select "Internet Protocol Version 4 (TCP/IPv4)"
   - Click Properties

3. **Set DNS Servers**
   - Select "Use the following DNS server addresses"
   - Preferred DNS server: `127.0.0.1`
   - Alternate DNS server: `8.8.8.8` (Google DNS fallback)
   - Click OK

4. **Flush DNS Cache**
   ```cmd
   ipconfig /flushdns
   ```

---

## Testing & Verification

### Test DNS Resolution

```bash
# Test with nslookup
nslookup iec.test 127.0.0.1
# Expected: Name: iec.test, Address: 127.0.0.1

# Test with ping
ping iec.test
# Expected: Reply from 127.0.0.1

# Test with curl
curl http://iec.test:8080
```

### Check Container Status

```bash
docker ps
```

You should see:
- `webserver` - Apache web server
- `dbserver` - MariaDB database
- `phpmyadmin` - Database management
- `dns-server` - BIND DNS server
- `nagios_container` - Monitoring (optional)

### View Logs

```bash
# Web server logs
docker logs webserver

# DNS server logs
docker logs dns-server

# Database logs
docker logs dbserver
```

---

## Common Issues & Solutions

### Issue 1: DNS Not Resolving in Browser

**Symptoms:**
- `nslookup iec.test 127.0.0.1` works
- Browser shows "Site can't be reached" for `http://iec.test:8080`

**Solution:**
1. Verify `127.0.0.1` is at the **top** of DNS servers list
2. Flush DNS cache:
   ```bash
   # macOS
   dscacheutil -flushcache
   
   # Windows
   ipconfig /flushdns
   ```
3. Restart your browser
4. Try incognito/private browsing mode

**Alternative:**
Use `/etc/hosts` (macOS/Linux) or `C:\Windows\System32\drivers\etc\hosts` (Windows):
```
127.0.0.1    iec.test www.iec.test
```

### Issue 2: DNS Container Not Running

**Check Status:**
```bash
docker ps -a | grep dns-server
```

**Restart DNS Server:**
```bash
cd bind
docker-compose -f docker-compose.bind.yml restart
```

**View Logs:**
```bash
docker logs dns-server
```

### Issue 3: Port Already in Use

**Error:** "Bind for 0.0.0.0:53 failed: port is already allocated"

**Solution:**

**macOS:** Port 53 might be used by mDNSResponder
```bash
# Check what's using port 53
sudo lsof -i :53

# If needed, you can use /etc/hosts instead of DNS server
```

**Windows:** Disable DNS Client service temporarily or use different ports

### Issue 4: Database Connection Failed

**Symptoms:**
- Web application can't connect to database

**Solution:**
1. Ensure database is running:
   ```bash
   docker ps | grep dbserver
   ```

2. Check database logs:
   ```bash
   docker logs dbserver
   ```

3. Verify environment variables in `Apache/docker-compose.web.yml`:
   ```yaml
   DB_HOST: dbserver
   DB_NAME: iec_platform  
   DB_USER: appuser
   DB_PASSWORD: secret123
   ```

---

## Adding Custom Subdomains

To add subdomains like `api.iec.test` or `admin.iec.test`:

1. **Edit DNS Zone File**
   ```bash
   nano bind/db.iec.test
   ```

2. **Add A Records**
   ```dns
   api     IN      A       127.0.0.1
   admin   IN      A       127.0.0.1
   ```

3. **Restart DNS Server**
   ```bash
   cd bind
   docker-compose -f docker-compose.bind.yml restart
   ```

4. **Test**
   ```bash
   nslookup api.iec.test 127.0.0.1
   ```

---

## Port Summary

| Service | Port | URL | Credentials |
|---------|------|-----|-------------|
| **Web App** | 8080 | http://iec.test:8080 or http://localhost:8080 | Sign in via web |
| **phpMyAdmin** | 8082 | http://localhost:8082 | root / rootpass123 |
| **Nagios** | 8081 | http://localhost:8081 | nagiosadmin / nagios |
| **DNS Server** | 53 | N/A (internal) | N/A |
| **Database** | 3306 | Internal only | appuser / secret123 |

---

## Understanding the Setup

### Docker Network: `csn_net`

All containers communicate through a shared Docker network called `csn_net`. This allows containers to reference each other by name (e.g., `dbserver`, `dns-server`).

### DNS Server Architecture

```
Browser Request (iec.test)
        ↓
macOS/Windows DNS Resolver
        ↓
127.0.0.1:53 (BIND DNS Server in Docker)
        ↓
Returns: 127.0.0.1
        ↓
Browser connects to 127.0.0.1:8080 (Web Server)
```

### Why DNS Server vs /etc/hosts?

**DNS Server (Recommended):**
- ✅ Proper DNS resolution
- ✅ Can add subdomains dynamically
- ✅ Professional setup
- ✅ Learning DNS server configuration

**/etc/hosts (Simple Alternative):**
- ✅ Simple one-line configuration
- ✅ No DNS configuration needed
- ❌ Static only (can't add subdomains without editing)
- ❌ Bypasses DNS learning

---

## Windows vs macOS Differences

| Feature | macOS | Windows |
|---------|-------|---------|
| **DNS Configuration** | System Settings → Network → DNS | Control Panel → Network Adapter |
| **`.local` TLD** | ❌ Conflicts with mDNS | ✅ Works fine |
| **`.test` TLD** | ✅ Works | ✅ Works |
| **Flush DNS** | `dscacheutil -flushcache` | `ipconfig /flushdns` |
| **Hosts File** | `/etc/hosts` | `C:\Windows\System32\drivers\etc\hosts` |

**Recommendation:** Use `.test` TLD for cross-platform compatibility.

---

## Resetting Everything

If you need to start fresh:

### Stop All Containers
```bash
ansible-playbook ansible/stop-services.yml
```

### Remove All Containers
```bash
docker-compose -f Apache/docker-compose.web.yml down
docker-compose -f mariadb/docker-compose.db.yml down
docker-compose -f bind/docker-compose.bind.yml down
docker-compose -f nagios/docker-compose.yml down
```

### Remove Database Volume (Data Loss!)
```bash
docker volume rm mariadb_db_data
```

### Remove Network
```bash
docker network rm csn_net
```

### Start Fresh
```bash
ansible-playbook ansible/start-services.yml
```

---

## Production Deployment Notes

**This setup is for LOCAL DEVELOPMENT only.**

For production deployment:
1. ❌ **Don't use** `/etc/hosts` or local DNS server
2. ✅ **Buy a real domain** (e.g., from Namecheap, GoDaddy)
3. ✅ **Point DNS A Record** to your server's public IP
4. ✅ **Use port 80** (HTTP) or **443** (HTTPS), not 8080
5. ✅ **Get SSL certificate** (Let's Encrypt)
6. ✅ **Use environment variables** for sensitive data
7. ✅ **Enable security headers** and firewall rules

---

## Troubleshooting Commands

```bash
# Check if Docker is running
docker ps

# Check all containers (including stopped)
docker ps -a

# View container logs
docker logs <container_name>

# Restart a container
docker restart <container_name>

# Check network
docker network inspect csn_net

# Check DNS configuration (macOS)
scutil --dns

# Check DNS configuration (Windows)
ipconfig /all

# Test DNS resolution
nslookup iec.test 127.0.0.1

# Test web server
curl http://localhost:8080
curl http://iec.test:8080

# Enter container shell
docker exec -it <container_name> /bin/bash
```

---

## Getting Help

If you encounter issues:
1. Check container logs: `docker logs <container_name>`
2. Verify all containers are running: `docker ps`
3. Check network connectivity: `docker network inspect csn_net`
4. Review this guide's troubleshooting section
5. Check Docker Desktop for any error messages

---

**Happy coding! 🚀**
