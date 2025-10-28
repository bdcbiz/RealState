#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
Units Import Script
Imports units from Excel file to database
"""

import pandas as pd
import pymysql
import sys
from datetime import datetime

# Configuration
EXCEL_FILE = '/var/www/realestate/units_data.xlsx'
DB_CONFIG = {
    'host': 'localhost',
    'user': 'laravel',
    'password': 'laravel123',
    'database': 'real_state',
    'charset': 'utf8mb4'
}

# Project name mapping to compound IDs
PROJECT_MAPPING = {
    'Club Views': 678,
    'Elan': 571,
    'ELAN': 571,
    'esse residence': 572,
    'Esse Residence': 572,
    'Origami': 577,
    'ORIGAMI': 577,
    'Rai': 719,
    'RAI': 719,
    'Rai Valleys': 575,
    'Rai Views': 574,
    'RAI VIEWS': 574,
    'Sheya Residence': 573,
    'Sheya residence': 573,
    'Talala': 796,
    'TALALA': 796,
    'The Butterfly': 601,
    'Zahw Assuit': 1362
}

def get_compound_id(project_name):
    """Get compound ID from project name"""
    return PROJECT_MAPPING.get(project_name.strip())

def parse_floor(floor_str):
    """Parse floor string to number"""
    if pd.isna(floor_str):
        return None

    floor_str = str(floor_str).strip().upper()

    # Floor mapping
    floor_map = {
        'G': 0,
        'GF': 0,
        'GROUND': 0,
        'V': -1,  # Villa/Variable
        'P': -2,  # Penthouse
        'R': -3,  # Roof
    }

    if floor_str in floor_map:
        return floor_map[floor_str]

    # Try to convert to integer
    try:
        return int(floor_str)
    except:
        return None

def main():
    print('=' * 80)
    print('Units Import Script')
    print('=' * 80)

    # Read Excel file
    print('\n[1/5] Reading Excel file...')
    try:
        df = pd.read_excel(EXCEL_FILE)
        print(f'   [OK] Loaded {len(df)} units from Excel')
    except Exception as e:
        print(f'   [ERROR] Error reading Excel: {e}')
        sys.exit(1)

    # Connect to database
    print('\n[2/5] Connecting to database...')
    try:
        conn = pymysql.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print('   [OK] Connected to database')
    except Exception as e:
        print(f'   [ERROR] Database connection failed: {e}')
        sys.exit(1)

    # Check for existing units
    print('\n[3/5] Checking existing units...')
    cursor.execute('SELECT COUNT(*) FROM units')
    existing_count = cursor.fetchone()[0]
    print(f'   [INFO] Found {existing_count} existing units')

    # Prepare insert statement
    insert_sql = """
    INSERT INTO units (
        compound_id,
        unit_name,
        unit_name_en,
        unit_name_ar,
        compound_name,
        usage_type,
        usage_type_en,
        usage_type_ar,
        built_up_area,
        garden_area,
        roof_area,
        floor_number,
        number_of_beds,
        normal_price,
        available,
        is_sold,
        status,
        created_at,
        updated_at
    ) VALUES (
        %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
        %s, %s, %s, %s, %s, %s, %s, %s, %s
    )
    """

    # Import units
    print('\n[4/5] Importing units...')

    stats = {
        'total': 0,
        'success': 0,
        'failed': 0,
        'skipped': 0,
        'errors': []
    }

    for index, row in df.iterrows():
        stats['total'] += 1

        try:
            # Get compound ID
            compound_id = get_compound_id(row['Project'])
            if not compound_id:
                stats['skipped'] += 1
                stats['errors'].append(f"Row {index+2}: Project '{row['Project']}' not found")
                continue

            # Prepare data
            unit_name = row['Unit Name']
            compound_name = row['Project']
            usage_type = row['Usage Type']
            built_up_area = float(row['BUA']) if pd.notna(row['BUA']) else None
            garden_area = float(row['Garden Area']) if pd.notna(row['Garden Area']) else None
            roof_area = float(row['Roof Area']) if pd.notna(row['Roof Area']) else None
            floor_number = parse_floor(row['Floor'])
            number_of_beds = int(row['No. of Bedrooms']) if pd.notna(row['No. of Bedrooms']) else None
            normal_price = float(row['Nominal Price']) if pd.notna(row['Nominal Price']) else None

            # Insert data
            cursor.execute(insert_sql, (
                compound_id,                  # compound_id
                unit_name,                    # unit_name
                unit_name,                    # unit_name_en
                unit_name,                    # unit_name_ar
                compound_name,                # compound_name
                usage_type,                   # usage_type
                usage_type,                   # usage_type_en
                usage_type,                   # usage_type_ar
                built_up_area,                # built_up_area
                garden_area,                  # garden_area
                roof_area,                    # roof_area
                floor_number,                 # floor_number
                number_of_beds,               # number_of_beds
                normal_price,                 # normal_price
                1,                            # available (default: yes)
                0,                            # is_sold (default: no)
                'in_progress',                # status
                datetime.now(),               # created_at
                datetime.now()                # updated_at
            ))

            stats['success'] += 1

            # Progress indicator
            if stats['total'] % 100 == 0:
                conn.commit()
                progress = (stats['total'] / len(df)) * 100
                print(f'   [PROGRESS] {stats["total"]}/{len(df)} ({progress:.1f}%) - '
                      f'{stats["success"]} success, {stats["skipped"]} skipped, {stats["failed"]} failed')

        except Exception as e:
            stats['failed'] += 1
            stats['errors'].append(f"Row {index+2}: {str(e)}")
            if stats['failed'] <= 10:  # Show first 10 errors
                print(f'   [ERROR] Error at row {index+2}: {str(e)}')

    # Commit final batch
    conn.commit()

    # Final report
    print('\n[5/5] Import completed!')
    print('=' * 80)
    print('Import Statistics:')
    print('=' * 80)
    print(f'  Total rows processed:    {stats["total"]}')
    print(f'  [OK] Successfully imported: {stats["success"]}')
    print(f'  [SKIP] Skipped:             {stats["skipped"]}')
    print(f'  [FAIL] Failed:              {stats["failed"]}')
    print('=' * 80)

    if stats['errors'] and len(stats['errors']) <= 20:
        print('\nErrors:')
        for error in stats['errors'][:20]:
            print(f'  - {error}')

    # Close connection
    cursor.close()
    conn.close()

    print('\n[SUCCESS] Import process completed!')

if __name__ == '__main__':
    main()
