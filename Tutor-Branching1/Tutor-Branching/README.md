# Tutor Branching - إضافة إدارة الكليات والتصنيفات

الوصف
- إضافة بسيطة لإدارة الكليات والتصنيفات الفرعية وربطها بالكورسات في Tutor LMS.
- تم إعداد الملفات لتكون جاهزة للاستخدام مع WordPress وب-prefix wp_.

المتطلبات
- WordPress بنسخة حديثة
- صلاحيات administrator لإضافة/تعديل القوائم
- قاعدة بيانات ب-prefix wp_ (كما تم افتراضه)

هيكل الحزمة
- tutor-branching/
  - tutor-branching.php (الملف الرئيسي للإضافة)
  - uninstall.php
  - languages/
    - tutor-branching-ar_AR.po
    - tutor-branching-ar_AR.mo
  - templates/
    - admin/
      - tb-faculty-edit.php
      - tb-subcategory-edit.php
  - assets/
    - css/
      - tb-styles.css
  - includes/
    - class-tb-settings.php
    - class-tb-admin.php
    - class-tb-metaboxes.php
    - class-tb-user.php
  - migrations/
    - tb-migrations.sql
  - samples/
    - sample-terms.json
  - README.md