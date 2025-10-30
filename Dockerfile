# Usa imagem oficial do PHP com suporte a servidor embutido
FROM php:8.2-cli

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia todos os arquivos pro container
COPY . /var/www/html

# Expõe a porta usada pelo Render
EXPOSE 10000

# Comando pra rodar servidor PHP embutido
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/var/www/html"]
