#!/bin/bash

BASE_URL="https://aqar.bdcbiz.com/api"

echo "=== اختبار API Endpoints ==="
echo ""

# Test 1: Public endpoints
echo "1. اختبار Endpoint عام (Companies):"
curl -s "$BASE_URL/companies" | jq -r '.success, .count' 2>/dev/null
echo ""

# Test 2: Compounds
echo "2. اختبار Compounds:"
curl -s "$BASE_URL/compounds" | jq -r '.success, .total' 2>/dev/null
echo ""

# Test 3: Search (requires auth)
echo "3. اختبار Search (بدون توثيق):"
curl -s "$BASE_URL/search?search=villa" | jq -r '.message // .status' 2>/dev/null
echo ""

# Test 4: Filter units (requires auth)
echo "4. اختبار Filter Units (بدون توثيق):"
curl -s "$BASE_URL/filter-units?unit_type=villa" | jq -r '.message // .success' 2>/dev/null
echo ""

echo "=== ملاحظة: endpoints البحث والفلترة تحتاج Bearer Token ==="
