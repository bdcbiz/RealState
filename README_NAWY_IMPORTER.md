# Nawy.com Compounds Importer

## نظرة عامة

هذا السكريبت يقوم باستيراد بيانات أكثر من **1344 كمباوند** من موقع Nawy.com إلى قاعدة البيانات الخاصة بك.

## المميزات

✅ **استخراج كامل للبيانات:**
- اسم الكمباوند (عربي/إنجليزي)
- المطور (Developer) مع المطابقة التلقائية
- الموقع (Location)
- الإحداثيات الجغرافية
- رابط Google Maps
- جميع الصور
- Master Plan
- عدد الوحدات
- الأسعار
- الوصف التفصيلي

✅ **تحميل الصور:**
- تحميل تلقائي لجميع الصور
- حفظ في مجلد منظم (compound-images/)
- دعم كافة صيغ الصور (JPG, PNG, WEBP, etc.)

✅ **مطابقة المطورين:**
- مطابقة تلقائية مع 557 شركة موجودة في قاعدة البيانات
- خوارزمية ذكية للمطابقة (Exact, Partial, Fuzzy)

✅ **معالجة الأخطاء:**
- تخطي الكمباوندات المكررة تلقائياً
- إعادة المحاولة عند الفشل
- تتبع كامل للإحصائيات

✅ **إمكانية الاستئناف:**
- يمكن إيقاف وإكمال الاستيراد لاحقاً
- دعم الاستيراد الجزئي

## الملفات المطلوبة

```
/var/www/realestate/
├── import_compounds_final.php  (السكريبت الرئيسي)
├── compound_urls.txt           (1344 رابط)
└── storage/compound-images/    (سيتم إنشاؤه تلقائياً)
```

## الاستخدام

### 1. استيراد كامل (1344 كمباوند)

```bash
cd /var/www/realestate
php import_compounds_final.php
```

### 2. استيراد جزئي (أول 100 كمباوند للاختبار)

```bash
php import_compounds_final.php --limit=100
```

### 3. الاستئناف من نقطة معينة

```bash
php import_compounds_final.php --start=500
```

### 4. استيراد نطاق محدد

```bash
php import_compounds_final.php --start=100 --limit=50
```

## الوقت المتوقع

- **للاختبار (10 compounds):** ~30 ثانية
- **استيراد جزئي (100 compounds):** ~5 دقائق
- **استيراد كامل (1344 compounds):** ~60-90 دقيقة

*يعتمد على سرعة الإنترنت ومواصفات السيرفر*

## الإحصائيات التي سيعرضها السكريبت

```
📊 Statistics:
  Total processed:        1344
  ✓ Successfully imported: 1280
  ⊘ Skipped (duplicates):  50
  ✗ Failed:                14
  ⏱ Total time:            1h 23m
  📈 Average rate:         0.27 compounds/sec
```

## البيانات التي سيتم استيرادها

### جدول Compounds

| الحقل | الوصف | مثال |
|------|------|------|
| project | اسم الكمباوند | "O West Orascom" |
| company_id | المطور (FK) | 549 |
| location | الموقع | "October Gardens" |
| location_url | رابط الخريطة | "https://www.google.com/maps?q=..." |
| images | مصفوفة الصور (JSON) | ["compound-images/258/..."] |
| total_units | عدد الوحدات | 342 |
| finish_specs | الوصف | "O West Compound is..." |

## معالجة المطورين

السكريبت يطابق المطورين بـ 3 طرق:

1. **Exact Match:** مطابقة تامة للاسم
2. **Partial Match:** البحث ضمن النص
3. **Fuzzy Match:** إزالة الكلمات الشائعة ومقارنة

### أمثلة على المطابقة:

| اسم في Nawy | اسم في قاعدة البيانات | النتيجة |
|-------------|----------------------|---------|
| La Vista Developments | لافيستا | ✓ Matched |
| Mountain View | ماونتن فيو للتطوير العقاري | ✓ Matched |
| Orascom Development Egypt | Orascom Developments | ✓ Matched |

## المطورون الغير موجودين

إذا لم يتم العثور على المطور، سيظهر في التقرير النهائي:

```
⚠ Developers not found in database (15):
  - New Developer Name
  - Another Developer
  ...
```

**الحل:** أضف هذه الشركات يدوياً إلى جدول `companies` ثم أعد تشغيل السكريبت على الكمباوندات الفاشلة.

## نقل الصور إلى public/storage

بعد الانتهاء من الاستيراد، الصور ستكون في:
```
/var/www/realestate/storage/compound-images/
```

لنقلها إلى المكان الصحيح:

```bash
cp -r storage/compound-images/* public/storage/compound-images/
chmod -R 755 public/storage/compound-images/
```

## استكشاف الأخطاء

### خطأ: "Database connection failed"
```bash
# تحقق من بيانات الاتصال
mysql -u laravel -plaravel123 real_state -e "SELECT 1;"
```

### خطأ: "Failed to fetch page"
```bash
# تحقق من اتصال الإنترنت
curl -I https://www.nawy.com
```

### خطأ: "Permission denied" للصور
```bash
chmod -R 755 storage/
chown -R www-data:www-data storage/
```

## الأمان

⚠️ **هام:**
- السكريبت يستخدم `SSL_VERIFYPEER => false` للتطوير فقط
- للإنتاج، يُفضل استخدام SSL verification
- لا تشارك ملف compound_urls.txt في Git

## الدعم

إذا واجهت مشاكل:
1. تحقق من ال logs في `/var/log/nginx/` أو `/var/log/apache2/`
2. راجع الإحصائيات في نهاية التشغيل
3. استخدم `--limit=5` للاختبار أولاً

## الإصدار

- **Version:** 1.0
- **Date:** 2025
- **Author:** Claude Code
- **License:** Private Use Only

---

**ملاحظة:** هذا السكريبت مصمم خصيصاً لنظام Laravel Real Estate ويستخدم بنية قاعدة البيانات الموجودة.
