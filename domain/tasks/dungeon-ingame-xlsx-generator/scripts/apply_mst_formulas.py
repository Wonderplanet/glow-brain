"""
MstPage/MstKomaLine投入用の汎用セル式を全対象シートに適用するスクリプト。

シートグループ別の固定行番号:
  GROUP_A (ストーリー全話・チャレンジ1-2話・降臨バトル1話): er=13, rr=13, kr_start=31
  GROUP_B (チャレンジ3-4話・高難度全話):                   er=13, rr=13, kr_start=32
  GROUP_C (通常ブロック・ボスブロック):                     er=14, rr=14, kr_start=19

対象:
- 【チャレンジ】死罪人と首切り役人設計.xlsx: 1話〜4話
- 【降臨バトル】まるで 悪夢を見ているようだ_地獄楽.xlsx: 1話
- 【高難度】手負いの獣は恐ろしいぞ.xlsx: 1話〜3話
- 検証用コピー_未完成_限界チャレンジ(VD)_アウトゲーム関連.xlsx: 通常ブロック, ボスブロック
- 検証用コピー_【ストーリー】必ず生きて帰る.xlsx: 1話〜6話（既存式を更新）
"""
import openpyxl
from openpyxl.utils import column_index_from_string
import shutil
import os

TASK_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
RAW_DIR = os.path.join(TASK_DIR, 'raw')
WORK_DIR = os.path.join(TASK_DIR, 'work')

# MstKomaLineのカラムヘッダー（EB〜FRの43列）
KOMA_LINE_HEADERS = [
    'ENABLE', 'id', 'mst_page_id', 'row', 'height', 'koma_line_layout_asset_key',
    'koma1_asset_key', 'koma1_width', 'koma1_back_ground_offset',
    'koma1_effect_type', 'koma1_effect_parameter1', 'koma1_effect_parameter2',
    'koma1_effect_target_side', 'koma1_effect_target_colors', 'koma1_effect_target_roles',
    'koma2_asset_key', 'koma2_width', 'koma2_back_ground_offset',
    'koma2_effect_type', 'koma2_effect_parameter1', 'koma2_effect_parameter2',
    'koma2_effect_target_side', 'koma2_effect_target_colors', 'koma2_effect_target_roles',
    'koma3_asset_key', 'koma3_width', 'koma3_back_ground_offset',
    'koma3_effect_type', 'koma3_effect_parameter1', 'koma3_effect_parameter2',
    'koma3_effect_target_side', 'koma3_effect_target_colors', 'koma3_effect_target_roles',
    'koma4_asset_key', 'koma4_width', 'koma4_back_ground_offset',
    'koma4_effect_type', 'koma4_effect_parameter1', 'koma4_effect_parameter2',
    'koma4_effect_target_side', 'koma4_effect_target_colors', 'koma4_effect_target_roles',
    'release_key',
]

EB_COL = column_index_from_string('EB')

# シートグループ別パラメータ
# er: page_id のExcel行番号（E列）
# rr: release_key のExcel行番号（N列）
# kr_start: コマ設計データ1行目のExcel行番号（D列〜）
SHEET_PARAMS = {
    'GROUP_A': {'er': 13, 'rr': 13, 'kr_start': 31},
    'GROUP_B': {'er': 13, 'rr': 13, 'kr_start': 32},
    'GROUP_C': {'er': 14, 'rr': 14, 'kr_start': 19},
}


def get_sheet_params(filename, sheet_name):
    """ファイル名・シート名からシートグループパラメータを返す"""
    if 'アウトゲーム' in filename:
        return SHEET_PARAMS['GROUP_C']
    elif 'チャレンジ' in filename and sheet_name in ('3話', '4話'):
        return SHEET_PARAMS['GROUP_B']
    elif '高難度' in filename:
        return SHEET_PARAMS['GROUP_B']
    else:
        return SHEET_PARAMS['GROUP_A']


def gen_page_formulas(er_row, rr_row):
    """MstPage用の式（29行目）を生成"""
    return {
        'EB': f'=IF(E${er_row}="","","e")',
        'EC': f'=IF(E${er_row}="","",E${er_row})',
        'ED': f'=IF(E${er_row}="","",TEXT(N${rr_row},"0"))',
    }


