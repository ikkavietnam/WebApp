#!/usr/bin/env bash
# Chuẩn bị các file API để chạy với api_gateway.php mới (gateway kiểm tra quyền TRƯỚC khi chạy file API).
# Xử lý 2 xung đột:
#   1) getSoftwareInfomation.php tự khai báo hàm TraKetQuaError(), trùng với Authorization.php
#      (gateway nạp Authorization.php trước) -> "Cannot redeclare TraKetQuaError".
#      Cách xử lý: đổi tên hàm cục bộ trong file đó thành gwNetError (chức năng giữ nguyên).
#   2) Nhiều file gọi  session_start();  không kiểm tra. Gateway đã mở session khi kiểm tra quyền,
#      nên gọi lần 2 sinh "Notice: Ignoring session_start()..." làm hỏng JSON khi bật hiển thị lỗi.
#      Cách xử lý: bọc bằng  if (session_status() === PHP_SESSION_NONE) { ... }
#
# Chạy TRONG container (Terminal của Container Manager), chỉ trên bản WebApp_Std:
#   bash /var/www/App/tools/prepare_gateway.sh            # chỉ liệt kê (dry-run)
#   bash /var/www/App/tools/prepare_gateway.sh --apply    # áp dụng
set -euo pipefail

API_DIR="${API_DIR:-/var/www/App/src/api}"
APPLY=0; [ "${1:-}" = "--apply" ] && APPLY=1

# ---------- 1) TraKetQuaError cục bộ ----------
NET="$API_DIR/modules/managerNET/getSoftwareInfomation.php"
echo "== [1] Hàm TraKetQuaError khai báo cục bộ"
if [ -f "$NET" ] && grep -qE 'function[[:space:]]+TraKetQuaError' "$NET"; then
    grep -nE 'TraKetQuaError' "$NET" | sed 's/^/   /'
    if [ $APPLY -eq 1 ]; then
        sed -i -E 's/\bTraKetQuaError\b/gwNetError/g' "$NET"
        echo "   -> Đã đổi tên thành gwNetError"
    fi
else
    echo "   (không có gì cần đổi)"
fi

# ---------- 2) session_start() chưa được bọc ----------
echo
echo "== [2] session_start(); chưa có điều kiện"
# in_awk: mode=list -> chỉ in file:dòng ; mode=fix -> ghi lại nội dung đã sửa
AWK_PROG='
{
    if ($0 ~ /^[[:space:]]*session_start\(\);/ && prev !~ /session_status/) {
        if (mode == "list") { print FILENAME ":" FNR ": " $0 }
        else { sub(/session_start\(\);/, "if (session_status() === PHP_SESSION_NONE) { session_start(); }") }
    }
    if (mode != "list") print
    if ($0 !~ /^[[:space:]]*$/) prev = $0
}'
COUNT=0
while IFS= read -r -d '' f; do
    OUT=$(awk -v mode=list "$AWK_PROG" "$f")
    if [ -n "$OUT" ]; then
        echo "$OUT" | sed 's/^/   /'
        COUNT=$((COUNT+1))
        if [ $APPLY -eq 1 ]; then
            awk -v mode=fix "$AWK_PROG" "$f" > "$f.tmp_gw" && cat "$f.tmp_gw" > "$f" && rm -f "$f.tmp_gw"
        fi
    fi
done < <(find "$API_DIR" -name '*.php' -print0)
echo "   Tổng: $COUNT file"

if [ $APPLY -eq 1 ]; then
    echo
    echo "== Kiểm tra cú pháp"
    find "$API_DIR" -name '*.php' -print0 | xargs -0 -n1 php -l 2>&1 | grep -v '^No syntax errors' || echo "   Không có lỗi cú pháp"
    echo "Hoàn tất."
else
    echo
    echo "(Dry-run) Chưa sửa gì. Thêm --apply để áp dụng."
fi
