#!/bin/bash
#
# Example shell script to run pre-provisioning.
#
# This script auto genrates self-signed certificates.
# Generate a new RSA private key.
sudo openssl genrsa -out "/etc/ssl/certs/unicef-root-CA.key" 4096

# Generate a CSR using the private key for encryption
sudo openssl req -new -key "/etc/ssl/certs/unicef-root-CA.key" -subj "/O=web/OU=web/CN=*.unicefplatform.com" -out "/etc/ssl/certs/unicef-root-CA.csr"

# Sign and generate a certificate.
sudo openssl x509 -req -days 365 -in "/etc/ssl/certs/unicef-root-CA.csr" -signkey "/etc/ssl/certs/unicef-root-CA.key" -out "/etc/ssl/certs/unicef-root-CA.crt"
