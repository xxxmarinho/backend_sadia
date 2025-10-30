# Usa a imagem PHP 8 com servidor embutido
FROM php:8.1-cli

# Copia os arquivos do projeto para o container
COPY . /var/www/html

# Define o diretório de trabalho
WORKDIR /var/www/html

# Expõe a porta que o Render vai usar
EXPOSE 10000

# Inicia o servidor PHP embutido
CMD ["php", "-S", "0.0.0.0:10000"]
