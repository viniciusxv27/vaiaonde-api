#!/bin/bash

# Script para iniciar o servidor Laravel com limites de upload aumentados
php -d upload_max_filesize=600M \
    -d post_max_size=600M \
    -d max_execution_time=600 \
    -d max_input_time=600 \
    -d memory_limit=512M \
    artisan serve
