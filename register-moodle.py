#!/usr/bin/env python3
"""
Register Moodle Stack to Komodo
Registers the Moodle learning platform as a managed stack in Komodo
"""

import requests
import json
import sys
from typing import Dict, Any

# Komodo API Configuration
KOMODO_API_URL = "http://192.168.200.40:9120"
API_KEY = "YOUR_API_KEY"  # Replace with actual API key from Komodo
API_SECRET = "YOUR_API_SECRET"  # Replace with actual API secret from Komodo

def get_headers() -> Dict[str, str]:
    """Get headers for Komodo API requests"""
    return {
        "X-API-Key": API_KEY,
        "X-API-Secret": API_SECRET,
        "Content-Type": "application/json"
    }

def deploy_moodle_stack() -> bool:
    """Deploy Moodle stack to Komodo"""
    print("🚀 Deploying Moodle Stack to Komodo...")
    
    stack_config = {
        "name": "moodle",
        "server": "Local",
        "compose_file": "/etc/komodo/stacks/moodle/docker-compose.yml",
        "environment": {
            "MOODLE_URL": "https://moodle-stg.ligjamaica.com",
            "MOODLE_DATAROOT": "/var/www/moodledata",
            "MOODLE_ADMIN": "admin",
            "DB_TYPE": "pgsql",
            "DB_HOST": "moodle-db",
            "DB_PORT": "5432",
            "DB_NAME": "moodle",
            "DB_USER": "moodle"
        },
        "labels": {
            "environment": "staging",
            "application": "learning-platform",
            "team": "education"
        },
        "ports": {
            "80": "8080"
        }
    }
    
    try:
        # Deploy the stack
        response = requests.post(
            f"{KOMODO_API_URL}/execute/DeployStack",
            headers=get_headers(),
            json=stack_config,
            timeout=60
        )
        
        if response.status_code in [200, 201]:
            print("✅ Moodle stack deployed successfully!")
            print(f"Response: {response.json()}")
            return True
        else:
            print(f"❌ Failed to deploy stack: {response.status_code}")
            print(f"Response: {response.text}")
            return False
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Error connecting to Komodo API: {e}")
        return False

def get_stack_status() -> bool:
    """Get the current status of Moodle stack"""
    print("\n📊 Checking Moodle Stack Status...")
    
    try:
        response = requests.post(
            f"{KOMODO_API_URL}/read/GetStack",
            headers=get_headers(),
            json={"server": "Local", "stack": "moodle"},
            timeout=30
        )
        
        if response.status_code == 200:
            stack_info = response.json()
            print("✅ Stack Status Retrieved:")
            print(json.dumps(stack_info, indent=2))
            return True
        else:
            print(f"❌ Failed to get stack status: {response.status_code}")
            print(f"Response: {response.text}")
            return False
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Error connecting to Komodo API: {e}")
        return False

def start_moodle_container() -> bool:
    """Start the Moodle container"""
    print("\n▶️  Starting Moodle Container...")
    
    try:
        response = requests.post(
            f"{KOMODO_API_URL}/execute/StartContainer",
            headers=get_headers(),
            json={"server": "Local", "container": "moodle"},
            timeout=30
        )
        
        if response.status_code == 200:
            print("✅ Moodle container started successfully!")
            print(f"Response: {response.json()}")
            return True
        else:
            print(f"❌ Failed to start container: {response.status_code}")
            print(f"Response: {response.text}")
            return False
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Error connecting to Komodo API: {e}")
        return False

def main():
    """Main execution"""
    if API_KEY == "YOUR_API_KEY" or API_SECRET == "YOUR_API_SECRET":
        print("❌ ERROR: Please configure API_KEY and API_SECRET in this script")
        print("   Get these from your Komodo installation")
        sys.exit(1)
    
    print("=" * 60)
    print("Moodle Komodo Stack Registration")
    print("=" * 60)
    
    # Deploy stack
    if not deploy_moodle_stack():
        sys.exit(1)
    
    # Check status
    if not get_stack_status():
        print("⚠️  Warning: Could not retrieve stack status")
    
    # Start container
    if not start_moodle_container():
        print("⚠️  Warning: Could not start container, it may be running already")
    
    print("\n" + "=" * 60)
    print("🎉 Moodle Stack Registration Complete!")
    print("=" * 60)
    print("\nNext Steps:")
    print("1. Access Moodle at: https://moodle-stg.ligjamaica.com")
    print("2. Complete the Moodle installation wizard")
    print("3. Configure database connection if not auto-detected")
    print("4. Create admin user and initial site settings")
    print("\nMonitor logs: docker logs moodle")
    print("=" * 60)

if __name__ == "__main__":
    main()
