using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Debugs.Command.Domains.UseCase;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Views.DebugMstUnitStatusView
{
    public class DebugMstUnitStatusView : UIView
    {
        [Header("最上部")]
        [SerializeField] ItemIconComponent _itemIcon;
        [SerializeField] CharacterIconComponent _characterIcon;
        [SerializeField] Text _seriesText;
        [SerializeField] Text _nameText;

        [Header("キャラ情報・攻撃ステータス")]
        [SerializeField] Image _attackStatusButtonImage;
        [SerializeField] ScrollRect _attackStatusScrollRect;
        [SerializeField] Text _attackStatusText;

        [Header("レベル別基礎ステータス")]
        [SerializeField] Image _levelStatusButtonImage;
        [SerializeField] ScrollRect _levelStatusScrollRect;
        [SerializeField] RectTransform _levelStatusContentRect;

        [Header("スペシャルユニットレベル/グレード別必殺ワザ情報")]
        [SerializeField] Image _specialUnitAttackButtonImage;
        [SerializeField] GameObject _specialUnitAttackAreaObj;
        [SerializeField] RectTransform _specialUnitAttackContentRect;
        [SerializeField] Dropdown _gradeFilterDropdown;
        [SerializeField] Dropdown _rankFilterDropdown;
        [SerializeField] Dropdown _upperLevelFilterDropdown;
        [SerializeField] Dropdown _underLevelFilterDropdown;
        UnitGrade _currentGrade = UnitGrade.Empty;
        UnitRank _currentRank = UnitRank.Empty;
        UnitLevel _currentUpperLevel = UnitLevel.Empty;
        UnitLevel _currentUnderLevel = UnitLevel.Empty;
        List<DebugMstUnitLevelStatusCell> _instancedLevelCells = new List<DebugMstUnitLevelStatusCell>();

        [Header("使いまわしCell")]
        [SerializeField] DebugMstUnitLevelStatusCell _levelCell;

        public void SetUpTopArea(
            ItemIconAssetPath itemIconAssetPath,
            CharacterIconViewModel characterIconViewModel,
            CharacterName charaName,
            SeriesName seriesName)
        {
            _itemIcon.Setup(itemIconAssetPath, characterIconViewModel.Rarity, ItemAmount.Empty);
            _characterIcon.Setup(characterIconViewModel);
            _seriesText.text = seriesName.Value;
            _nameText.text = charaName.Value;
        }

        // タブ切り替え
        public void ShowTextArea(DebugMstUnitStatusType attackStatus)
        {
            _attackStatusScrollRect.gameObject
                .SetActive(attackStatus == DebugMstUnitStatusType.AttackStatus);
            _levelStatusScrollRect.gameObject
                .SetActive(attackStatus == DebugMstUnitStatusType.LevelStatus);
            _specialUnitAttackAreaObj.gameObject
                .SetActive(attackStatus == DebugMstUnitStatusType.SpecialUnitSpecialAttackStatus);

            _attackStatusButtonImage.color = attackStatus == DebugMstUnitStatusType.AttackStatus
                ? new Color32(44, 64, 109, 255)
                : Color.gray;
            _levelStatusButtonImage.color = attackStatus == DebugMstUnitStatusType.LevelStatus
                ? new Color32(44, 64, 109, 255)
                : Color.gray;
            _specialUnitAttackButtonImage.color = attackStatus == DebugMstUnitStatusType.SpecialUnitSpecialAttackStatus
                ? new Color32(44, 64, 109, 255)
                : Color.gray;
        }

        public void SetUpAttackStatusText(DebugMstUnitAttackStatusUseCaseModel model)
        {
            _attackStatusText.text = "";
            //基本情報
            _attackStatusText.text = "====基本情報====\n";
            _attackStatusText.text += $"ユニットID: {model.AtBase.MstCharacterId.Value}\n" +
                                      $"アセットキー: {model.AtBase.AssetKey.Value}\n" +
                                      $"ユニット名: {model.AtBase.CharacterName.Value}\n" +
                                      $"シリーズ名: {model.AtBase.SeriesName.Value}\n" +
                                      $"レアリティ: {model.AtBase.Rarity}\n" +
                                      $"ユニットカラー: {model.AtBase.CharacterColor}\n" +
                                      $"ユニットラベル: {model.AtBase.UnitLabel}\n" +
                                      $"ワンポイント: {model.AtBase.UnitInfoDetail.Value}\n"
                ;

            // ピーズ情報
            _attackStatusText.text += "\n====ピース情報====\n";
            _attackStatusText.text += $"アイテム名: {model.AtPiece.ItemName.Value}\n" +
                                      $"アセットキー: {model.AtPiece.ItemIconAssetPath.Value}\n"
                ;

            // 特性情報
            _attackStatusText.text += "\n====特性情報====\n";
            foreach (var ability in model.AtAbility.Elements)
            {
                _attackStatusText.text += $"開放ランク: {ability.UnlockUnitRank.Value}\n" +
                                          $"特性開放条件：強化可能Lv.をLv.{ability.UnlockUnitLevel.Value}まで開放\n" +
                                          $"特性説明: {ability.Description}\n" +
                                          $"-----------------\n";
            }

            //バトル情報
            _attackStatusText.text += "\n====バトル基本情報====\n";
            _attackStatusText.text += $"ロール: {model.AtBattleBase.CharacterUnitRoleType}\n" +
                                      $"射程: {model.AtBattleBase.CharacterAttackRangeType}\n" +
                                      $"召喚コスト: {model.AtBattleBase.SummonCost}\n" +
                                      $"召喚クールタイム: {model.AtBattleBase.SummonCoolTime.Value}\n" +
                                      $"最小HP: {model.AtBattleBase.MinHp}\n" +
                                      $"最大HP: {model.AtBattleBase.MaxHp}\n" +
                                      $"ノックバックカウント: {model.AtBattleBase.KnockBackCount.Value}\n" +
                                      $"移動速度: {model.AtBattleBase.UnitMoveSpeed.Value}\n" +
                                      $"会敵距離(WellDistance): {model.AtBattleBase.WellDistance.Value}\n" +
                                      $"最小攻撃力: {model.AtBattleBase.MinAttackPower}\n" +
                                      $"最大攻撃力: {model.AtBattleBase.MaxAttackPower}\n";

            _attackStatusText.text += "\n====RUSHダメージUP倍率(%)====\n";
            foreach (var e in model.AtSpecialUnitRushUp.Elements)
            {
                _attackStatusText.text += $"ランク: {e.UnitRank.Value} | " +
                                          $"UP倍率: {e.AttackPower.ToRushPercentageM().ToStringF2()}\n";
            }

            //通常攻撃基本情報
            _attackStatusText.text += "\n====通常攻撃基本情報====\n";
            _attackStatusText.text += $"吹き出しアセットキー: {model.AtNormalAttack.ToOnomatopoeiaString()}\n" +
                                      $"クールタイム: {model.AtNormalAttack.NormalAttackCoolTime.Value}\n" +
                                      $"射程タイプ: {model.AtNormalAttack.CharacterAttackRangeType}\n" +
                                      $"特攻属性: {model.AtNormalAttack.ToKillerColorsString()}\n" +
                                      $"特攻倍率: {model.AtNormalAttack.ToKillerPercentageString()}\n"
                ;

            //通常攻撃当たり判定情報
            _attackStatusText.text += "----通常攻撃当たり判定情報----\n";
            for (var i = 0; i < model.AtNormalAttack.AtAttackElementStatus.Count; i++)
            {
                var hit = model.AtNormalAttack.AtAttackElementStatus[i];
                _attackStatusText.text += $"{i + 1}.\n" +
                                          $"モーション全体F: {hit.ActionDuration.Value}\n" +
                                          $"攻撃発生F: {hit.AttackFrames.Value}({hit.ToAttackFramesToSec()}s)\n" +
                                          $"攻撃ヒットF: {hit.AttackHitFrames.Value}({hit.ToAttackHitFramesToSec()}s)\n" +
                                          $"攻撃距離: {hit.AttackRange}\n" +
                                          $"攻撃範囲(最大対象数): {hit.MaxTargetCount.Value}\n" +
                                          $"攻撃倍率(100=×1): {hit.PowerParameter}\n" +
                                          $"RangeStartType: {hit.RangeStartType}\n" +
                                          $"RangeStartParameter: {hit.RangeStartParameter.Value}\n" +
                                          $"RangeEndType: {hit.RangeEndType}\n" +
                                          $"RangeEndParameter: {hit.RangeEndParameter.Value}\n" +
                                          $"攻撃対象: {hit.AttackTarget}\n" +
                                          $"攻撃属性: {hit.ToTargetColorsString()}\n" +
                                          $"対象ロール: {hit.ToTargetRolesString()}\n" +
                                          $". . . . . . . . .\n";
            }

            // 必殺ワザ基本情報
            _attackStatusText.text += "\n====必殺ワザ基本情報====\n";
            _attackStatusText.text += $"初回クールタイム: {model.AtSpecialAttack.SpecialAttackInitialCoolTime.Value.Value}({model.AtSpecialAttack.SpecialAttackInitialCoolTime.ToCoolTimeString()})\n" +
                                      $"2回目以降クールタイム: {model.AtSpecialAttack.SpecialAttackCoolTime.Value.Value}({model.AtSpecialAttack.SpecialAttackCoolTime.ToCoolTimeString()})\n" +
                                      $"射程タイプ: {model.AtSpecialAttack.CharacterAttackRangeType}\n" +
                                      $"名前: {model.AtSpecialAttack.SpecialAttackName.Value}\n" +
                                      $"説明文: {model.AtSpecialAttack.SpecialAttackInfoDescription.Value}\n"
                ;
            //グレード別必殺ワザ情報
            _attackStatusText.text += "----グレード別必殺ワザ情報----\n";
            for (var i = 0; i < model.AtSpecialAttack.Elements.Count; i++)
            {
                var specialAttack = model.AtSpecialAttack.Elements[i];
                _attackStatusText.text += $"Grade: {specialAttack.UnitGrade.Value}.\n" +
                                          $"吹き出しアセットキー: {specialAttack.ToOnomatopoeiaString()}\n" +
                                          $"特攻属性: {specialAttack.ToKillerColorsString()}\n" +
                                          $"特攻倍率: {specialAttack.ToKillerPercentageString()}\n" +
                                          $"説明文: {specialAttack.SpecialAttackInfoDescription.Value}\n" +
                                          $". . . . . . . . .\n";
                // 当たり判定情報
                _attackStatusText.text += "必殺ワザ当たり判定情報\n";
                for (int j = 0; j < specialAttack.AtAttackElementStatus.Count; j++)
                {
                    var hit = specialAttack.AtAttackElementStatus[j];
                    _attackStatusText.text += $"{specialAttack.UnitGrade.Value}-{j + 1}.\n" +
                                              $"モーション全体F: {hit.ActionDuration.Value}\n" +
                                              $"攻撃発生F: {hit.AttackFrames.Value}({hit.ToAttackFramesToSec()}s)\n" +
                                              $"攻撃ヒットF: {hit.AttackHitFrames.Value}({hit.ToAttackHitFramesToSec()}s)\n" +
                                              $"攻撃距離: {hit.AttackRange}\n" +
                                              $"攻撃範囲(最大対象数): {hit.MaxTargetCount.Value}\n" +
                                              $"攻撃倍率(100=×1): {hit.PowerParameter}\n" +
                                              $"RangeStartType: {hit.RangeStartType}\n" +
                                              $"RangeStartParameter: {hit.RangeStartParameter.Value}\n" +
                                              $"RangeEndType: {hit.RangeEndType}\n" +
                                              $"RangeEndParameter: {hit.RangeEndParameter.Value}\n" +
                                              $"攻撃対象: {hit.AttackTarget}\n" +
                                              $"攻撃属性: {hit.ToTargetColorsString()}\n" +
                                              $"対象ロール: {hit.ToTargetRolesString()}\n" +
                                              $"攻撃タイプ: {hit.ToHitTypeString()}\n" +
                                              $"付与効果: {hit.StateEffectType}\n" +
                                              $"付与効果発動確率: {hit.EffectiveProbability.Value}\n" +
                                              $"付与効果時間: {hit.EffectiveDuration.Value}\n" +
                                              $"付与効果効果値: {hit.StateEffectParameter.Value}\n" +
                                              $". . . . . . . . .\n";
                }
            }
        }


        public void SetUpLevelStatusText(IReadOnlyList<DebugMstLevelStatusUseCaseModel> modelLevelStatuses)
        {
            // 表示イメージ
            // "Lv    |Rank  |基礎HP|基礎ATK|星1HP |星1ATK|星2HP |星3HP  |星4HP |星5HP |星2ATK|星3ATK|星4ATK|星5ATK|\n";
            // "------|------|------|------|------|------|------|------|------|------|------|------|------|------|------|\n";

            int index = 0;
            foreach (var model in modelLevelStatuses)
            {
                var instanced = InstanceDebugMstUnitLevelStatusCell(_levelStatusContentRect);
                var instancedLevelText = instanced.LevelText;
                instancedLevelText.text = "";

                var text = "| Lv";
                text += string.Format(
                    "{0,5} |",
                    model.Level.Value
                    );

                text += "| Rank";
                text += string.Format(
                    "{0,5} |",
                    model.Rank.Value
                );

                text += "| HP/ATK:基礎|";
                text += string.Format(
                    "{0,6} |{1,6} |",
                    model.BaseHP.Value,
                    model.BaseAttackPower.Value
                );

                text += "| HP/ATK:星1|";
                text += string.Format(
                    "{0,6} |{1,6} |",
                    model.PerGradeStatuses[0].Hp,
                    model.PerGradeStatuses[0].AttackPower
                );

                text += $"| HP:星2~星{model.PerGradeStatuses.Count}|";
                for (var i = 1; i < model.PerGradeStatuses.Count; i++)
                {
                    text += string.Format(
                        " {0,6} |",
                        model.PerGradeStatuses[i].Hp
                    );
                }

                text += $"| ATK:星2~星{model.PerGradeStatuses.Count}|";
                for (var i = 1; i < model.PerGradeStatuses.Count; i++)
                {
                    text += string.Format(
                        " {0,6} |",
                        model.PerGradeStatuses[i].AttackPower
                    );
                }

                //色変更
                var colorCode = GetDescColorCode(index);
                instanced.Bg.color = colorCode;

                instancedLevelText.text = text + "\n";

                index++;
            }
        }

        public void SetUpSpecialUnitFilter(
            DebugMstUnitSpecialUnitSpecialParamUseCaseModel modelSpecialUnitSpecialAttackStatus)
        {
            //初期化
            _gradeFilterDropdown.options.Clear();
            _rankFilterDropdown.options.Clear();
            _upperLevelFilterDropdown.options.Clear();
            _underLevelFilterDropdown.options.Clear();

            if (!modelSpecialUnitSpecialAttackStatus.IsSpecialRole)
            {
                return;
            }

            // グレードフィルターの設定
            var grades = modelSpecialUnitSpecialAttackStatus.Elements
                .Select(e => e.UnitGrade.Value)
                .Distinct()
                .OrderBy(g => g)
                .ToList();
            _gradeFilterDropdown.options.Add(new Dropdown.OptionData("Grade: All"));
            foreach (var grade in grades)
            {
                _gradeFilterDropdown.options.Add(new Dropdown.OptionData($"Grade: {grade}"));
            }
            _gradeFilterDropdown.value = 0; // 初期値は「All」
            _gradeFilterDropdown.onValueChanged.AddListener((value) =>
            {
                _currentGrade = value == 0 ? UnitGrade.Empty : new UnitGrade(value);
                ApplySpecialUnitFilter(_currentGrade, _currentRank, _currentUnderLevel, _currentUpperLevel);
            });
            // ランクフィルターの設定
            var ranks = modelSpecialUnitSpecialAttackStatus.Elements
                .Select(e => e.UnitRank.Value)
                .Distinct()
                .OrderBy(r => r)
                .ToList();
            _rankFilterDropdown.options.Add(new Dropdown.OptionData("Rank: All"));
            foreach (var rank in ranks)
            {
                _rankFilterDropdown.options.Add(new Dropdown.OptionData($"Rank: {rank}"));
            }
            _rankFilterDropdown.value = 0; // 初期値は「All」
            _rankFilterDropdown.onValueChanged.AddListener((value) =>
            {
                _currentRank = value == 0 ? UnitRank.Empty : new UnitRank(value - 1);
                ApplySpecialUnitFilter(_currentGrade, _currentRank, _currentUnderLevel, _currentUpperLevel);
            });

            // 上限レベルフィルターの設定
            var upperLevels = modelSpecialUnitSpecialAttackStatus.Elements
                .Select(e => e.UnitLevel.Value)
                .Distinct()
                .OrderBy(l => l)
                .ToList();
            _upperLevelFilterDropdown.options.Add(new Dropdown.OptionData("Lv.指定なし"));
            foreach (var level in upperLevels)
            {
                _upperLevelFilterDropdown.options.Add(new Dropdown.OptionData($"Lv. {level}以下"));
            }
            _upperLevelFilterDropdown.value = 0; // 初期値は「Lv.指定なし」
            _upperLevelFilterDropdown.onValueChanged.AddListener((value) =>
            {
                _currentUpperLevel = value == 0 ? UnitLevel.Empty : new UnitLevel(value);
                ApplySpecialUnitFilter(_currentGrade, _currentRank, _currentUnderLevel, _currentUpperLevel);
            });

            // 下限レベルフィルターの設定
            var underLevels = modelSpecialUnitSpecialAttackStatus.Elements
                .Select(e => e.UnitLevel.Value)
                .Distinct()
                .OrderBy(l => l)
                .ToList();
            _underLevelFilterDropdown.options.Add(new Dropdown.OptionData("Lv.指定なし"));
            foreach (var level in underLevels)
            {
                _underLevelFilterDropdown.options.Add(new Dropdown.OptionData($"Lv. {level}以上"));
            }
            _underLevelFilterDropdown.value = 0; // 初期値は「Lv.指定なし」
            _underLevelFilterDropdown.onValueChanged.AddListener((value) =>
            {
                _currentUnderLevel = value == 0 ? UnitLevel.Empty : new UnitLevel(value);
                ApplySpecialUnitFilter(_currentGrade, _currentRank, _currentUnderLevel, _currentUpperLevel);
            });

            // 初期状態でフィルターを適用
            ApplySpecialUnitFilter(_currentGrade, _currentRank, _currentUnderLevel, _currentUpperLevel);
        }

        void ApplySpecialUnitFilter(UnitGrade unitGrade, UnitRank unitRank, UnitLevel unitUnderLevel, UnitLevel unitUpperLevel)
        {
            foreach (var cell in _instancedLevelCells)
            {
                // Empty = Allと表現して扱う
                var isTargetGrade = unitGrade.IsEmpty() || cell.UnitGrade == unitGrade;
                var isTargetRank = unitRank.IsEmpty() || cell.UnitRank == unitRank;
                // レベルのフィルター条件
                // 1. 上限下限の両方指定なし
                // 2. 上限のみ指定
                // 3. 下限のみ指定
                // 4. 上限と下限の両方指定
                var isTargetLevel = (unitUnderLevel.IsEmpty() && unitUpperLevel.IsEmpty()) ||
                                    (unitUnderLevel.IsEmpty() && !unitUpperLevel.IsEmpty() && cell.UnitLevel <= unitUpperLevel) ||
                                    (!unitUnderLevel.IsEmpty() && unitUpperLevel.IsEmpty() && unitUnderLevel <= cell.UnitLevel) ||
                                    (unitUnderLevel <= cell.UnitLevel && cell.UnitLevel <= unitUpperLevel);

                // フィルター条件に合致するセルのみ表示
                if (isTargetGrade && isTargetRank && isTargetLevel)
                {
                    cell.gameObject.SetActive(true);
                }
                else
                {
                    cell.gameObject.SetActive(false);
                }
            }
        }

        public void SetUpSpecialUnitSpecialAttackStatusText(
            DebugMstUnitSpecialUnitSpecialParamUseCaseModel modelSpecialUnitSpecialAttackStatus)
        {
            if (!modelSpecialUnitSpecialAttackStatus.IsSpecialRole)
            {
                var instanced = InstanceDebugMstUnitLevelStatusCell(_specialUnitAttackContentRect);
                instanced.LevelText.text = "このユニットはスペシャルロールではないため、" +
                                           "スペシャルユニットレベル/グレード別必殺ワザ情報は表示されません。";
                return;
            }

            int index = 0;
            foreach (var model in modelSpecialUnitSpecialAttackStatus.Elements)
            {
                var instanced = InstanceDebugMstUnitLevelStatusCell(_specialUnitAttackContentRect);
                instanced.UnitGrade = model.UnitGrade;
                instanced.UnitLevel = model.UnitLevel;
                instanced.UnitRank = model.UnitRank;

                var instancedLevelText = instanced.LevelText;
                instancedLevelText.text = "";

                var text = "| Grade";
                text += string.Format(
                    "{0,5} |",
                    model.UnitGrade.Value
                );

                text += "| Rank";
                text += string.Format(
                    "{0,5} |",
                    model.UnitRank.Value
                );

                text += "| Lv";
                text += string.Format(
                    "{0,5} |",
                    model.UnitLevel.Value
                );

                text += "| ";// 説明文
                text += string.Format(
                    "{0,6} |",
                    model.SpecialAttackInfoDescription.Value
                );

                //色変更
                var colorCode = GetDescColorCode(index);
                instanced.Bg.color = colorCode;

                instancedLevelText.text = text + "\n";

                // List追加処理
                _instancedLevelCells.Add(instanced);

                index++;
            }
        }

        DebugMstUnitLevelStatusCell InstanceDebugMstUnitLevelStatusCell(RectTransform targetRect)
        {
            return Instantiate(_levelCell,targetRect);
        }

        Color GetDescColorCode(int index)
        {
            // 黒 or グレー
            return index % 2 == 0
                ? Color.black
                : new Color32(70,70,70, 255);
        }

        protected override void OnDestroy()
        {
            _instancedLevelCells.Clear();
        }
    }
}
