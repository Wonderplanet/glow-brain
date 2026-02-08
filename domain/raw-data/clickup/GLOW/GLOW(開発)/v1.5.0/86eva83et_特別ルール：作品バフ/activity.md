# 特別ルール：作品バフ - Activity

**コメント #1** by Kazuya Adachi 
(2026-01-26 15:02:32)

1/26 安達
作品バフギミックについて、作品IDの対象/対象外の動作、
他バフとの組み合わせ等に問題がないことを確認しました

※検証内容の詳細については下記項目書を参照
【v1.5.0】作品バフギミック/スキル検証_項目書＞作品バフギミック
docs.google.com/spreadsheets/d/1FxxZTCcO89Y2vWmScd9DjuAfLzOS4IHC-oZWAkQdlkU/edit?gid=393523044#gid=393523044 
  

確認ビルド：iOS#37(580)、AOS#1180(581)
確認環境：dev-qa2
確認端末：iPhoneSE3(iOS:18.6)
　　　　　ZenFone6(AOS:9.0)



**コメント #2** by wataru shigeyama 
(2026-01-09 16:53:28)

iOS : 1.5.0 (539)
Android : 1.5.0 (542)

develop環境にデータ設定行っています
ステージ1
→属性制限

ランクマッチ
→全てのキャラ：召喚クールタイム -1秒
→全てのキャラ：必殺ワザクールタイム -1秒
→ダンダダン作品キャラ：攻撃力 50%UP



**コメント #3** by wataru shigeyama 
(2026-01-09 16:32:01)

undefined実装後確認シート



**コメント #4** by Kenta Inui 
(2026-01-09 11:37:28)

作品バフスキル（スキルの対象設定箇所を増やすのみ）
docs.google.com/presentation/d/1v5w0gN0OJxczmtDa2MUVWqXSXFlMS-wkffQjl_i3FPY/edit?slide=id.g3b0ef0bd6b5_0_23#slide=id.g3b0ef0bd6b5_0_23 
 
に関しては下記タスクに分離
86ew4yy7u



**コメント #5** by Ryosuke Goto 
(2025-12-16 20:25:46)

要件仕様書は下記
作品バフギミック（設定マスターは特別ルールの設定ている場所を使用する）
docs.google.com/presentation/d/1v5w0gN0OJxczmtDa2MUVWqXSXFlMS-wkffQjl_i3FPY/edit?slide=id.g3a7097fed0f_0_251#slide=id.g3a7097fed0f_0_251 
 

作品バフスキル（スキルの対象設定箇所を増やすのみ）
docs.google.com/presentation/d/1v5w0gN0OJxczmtDa2MUVWqXSXFlMS-wkffQjl_i3FPY/edit?slide=id.g3b0ef0bd6b5_0_23#slide=id.g3b0ef0bd6b5_0_23 
 



