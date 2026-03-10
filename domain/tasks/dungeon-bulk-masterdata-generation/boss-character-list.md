# ダンジョンボスキャラ選定リスト

> 作成日: 2026-03-10
> 調査元: `projects/glow-masterdata/MstEnemyCharacter.csv` × `MstEnemyCharacterI18n.csv`
> 条件: 各作品のURキャラから1体をボスキャラとして選定

---

## ボスキャラ一覧（15作品）

| # | series_id | 作品名 | MstEnemyCharacter.id | 日本語名 | 選定理由 |
|---|-----------|--------|----------------------|----------|---------|
| 1 | chi | チェンソーマン | `chara_chi_00002` | 悪魔が恐れる悪魔 チェンソーマン | 作品の象徴的フォーム、唯一のUR |
| 2 | dan | ダンダダン | `chara_dan_00002` | ターボババアの霊力 オカルン | 主人公の強力フォーム |
| 3 | gom | 姫様"拷問"の時間です | `chara_gom_00001` | 囚われの王女 姫様 | 唯一のUR |
| 4 | hut | ふつうの軽音部 | `chara_hut_00001` | ひたむきギタリスト 鳩野 ちひろ | 唯一のUR |
| 5 | jig | 地獄楽 | `chara_jig_00001` | がらんの画眉丸 | 主人公、作品の象徴的キャラ |
| 6 | kai | 怪獣８号 | `chara_kai_00002` | 隠された英雄の姿 怪獣８号 | モンスター形態がボスとして最適 |
| 7 | kim | 君のことが大大大大大好きな100人の彼女 | `chara_kim_00001` | 溢れる母性 花園 羽々里 | 唯一のUR |
| 8 | mag | 株式会社マジルミエ | `chara_mag_00201` | 絶対効率の体現者 土刃 メイ | 「絶対効率の体現者」がボスとして最適 |
| 9 | osh | 【推しの子】 | `chara_osh_00001` | B小町不動のセンター アイ | MstEnemyCharacterI18nに登録あり |
| 10 | spy | SPY×FAMILY | `chara_spy_00101` | <黄昏> ロイド | 主人公、作品の象徴的キャラ |
| 11 | sum | サマータイムレンダ | `chara_sum_00101` | 影のウシオ 小舟 潮 | 唯一のUR |
| 12 | sur | 魔都精兵のスレイブ | `chara_sur_00901` | 万物を統べる総組長 山城 恋 | 「万物を統べる総組長」がボスとして最適、FestivalUR |
| 13 | tak | タコピーの原罪 | `chara_tak_00001` | ハッピー星からの使者 タコピー | 唯一のUR |
| 14 | you | 幼稚園WARS | `chara_you_00001` | 元殺し屋の新人教諭 リタ | 唯一のUR |
| 15 | yuw | 2.5次元の誘惑 | `chara_yuw_00001` | リリエルに捧ぐ愛 天乃 リリサ | 主人公、作品の象徴的キャラ |

---

## IDのみ一覧（コピー用）

```
chara_chi_00002
chara_dan_00002
chara_gom_00001
chara_hut_00001
chara_jig_00001
chara_kai_00002
chara_kim_00001
chara_mag_00201
chara_osh_00001
chara_spy_00101
chara_sum_00101
chara_sur_00901
chara_tak_00001
chara_you_00001
chara_yuw_00001
```

---

## 備考

- MstEnemyCharacterI18nに登録されているJA名のみ対象として取得
- `chara_osh_00101`（osh PremiumUR Colorless Special）は i18n 未登録のため除外
- `chara_spy_00001`（spy PremiumUR Colorless Special）は i18n 未登録のため除外
- `chara_yuw_00102`（yuw FestivalUR Green Support）は MstEnemyCharacter に未登録のため除外
- 複数URがある作品は「ボスらしさ（称号・モンスター形態・役職）」を基準に1体を選定