def gen_komaline_formulas(row_num, er_row, rr_row, kr_start):
    """MstKomaLine用の式を行番号（1-5）ごとに生成"""
    kr = kr_start + row_num - 1  # このコマ行のExcel行番号

    def ke(col):
        """koma存在チェック式（コマなし/none/errorを除外）"""
        return (f'AND({col}${kr}<>"",{col}${kr}<>"none",'
                f'{col}${kr}<>"error",NOT(ISBLANK({col}${kr})))')

    return {
        # --- 基本フィールド ---
        'EB': f'=IF(D${kr}="","","e")',
        'EC': f'=IF(D${kr}="","",E${er_row}&"_{row_num}")',
        'ED': f'=IF(D${kr}="","",E${er_row})',
        'EE': f'=IF(D${kr}="","",{row_num})',
        'EF': f'=IF(D${kr}="","",H${kr})',
        'EG': f'=IF(D${kr}="","",INT(D${kr}))',
        # --- koma1 ---
        'EH': f'=IF(D${kr}="","",IFERROR(R${kr},""))',
        'EI': f'=IF(D${kr}="","",J${kr})',
        'EJ': f'=IF(D${kr}="","",IF(J${kr}=1,0,-1))',
        'EK': f'=IF(D${kr}="","",IF(OR(U${kr}="",ISBLANK(U${kr})),"None",U${kr}))',
        'EL': f'=IF(D${kr}="","",IF(OR(U${kr}="",ISBLANK(U${kr})),0,Z${kr}))',
        'EM': f'=IF(D${kr}="","",IF(OR(U${kr}="",ISBLANK(U${kr})),0,AB${kr}))',
        'EN': f'=IF(D${kr}="","","All")',
        'EO': f'=IF(D${kr}="","","All")',
        'EP': f'=IF(D${kr}="","","All")',
        # --- koma2 ---
        'EQ': f'=IF(D${kr}="","",IF({ke("L")},IFERROR(R${kr},""),""))',
        'ER': f'=IF(D${kr}="","",IF({ke("L")},L${kr},""))',
        'ES': f'=IF(D${kr}="","",IF({ke("L")},IF(L${kr}=1,0,-1),"__NULL__"))',
        'ET': f'=IF(D${kr}="","",IF({ke("L")},IF(OR(AD${kr}="",ISBLANK(AD${kr})),"None",AD${kr}),"None"))',
        'EU': f'=IF(D${kr}="","",IF({ke("L")},IF(OR(AD${kr}="",ISBLANK(AD${kr})),0,AI${kr}),""))',
        'EV': f'=IF(D${kr}="","",IF({ke("L")},IF(OR(AD${kr}="",ISBLANK(AD${kr})),0,AK${kr}),""))',
        'EW': f'=IF(D${kr}="","",IF({ke("L")},"All","__NULL__"))',
        'EX': f'=IF(D${kr}="","",IF({ke("L")},"All",""))',
        'EY': f'=IF(D${kr}="","",IF({ke("L")},"All",""))',
        # --- koma3 ---
        'EZ': f'=IF(D${kr}="","",IF({ke("N")},IFERROR(R${kr},""),""))',
        'FA': f'=IF(D${kr}="","",IF({ke("N")},N${kr},""))',
        'FB': f'=IF(D${kr}="","",IF({ke("N")},IF(N${kr}=1,0,-1),"__NULL__"))',
        'FC': f'=IF(D${kr}="","",IF({ke("N")},IF(OR(AM${kr}="",ISBLANK(AM${kr})),"None",AM${kr}),"None"))',
        'FD': f'=IF(D${kr}="","",IF({ke("N")},IF(OR(AM${kr}="",ISBLANK(AM${kr})),0,AR${kr}),""))',
        'FE': f'=IF(D${kr}="","",IF({ke("N")},IF(OR(AM${kr}="",ISBLANK(AM${kr})),0,AT${kr}),""))',
        'FF': f'=IF(D${kr}="","",IF({ke("N")},"All","__NULL__"))',
        'FG': f'=IF(D${kr}="","",IF({ke("N")},"All",""))',
        'FH': f'=IF(D${kr}="","",IF({ke("N")},"All",""))',
        # --- koma4 ---
        'FI': f'=IF(D${kr}="","",IF({ke("P")},IFERROR(R${kr},""),""))',
        'FJ': f'=IF(D${kr}="","",IF({ke("P")},P${kr},""))',
        'FK': f'=IF(D${kr}="","",IF({ke("P")},IF(P${kr}=1,0,-1),"__NULL__"))',
        'FL': f'=IF(D${kr}="","",IF({ke("P")},IF(OR(AV${kr}="",ISBLANK(AV${kr})),"None",AV${kr}),"None"))',
        'FM': f'=IF(D${kr}="","",IF({ke("P")},IF(OR(AV${kr}="",ISBLANK(AV${kr})),0,BA${kr}),""))',
        'FN': f'=IF(D${kr}="","",IF({ke("P")},IF(OR(AV${kr}="",ISBLANK(AV${kr})),0,BC${kr}),""))',
        'FO': f'=IF(D${kr}="","",IF({ke("P")},"All","__NULL__"))',
        'FP': f'=IF(D${kr}="","",IF({ke("P")},"All",""))',
        'FQ': f'=IF(D${kr}="","",IF({ke("P")},"All",""))',
        # --- release_key ---
        'FR': f'=IF(D${kr}="","",TEXT(N${rr_row},"0"))',
    }


