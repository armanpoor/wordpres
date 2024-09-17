function add_woocommerce_categories_to_menu() {
    $menu_name = 'primary'; // نام منویی که می‌خواهید دسته‌ها اضافه شوند
    $menu_exists = wp_get_nav_menu_object($menu_name);

    if ($menu_exists) {
        $menu_id = $menu_exists->term_id; // شناسه منو

        // پاک کردن آیتم‌های منوی فعلی
        $menu_items = wp_get_nav_menu_items($menu_id);
        if (!empty($menu_items)) {
            foreach ($menu_items as $menu_item) {
                wp_delete_post($menu_item->ID, true); // حذف آیتم‌های منوی فعلی
            }
        }

        // گرفتن همه دسته‌بندی‌های محصولات شامل والدین و فرزندان، حتی دسته‌های خالی
        $categories = get_terms('product_cat', array('hide_empty' => false, 'parent' => 0)); // فقط دسته‌های والد

        // افزودن دسته‌های والد به منو و فراخوانی تابع بازگشتی برای اضافه کردن زیرمجموعه‌ها
        foreach ($categories as $category) {
            add_category_to_menu($category, $menu_id, 0); // فراخوانی تابع بازگشتی برای والدین
        }
    }
}
add_action('init', 'add_woocommerce_categories_to_menu');

// تابع بازگشتی برای اضافه کردن دسته‌ها و زیرمجموعه‌های آن‌ها به منو
function add_category_to_menu($category, $menu_id, $parent_menu_item_id) {
    // اضافه کردن دسته‌بندی به منو
    $menu_item_id = wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title' => $category->name,
        'menu-item-object' => 'product_cat',
        'menu-item-object-id' => $category->term_id,
        'menu-item-type' => 'taxonomy',
        'menu-item-status' => 'publish',
        'menu-item-parent-id' => $parent_menu_item_id // مشخص کردن والد منو
    ));

    // گرفتن زیرمجموعه‌های این دسته
    $subcategories = get_terms('product_cat', array('hide_empty' => false, 'parent' => $category->term_id));

    // اگر زیرمجموعه‌ای وجود داشت، به صورت بازگشتی آن‌ها را اضافه کن
    if (!empty($subcategories)) {
        foreach ($subcategories as $subcategory) {
            add_category_to_menu($subcategory, $menu_id, $menu_item_id); // فراخوانی بازگشتی
        }
    }
}
