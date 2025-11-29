FROM php:8.2-alpine

# تثبيت الإضافات المطلوبة
RUN apk add --no-cache \
    curl \
    git \
    unzip

# نسخ ملفات المشروع
COPY . /var/www/html

# تغيير المسار
WORKDIR /var/www/html

# تشغيل السيرفر
CMD ["php", "-S", "0.0.0.0:8000"]
