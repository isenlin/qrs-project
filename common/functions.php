<?php
/**
 * 共用函式庫
 */

/**
 * 格式化金額顯示
 */
function format_currency($amount) {
    return 'NT$ ' . number_format($amount);
}

/**
 * 取得星期幾的中文名稱
 */
function get_chinese_weekday($date) {
    $weekdays = ['日', '一', '二', '三', '四', '五', '六'];
    $day_of_week = date('w', strtotime($date));
    return $weekdays[$day_of_week];
}

/**
 * 計算日期範圍內的天數
 */
function get_date_range_days($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    return $interval->days + 1; // 包含起訖日
}

/**
 * 產生日期選項
 */
function generate_date_options($start_year = 2023, $end_year = 2026) {
    $options = [];
    
    for ($year = $start_year; $year <= $end_year; $year++) {
        for ($month = 1; $month <= 12; $month++) {
            $month_str = sprintf('%04d-%02d', $year, $month);
            $options[$month_str] = "{$year}年{$month}月";
        }
    }
    
    return $options;
}

/**
 * 檢查日期是否在範圍內
 */
function is_date_in_range($date, $start_date, $end_date) {
    $timestamp = strtotime($date);
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    
    return $timestamp >= $start_timestamp && $timestamp <= $end_timestamp;
}

/**
 * 取得店櫃的業務人員
 */
function get_store_sales_person($store_code, $stores_data) {
    foreach ($stores_data as $store) {
        if ($store['code'] === $store_code) {
            return $store['sales_person'];
        }
    }
    return '未知';
}

/**
 * 取得店櫃的督導
 */
function get_store_supervisor($store_code, $stores_data) {
    foreach ($stores_data as $store) {
        if ($store['code'] === $store_code) {
            return $store['supervisor'];
        }
    }
    return '未知';
}

/**
 * 計算業績統計
 */
function calculate_sales_stats($sales_data, $user_stores, $start_date, $end_date) {
    $stats = [
        'total_sales' => 0,
        'total_stores' => count($user_stores),
        'avg_daily_sales' => 0,
        'best_day' => ['date' => '', 'sales' => 0],
        'worst_day' => ['date' => '', 'sales' => PHP_INT_MAX],
        'store_stats' => []
    ];
    
    // 初始化店櫃統計
    foreach ($user_stores as $store_code => $store) {
        $stats['store_stats'][$store_code] = [
            'total' => 0,
            'avg' => 0,
            'name' => $store['name']
        ];
    }
    
    // 計算每日統計
    $current_date = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    $days_count = 0;
    
    while ($current_date <= $end_date_obj) {
        $date_str = $current_date->format('Y-m-d');
        $daily_total = 0;
        
        foreach ($user_stores as $store_code => $store) {
            $sales = 0;
            if (isset($sales_data[$date_str][$store_code])) {
                $sales = $sales_data[$date_str][$store_code]['amount'] ?? 0;
            }
            
            $stats['store_stats'][$store_code]['total'] += $sales;
            $daily_total += $sales;
        }
        
        $stats['total_sales'] += $daily_total;
        
        // 更新最佳/最差日
        if ($daily_total > $stats['best_day']['sales']) {
            $stats['best_day'] = [
                'date' => $date_str,
                'sales' => $daily_total
            ];
        }
        
        if ($daily_total < $stats['worst_day']['sales']) {
            $stats['worst_day'] = [
                'date' => $date_str,
                'sales' => $daily_total
            ];
        }
        
        $days_count++;
        $current_date->modify('+1 day');
    }
    
    // 計算平均
    if ($days_count > 0) {
        $stats['avg_daily_sales'] = $stats['total_sales'] / $days_count;
        
        foreach ($stats['store_stats'] as $store_code => &$store_stat) {
            $store_stat['avg'] = $store_stat['total'] / $days_count;
        }
    }
    
    // 如果最差日的銷售額還是初始值，設為0
    if ($stats['worst_day']['sales'] === PHP_INT_MAX) {
        $stats['worst_day']['sales'] = 0;
    }
    
    return $stats;
}

/**
 * 產生 CSV 內容
 */
function generate_csv_content($headers, $data) {
    $output = '';
    
    // 加入 BOM 讓 Excel 正確顯示 UTF-8
    $output .= "\xEF\xBB\xBF";
    
    // 加入標題
    $output .= implode(',', $headers) . "\n";
    
    // 加入資料
    foreach ($data as $row) {
        $output .= implode(',', $row) . "\n";
    }
    
    return $output;
}

/**
 * 安全輸出 HTML
 */
function safe_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 取得角色中文名稱
 */
function get_role_chinese_name($role) {
    $roles = [
        'admin' => '系統管理員',
        'supervisor' => '督導',
        'sales' => '業務',
        'store' => '店櫃'
    ];
    
    return $roles[$role] ?? $role;
}

/**
 * 產生分頁連結
 */
function generate_pagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $pagination = '<div class="pagination">';
    
    // 上一頁
    if ($current_page > 1) {
        $pagination .= '<a href="' . $base_url . '&page=' . ($current_page - 1) . '" class="page-link">&laquo; 上一頁</a>';
    }
    
    // 頁碼
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $pagination .= '<span class="page-link current">' . $i . '</span>';
        } else {
            $pagination .= '<a href="' . $base_url . '&page=' . $i . '" class="page-link">' . $i . '</a>';
        }
    }
    
    // 下一頁
    if ($current_page < $total_pages) {
        $pagination .= '<a href="' . $base_url . '&page=' . ($current_page + 1) . '" class="page-link">下一頁 &raquo;</a>';
    }
    
    $pagination .= '</div>';
    
    return $pagination;
}