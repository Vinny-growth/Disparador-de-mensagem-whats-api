#!/bin/bash

# Atualizar o sistema
echo "Atualizando o sistema..."
sudo apt update && sudo apt upgrade -y

# Instalar Apache
echo "Instalando Apache..."
sudo apt install -y apache2

# Configurar o firewall para permitir o tráfego da web
echo "Configurando o firewall para permitir tráfego da web..."
sudo ufw allow in "Apache Full"

# Instalar MySQL
echo "Instalando MySQL..."
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Instalar PHP e extensões necessárias
echo "Instalando PHP e extensões necessárias..."
sudo apt install -y php libapache2-mod-php php-mysql php-mbstring

# Instalação do Composer
echo "Instalando Composer..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"

# Habilitar mod_rewrite do Apache para regras de URL amigável, se necessário
sudo a2enmod rewrite
sudo systemctl restart apache2

# Criação do banco de dados e usuário
echo "Criando banco de dados e usuário..."
sudo mysql -e "CREATE DATABASE whatsapp_dispatcher;"
sudo mysql -e "CREATE USER 'dispatcher_user'@'localhost' IDENTIFIED BY 'your_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON whatsapp_dispatcher.* TO 'dispatcher_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Instalar dependências do projeto via Composer
echo "Instalando dependências do projeto..."
composer install

# Finalização
echo "Instalação concluída. Por favor, configure o arquivo de conexão do banco de dados com os detalhes corretos."