def apply_formulas_to_sheet(ws, er_row, rr_row, kr_start):
    """指定シートに★MstPage/MstKomaLine投入用ヘッダー・式を書き込む"""
    # EA28: ★MstPage投入用ヘッダー
    ws['EA28'] = '★MstPage投入用▶'
    ws['EB28'] = 'ENABLE'
    ws['EC28'] = 'id'
    ws['ED28'] = 'release_key'

    # EB29〜ED29: MstPageデータ式
    for col_letter, formula in gen_page_formulas(er_row, rr_row).items():
        ws[f'{col_letter}29'] = formula

    # EA30: ★MstKomaLine投入用ヘッダー
    ws['EA30'] = '★MstKomaLine投入用▶'

    # EB30〜FR30: カラムヘッダー
    for i, header in enumerate(KOMA_LINE_HEADERS):
        ws.cell(row=30, column=EB_COL + i, value=header)

    # EB31〜FR35: 1〜5行目データ式
    for row_num in range(1, 6):
        excel_row = 30 + row_num
        for col_letter, formula in gen_komaline_formulas(row_num, er_row, rr_row, kr_start).items():
            ws.cell(row=excel_row, column=column_index_from_string(col_letter),
                    value=formula)

    print(f'  ✓ {ws.title}')


def ensure_work_copy(raw_filename, work_filename=None):
    if work_filename is None:
        work_filename = raw_filename
    raw_path = os.path.join(RAW_DIR, raw_filename)
    work_path = os.path.join(WORK_DIR, work_filename)
    if not os.path.exists(work_path):
        shutil.copy2(raw_path, work_path)
        print(f'  rawからworkにコピー: {work_filename}')
    return work_path


def process_file(work_path, target_sheets):
    filename = os.path.basename(work_path)
    print(f'\n[{filename}]')
    wb = openpyxl.load_workbook(work_path)
    for sheet_name in target_sheets:
        if sheet_name in wb.sheetnames:
            params = get_sheet_params(filename, sheet_name)
            apply_formulas_to_sheet(
                wb[sheet_name],
                params['er'], params['rr'], params['kr_start'],
            )
        else:
            print(f'  ⚠ シートが見つかりません: {sheet_name}')
    wb.save(work_path)
    print(f'  → 保存完了')


def main():
    os.makedirs(WORK_DIR, exist_ok=True)

    targets = [
        ('【チャレンジ】死罪人と首切り役人設計.xlsx',
         '【チャレンジ】死罪人と首切り役人設計.xlsx',
         ['1話', '2話', '3話', '4話']),
        ('【降臨バトル】まるで 悪夢を見ているようだ_地獄楽.xlsx',
         '【降臨バトル】まるで 悪夢を見ているようだ_地獄楽.xlsx',
         ['1話']),
        ('【高難度】手負いの獣は恐ろしいぞ.xlsx',
         '【高難度】手負いの獣は恐ろしいぞ.xlsx',
         ['1話', '2話', '3話']),
        ('検証用コピー_未完成_限界チャレンジ(VD)_アウトゲーム関連.xlsx',
         '検証用コピー_未完成_限界チャレンジ(VD)_アウトゲーム関連.xlsx',
         ['通常ブロック', 'ボスブロック']),
        # ストーリーはwork/に既存 → 式を上書き更新
        ('検証用コピー_【ストーリー】必ず生きて帰る.xlsx',
         '検証用コピー_【ストーリー】必ず生きて帰る.xlsx',
         ['1話', '2話', '3話', '4話', '5話', '6話']),
    ]

    for raw_name, work_name, sheets in targets:
        work_path = ensure_work_copy(raw_name, work_name)
        process_file(work_path, sheets)

    print('\n=== 全ファイル処理完了 ===')


if __name__ == '__main__':
    main()
