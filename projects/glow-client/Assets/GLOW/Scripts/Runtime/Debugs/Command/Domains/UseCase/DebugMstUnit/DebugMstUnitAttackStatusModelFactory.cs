using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Encoder;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugMstUnitAttackStatusUseCaseModel(
        DebugMstUnitStatusAtBase AtBase,
        DebugMstUnitStatusAtPiece AtPiece,
        DebugMstUnitStatusAtBattleBase AtBattleBase,
        DebugMstUnitStatusAtAbility AtAbility,
        DebugMstUnitStatusAtNormalAttack AtNormalAttack,
        DebugMstUnitStatusAtSpecialAttack AtSpecialAttack,
        DebugMstUnitStatusAtSpecialUnitRushUp AtSpecialUnitRushUp
    );

    public record DebugMstUnitStatusAtBase(
        MasterDataId MstCharacterId,
        UnitAssetKey AssetKey,
        CharacterName CharacterName,
        UnitInfoDetail UnitInfoDetail,
        Rarity Rarity,
        UnitLabel UnitLabel,
        CharacterColor CharacterColor,
        CharacterIconAssetPath CharacterIconAssetPath,
        SeriesName SeriesName,
        SeriesPrefixWord SeriesPrefixWord);

    public record DebugMstUnitStatusAtPiece(ItemName ItemName, ItemIconAssetPath ItemIconAssetPath);

    public record DebugMstUnitStatusAtBattleBase(
        CharacterUnitRoleType CharacterUnitRoleType,
        CharacterAttackRangeType CharacterAttackRangeType,
        BattlePoint SummonCost,
        TickCount SummonCoolTime,
        HP MinHp,
        HP MaxHp,
        KnockBackCount KnockBackCount,
        UnitMoveSpeed UnitMoveSpeed,
        WellDistance WellDistance,
        AttackPower MinAttackPower,
        AttackPower MaxAttackPower);

    // ▼特性情報
    //   ∟名称  / 存在しないので、記載しない
    //   ∟効果値 / (ユニット詳細の画面参考にFormat記述する) / MstUnit.MstUnitAbilityId1 ~ 3
    //   ∟開放ランク / UnitAbility.UnlockUnitRank
    //   ∟開放レベル / (ユニット詳細のトースト表示を参考に記述する / UnitEnhanceAbilityModelListFactory.GetUnlockNextLevelCap)
    public record DebugMstUnitStatusAtAbility(IReadOnlyList<DebugMstUnitStatusAtAbilityElement> Elements);

    public record DebugMstUnitStatusAtAbilityElement(
        ObscuredString Description,
        UnitRank UnlockUnitRank,
        UnitLevel UnlockUnitLevel
    );

    // ▼通常攻撃ステータス
    //   ∟オノマトペ / MstAttackElement.hit_effect_id or AttackElement.AttackHitData.OnomatopoeiaAssetKeys
    //   ∟攻撃CDF / MstAttack.NextAttackInterval
    //   DebugMstUnitStatusAtAttackElementStatus
    //   ∟射程 / MstCharacterModel.CharacterAttackRangeType
    //   ∟(新規)特攻属性 / MstAttack.KillerColors
    public record DebugMstUnitStatusAtNormalAttack(
        IReadOnlyList<AttackHitOnomatopoeiaAssetKey> OnomatopoeiaAssetKeys,
        TickCount NormalAttackCoolTime,
        IReadOnlyList<DebugMstUnitStatusAtAttackElementStatus> AtAttackElementStatus,
        CharacterAttackRangeType CharacterAttackRangeType,
        IReadOnlyList<CharacterColor> KillerColors,
        KillerPercentage KillerPercentage)
    {
        public string ToOnomatopoeiaString()
        {
            return string.Join(", ", OnomatopoeiaAssetKeys.Select(x => x.Value));
        }

        public string ToKillerColorsString()
        {
            return string.Join(", ", KillerColors.Select(x => x));
        }

        public string ToKillerPercentageString()
        {
            var isEmpty = KillerPercentage.IsEmpty() || !KillerColors.Any();
            return isEmpty ? "" : $"{KillerPercentage.Value}";
        }
    };

    //   ∟通常攻撃発生F / AttackData.AttackDeley + MstAttackElementData.AttackDelay (連撃の場合はAttackData:Element = 1:nになって少しづつずらしている。ので表示は1,2,3,4とかになりそう)
    //   ∟攻撃ヒットF / AttackData.AttackDeley + MstAttackElementData.AttackDelay
    //      ∟通常攻撃発生Fと一旦同一の内容を記述
    //   ∟攻撃距離  / MatAttackElementのrange_start_type	range_start_parameter	range_end_type	range_end_parameterの組み合わせ
    //      ∟実際：攻撃実行距離(AttackAction遷移) /  MatAttackElementのrange_start_type	range_start_parameter	range_end_type	range_end_parameterの組み合わせ
    //      ∟実際：AttackRangeConverter.ToFieldCoordAttackRange()参考
    //      ∟仕様：「MstAttack.well_distance(会敵) ~ (MstAttackElement.range_end_parameter ~ MstAttackElement.range_start_parameter)(攻撃発生)」の中間(※簡易説明向けにそれっぽく表現しているだけ)
    //      ∟表現(これで表示する)：「MstAttack.well_distance(会敵) ~ MstAttackElement.range_start_parameter)(攻撃発生)」の距離
    //   ∟攻撃範囲(最大対象数) /  MstAttackElement.MaxTargetCount
    //   ∟攻撃倍率  MstAttackElement.PowerParameter
    //   ∟range_start_type
    //   ∟range_start_parameter
    //   ∟range_end_type
    //   ∟range_end_parameter
    //   ∟対象   / AttackElement.AttackTarget
    //   ∟対象属性   / AttackElement.TargetColors
    //   ∟対象ロール  / AttackElement.TargetRoles
    public record DebugMstUnitStatusAtAttackElementStatus(
        TickCount ActionDuration,
        TickCount AttackFrames,
        TickCount AttackHitFrames,
        ObscuredFloat AttackRange,
        FieldObjectCount MaxTargetCount,
        AttackPowerParameter PowerParameter,
        AttackRangePointType RangeStartType,
        AttackRangeParameter RangeStartParameter,
        AttackRangePointType RangeEndType,
        AttackRangeParameter RangeEndParameter,
        AttackTarget AttackTarget,
        IReadOnlyList<CharacterColor> TargetColors,
        IReadOnlyList<CharacterUnitRoleType> TargetRoles,
        StateEffectType StateEffectType,
        AttackHitType HitType,
        AttackHitParameter HitParameter1,
        AttackHitParameter HitParameter2,
        StateEffectParameter StateEffectParameter,
        TickCount EffectiveDuration,
        EffectiveProbability EffectiveProbability
    )
    {
        public float ToAttackFramesToSec()
        {
            return AttackFrames.ToSeconds();
        }

        public float ToAttackHitFramesToSec()
        {
            return AttackHitFrames.ToSeconds();
        }

        public string ToTargetColorsString()
        {
            var allEnumValues = Enum.GetValues(typeof(CharacterColor)).Cast<CharacterColor>();
            var isAll = allEnumValues.All(enumValue => TargetColors.Contains(enumValue));
            if (isAll)
            {
                return "All"; // 全ての色が含まれている場合は "All" と表示
            }

            return string.Join(", ", TargetColors.Select(x => x));
        }

        public string ToTargetRolesString()
        {
            var allEnumValues = Enum.GetValues(typeof(CharacterUnitRoleType)).Cast<CharacterUnitRoleType>();
            var isAll = allEnumValues.All(enumValue => TargetRoles.Contains(enumValue));
            if (isAll)
            {
                return "All"; // 全てのロールが含まれている場合は "All" と表示
            }

            return string.Join(", ", TargetRoles.Select(x => x));
        }

        public string ToHitTypeString()
        {
            //   ∟新規：効果 / MstAttackElement.hit_type
            //      ∟例:HitType.Stun...HitParameter1->TickCount(効果時間), HitParameter2->Probability(発動確率)
            //      ∟例:HitType.Freeze...HitParameter1->TickCount(効果時間), HitParameter2->Probability(発動確率)
            //      ∟例:HitType.Drain...HitParameter1->HealPercentage, HitParameter2->Probability(発動確率)
            //      ∟例:HitType.その他...HitParameter1->未使用, HitParameter2->未使用
            return HitType switch
            {
                AttackHitType.Stun => $"Stun 効果時間={HitParameter1.ToTickCount().Value}, 発動確率={HitParameter2.Value}",
                AttackHitType.Freeze => $"Freeze 効果時間={HitParameter1.ToTickCount().Value}, 発動確率={HitParameter2.Value}",
                AttackHitType.Drain => $"Drain 回復倍率(%)={HitParameter1.ToPercentage().Value}, 発動確率={HitParameter2.Value}",
                _ => $"{HitType}"
            };
        }
    };

    // ▼必殺ワザステータス
    //   ∟初回必殺CDF ... MstUnit.SpecialAttackInitialCoolTime(MstCharacterModel.SpecialAttackInitialCoolTime)
    //   ∟2回目以降必殺CDF...MstUnit.SpecialAttackCoolTime(MstCharacterModel.SpecialAttackCoolTime)
    //   ∟射程
    // ▼必殺ワザグレード別文言
    //   ∟(新規)必殺ワザ名称 / MstSpecialAttackI18n.Name
    //   ∟必殺ワザ文言 / MstAttackI18n.Description
    public record DebugMstUnitStatusAtSpecialAttack(
        SpecialAttackCoolTime SpecialAttackInitialCoolTime,
        SpecialAttackCoolTime SpecialAttackCoolTime,
        CharacterAttackRangeType CharacterAttackRangeType,
        SpecialAttackName SpecialAttackName,
        SpecialAttackInfoDescription SpecialAttackInfoDescription,
        IReadOnlyList<DebugMstUnitStatusAtSpecialAttackElement> Elements);

    // ▼必殺ワザステータス
    //   ∟必殺攻撃_オノマトペ
    //   ∟必殺攻撃F/ AttackData.AttackDeley + MstAttackElementData.AttackDelay (連撃の場合はAttackData:Element = 1:nになって少しづつずらしている。ので表示は1,2,3,4とかになりそう)
    //   ∟必殺攻撃ヒットF
    //   ∟攻撃距離
    //   ∟攻撃範囲
    //   ∟攻撃倍率
    //   ∟range_start_type
    //   ∟range_start_parameter
    //   ∟range_end_type
    //   ∟range_end_parameter
    //   ∟対象
    //   ∟対象属性
    //   ∟対象ロール
    //   ∟(新規)特攻属性  / MstAttack.KillerColors
    //   ∟バフ・デバフ効果型 / MstAttackElement.EffectType(AttackElement.StateEffect.Type)
    //   ∟(新規)必殺ワザ名前 / MstSpecialAttackI18n.Name(MstSpecialAttackModel.Name)
    // ▼必殺ワザグレード別詳細
    //   ∟グレード（1，2，3，4，5） / MstAttack.UnitGrade
    //   ∟新規：効果 / MstAttackElement.hit_type
    //      ∟例:HitType.Stun...HitParameter1->TickCount(効果時間), HitParameter2->Probability(発動確率)
    //      ∟例:HitType.Freeze...HitParameter1->TickCount(効果時間), HitParameter2->Probability(発動確率)
    //      ∟例:HitType.Drain...HitParameter1->HealPercentage, HitParameter2->Probability(発動確率)
    //      ∟例:HitType.その他...HitParameter1->未使用, HitParameter2->未使用
    //   ∟効果値 / MstAttackElement.effect_parameter (AttackElement.StateEffect.EffectParameter)
    //      ∟例：StateEffectType.Burn...固定ダメージ
    //      ∟例：StateEffectType.AttackPowerUp...上昇％
    //      ∟例：StateEffectType.None...無影響
    //   ∟効果時間 / MstAttackElement.effective_duration (AttackElement.StateEffect.Duration)
    //   ∟発動確率 / MstAttackElement.Probability (AttackElement.StateEffect.EffectiveProbability)
    // ▼必殺ワザグレード別文言
    //   ∟必殺ワザ詳細文言 / MstAttackI18n.GradeDescription
    public record DebugMstUnitStatusAtSpecialAttackElement(
        IReadOnlyList<AttackHitOnomatopoeiaAssetKey> OnomatopoeiaAssetKeys,
        IReadOnlyList<DebugMstUnitStatusAtAttackElementStatus> AtAttackElementStatus,
        IReadOnlyList<CharacterColor> KillerColors,
        KillerPercentage KillerPercentage,
        UnitGrade UnitGrade,
        SpecialAttackInfoDescription SpecialAttackInfoDescription,
        SpecialAttackInfoGradeDescription SpecialAttackInfoGradeDescription)
    {
        public string ToOnomatopoeiaString()
        {
            return string.Join(", ", OnomatopoeiaAssetKeys.Select(x => x.Value));
        }

        public string ToKillerColorsString()
        {
            return string.Join(", ", KillerColors.Select(x => x));
        }

        public string ToKillerPercentageString()
        {
            var isEmpty = KillerPercentage.IsEmpty() || !KillerColors.Any();
            return isEmpty ? "" : $"{KillerPercentage.Value}";
        }
    };

    public record DebugMstUnitStatusAtSpecialUnitRushUp(IReadOnlyList<DebugMstUnitStatusAtSpecialUnitRushUpElement> Elements);

    public record DebugMstUnitStatusAtSpecialUnitRushUpElement(UnitRank UnitRank, AttackPower AttackPower);


    public interface IDebugMstUnitAttackStatusModelFactory
    {
        DebugMstUnitAttackStatusUseCaseModel Create(MstCharacterModel mstCharacterModel);
    }

    public class DebugMstUnitAttackStatusModelFactory : IDebugMstUnitAttackStatusModelFactory
    {
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] ISpecialAttackDescriptionEncoder SpecialAttackDescriptionEncoder { get; }
        [Inject] IMstUnitRankCoefficientRepository MstUnitRankCoefficientRepository { get; }
        [Inject] IMstUnitGradeCoefficientRepository MstUnitGradeCoefficientRepository { get; }
        [Inject] IUnitStatusCalculator UnitStatusCalculator { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }

        DebugMstUnitAttackStatusUseCaseModel IDebugMstUnitAttackStatusModelFactory.Create(MstCharacterModel mstCharacterModel)
        {
            return CreateDebugMstUnitStatusUseCaseModel(mstCharacterModel);
        }

        DebugMstUnitAttackStatusUseCaseModel CreateDebugMstUnitStatusUseCaseModel(
            MstCharacterModel mstCharacterModel
        )
        {
            return new DebugMstUnitAttackStatusUseCaseModel(
                CreateDebugMstUnitStatusAtBase(mstCharacterModel),
                CreateDebugMstUnitStatusAtPiece(mstCharacterModel),
                CreateDebugMstUnitStatusAtBattleBase(mstCharacterModel),
                CreateDebugMstUnitStatusAtAbility(mstCharacterModel),
                CreateDebugMstUnitStatusAtNormalAttack(mstCharacterModel),
                CreateDebugMstUnitStatusAtSpecialAttack(mstCharacterModel),
                CreateDebugMstUnitStatusAtSpecialUnitRushUp(mstCharacterModel)
            );
            //▼キャラ
            //   ∟属性情報 mst_units.color / MstCharacterModel.Color
            //   ∟キャラワンポイント文言  MstCharacterModel.UnitInfoDetail
            // ▼特性情報
            //   ∟名称  / 存在しないので、記載しない
            //   ∟効果値 / (ユニット詳細の画面参考にFormat記述する) / MstUnit.MstUnitAbilityId1 ~ 3
            //   ∟開放ランク / UnitAbility.UnlockUnitRank
            //   ∟開放レベル / (ユニット詳細のトースト表示を参考に記述する)
            // ▼通常攻撃ステータス
            //   ∟オノマトペ   MstAttackElement.hit_effect_id or AttackElement.AttackHitData.OnomatopoeiaAssetKeys
            //   ∟通常攻撃発生F / AttackData.AttackDeley + MstAttackElementData.AttackDelay (連撃の場合はAttackData:Element = 1:nになって少しづつずらしている。ので表示は1,2,3,4とかになりそう)
            //   ∟攻撃ヒットF / AttackData.AttackDeley + MstAttackElementData.AttackDelay
            //      ∟通常攻撃発生Fと一旦同一の内容を記述
            //   ∟攻撃CDF / MstAttack.NextAttackInterval
            //   ∟攻撃距離  / MatAttackElementのrange_start_type	range_start_parameter	range_end_type	range_end_parameterの組み合わせ
            //      ∟実際：攻撃実行距離(AttackAction遷移) /  MatAttackElementのrange_start_type	range_start_parameter	range_end_type	range_end_parameterの組み合わせ
            //      ∟実際：AttackRangeConverter.ToFieldCoordAttackRange()参考
            //      ∟仕様：「MstAttack.well_distance(会敵) ~ (MstAttackElement.range_end_parameter ~ MstAttackElement.range_start_parameter)(攻撃発生)」の中間(※簡易説明向けにそれっぽく表現しているだけ)
            //      ∟表現(これで表示する)：「MstAttack.well_distance(会敵) ~ MstAttackElement.range_start_parameter)(攻撃発生)」の距離
            //   ∟射程 / MstCharacterModel.CharacterAttackRangeType
            //   ∟攻撃範囲(最大対象数) /  MstAttackElement.MaxTargetCount
            //   ∟攻撃倍率  MstAttackElement.PowerParameter
            //   ∟range_start_type
            //   ∟range_start_parameter
            //   ∟range_end_type
            //   ∟range_end_parameter
            //   ∟対象   / AttackElement.AttackTarget
            //   ∟対象属性   / AttackElement.TargetColors
            //   ∟対象ロール  / AttackElement.TargetRoles
            //   ∟(新規)特攻属性  / MstAttack.KillerColors
            // ▼必殺ワザステータス
            //   ∟必殺攻撃_オノマトペ
            //   ∟必殺攻撃F
            //   ∟必殺攻撃ヒットF
            //   ∟初回必殺CDF ... MstUnit.SpecialAttackInitialCoolTime(MstCharacterModel.SpecialAttackInitialCoolTime)
            //   ∟2回目以降必殺CDF...MstUnit.SpecialAttackCoolTime(MstCharacterModel.SpecialAttackCoolTime)
            //   ∟攻撃距離
            //   ∟射程
            //   ∟攻撃範囲
            //   ∟攻撃倍率
            //   ∟range_start_type
            //   ∟range_start_parameter
            //   ∟range_end_type
            //   ∟range_end_parameter
            //   ∟対象
            //   ∟対象属性
            //   ∟対象ロール
            //   ∟(新規)特攻属性  / MstAttack.KillerColors
            //   ∟バフ・デバフ効果型 / MstAttackElement.EffectType(AttackElement.StateEffect.Type)
            // ▼必殺ワザグレード別詳細
            //   ∟グレード（1，2，3，4，5） / MstAttack.UnitGrade
            //   ∟新規：効果 / MstAttackElement.hit_type
            //      ∟例:HitType.Stun...HitParameter1->TickCount(効果時間), HitParameter2->Probability(発動確率)
            //      ∟例:HitType.Freeze...HitParameter1->TickCount(効果時間), HitParameter2->Probability(発動確率)
            //      ∟例:HitType.Drain...HitParameter1->HealPercentage, HitParameter2->Probability(発動確率)
            //      ∟例:HitType.その他...HitParameter1->未使用, HitParameter2->未使用
            //   ∟効果値 / MstAttackElement.effect_parameter (AttackElement.StateEffect.EffectParameter)
            //      ∟例：StateEffectType.Burn...固定ダメージ
            //      ∟例：StateEffectType.AttackPowerUp...上昇％
            //      ∟例：StateEffectType.None...無影響
            //   ∟効果時間 / MstAttackElement.effective_duration (AttackElement.StateEffect.Duration)
            //   ∟発動確率 / MstAttackElement.Probability (AttackElement.StateEffect.EffectiveProbability)
            // ▼必殺ワザグレード別文言
            //   ∟(新規)必殺ワザ名称 / MstSpecialAttackI18n.Name
            //   ∟必殺ワザ文言 / MstAttackI18n.Description
            //   ∟必殺ワザ詳細文言 / MstAttackI18n.GradeDescription
        }

        DebugMstUnitStatusAtBase CreateDebugMstUnitStatusAtBase(
            MstCharacterModel mstCharacterModel
        )
        {
            // ここ効率化したい
            var mstSeriesModel = MstSeriesDataRepository.GetMstSeriesModel(mstCharacterModel.MstSeriesId);

            return new DebugMstUnitStatusAtBase(
                mstCharacterModel.Id,
                mstCharacterModel.AssetKey,
                mstCharacterModel.Name,
                mstCharacterModel.Detail,
                mstCharacterModel.Rarity,
                mstCharacterModel.UnitLabel,
                mstCharacterModel.Color,
                CharacterIconAssetPath.FromAssetKey(mstCharacterModel.AssetKey),
                mstSeriesModel.Name,
                mstSeriesModel.PrefixWord
            );
        }

        DebugMstUnitStatusAtPiece CreateDebugMstUnitStatusAtPiece(
            MstCharacterModel mstCharacterModel
        )
        {
            var mstItem = MstItemDataRepository.GetItem(mstCharacterModel.FragmentMstItemId);
            return new DebugMstUnitStatusAtPiece(mstItem.Name, ItemIconAssetPath.FromAssetKey(mstItem.ItemAssetKey));
        }

        DebugMstUnitStatusAtBattleBase CreateDebugMstUnitStatusAtBattleBase(
            MstCharacterModel mstCharacterModel
        )
        {
            return new DebugMstUnitStatusAtBattleBase(
                mstCharacterModel.RoleType,
                mstCharacterModel.AttackRangeType,
                mstCharacterModel.SummonCost,
                mstCharacterModel.SummonCoolTime,
                mstCharacterModel.MinHp,
                mstCharacterModel.MaxHp,
                mstCharacterModel.DamageKnockBackCount,
                mstCharacterModel.UnitMoveSpeed,
                mstCharacterModel.WellDistance,
                mstCharacterModel.MinAttackPower,
                mstCharacterModel.MaxAttackPower
            );
        }

        DebugMstUnitStatusAtAbility CreateDebugMstUnitStatusAtAbility(
            MstCharacterModel mstCharacterModel
        )
        {
            var elements = mstCharacterModel.MstUnitAbilityModels
                .Where(m => !m.IsEmpty)
                .Select(x => new DebugMstUnitStatusAtAbilityElement(
                    x.UnitAbility.Description,
                    x.UnitAbility.UnlockUnitRank,
                    GetUnlockNextLevelCap(mstCharacterModel, x.UnitAbility.UnlockUnitRank)
                )).ToList();

            return new DebugMstUnitStatusAtAbility(elements);


            //UnitEnhanceAbilityModelListFactory.GetUnlockNextLevelCapのコピペ
            UnitLevel GetUnlockNextLevelCap(MstCharacterModel mstCharacterModel, UnitRank rank)
            {
                var unitLevelCap = MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToUnitLevel();
                if (mstCharacterModel.HasSpecificRankUp)
                {
                    var unitSpecificRankUp = MstUnitSpecificRankUpRepository
                        .GetUnitSpecificRankUpList(mstCharacterModel.Id)
                        .MinByAboveLowerLimit(x => x.Rank, rank) ?? MstUnitSpecificRankUpModel.Empty;

                    if (unitSpecificRankUp.IsEmpty())
                    {
                        return unitLevelCap;
                    }

                    return unitSpecificRankUp.RequireLevel;
                }
                else
                {
                    var unitRankUp = MstUnitRankUpRepository
                        .GetUnitRankUpList(mstCharacterModel.UnitLabel)
                        .MinByAboveLowerLimit(x => x.Rank, rank) ?? MstUnitRankUpModel.Empty;

                    if (unitRankUp.IsEmpty())
                    {
                        return unitLevelCap;
                    }

                    return unitRankUp.RequireLevel;
                }
            }
        }

        DebugMstUnitStatusAtNormalAttack CreateDebugMstUnitStatusAtNormalAttack(
            MstCharacterModel mstCharacterModel
        )
        {
            return new DebugMstUnitStatusAtNormalAttack(
                mstCharacterModel.NormalMstAttackModel.AttackData.AttackElements
                    .SelectMany(e => e.AttackHitData.OnomatopoeiaAssetKeys)
                    .Distinct()
                    .ToList(),
                mstCharacterModel.NormalMstAttackModel.AttackData.BaseData.AttackInterval,
                mstCharacterModel.NormalMstAttackModel.AttackData.AttackElements
                    .Select(e =>
                        CreateDebugMstUnitStatusAtAttackElementStatus(
                            mstCharacterModel.WellDistance,
                            mstCharacterModel.NormalMstAttackModel.AttackData,
                            e))
                    .ToList(),
                mstCharacterModel.AttackRangeType,
                mstCharacterModel.NormalMstAttackModel.AttackData.BaseData.KillerColors,
                mstCharacterModel.NormalMstAttackModel.AttackData.BaseData.KillerPercentage
            );
        }

        DebugMstUnitStatusAtAttackElementStatus CreateDebugMstUnitStatusAtAttackElementStatus(
            WellDistance wellDistance,
            AttackData attackData,
            AttackElement attackElement
        )
        {
            return new DebugMstUnitStatusAtAttackElementStatus(
                attackData.BaseData.ActionDuration,
                attackData.AttackDelay + attackElement.AttackDelay,
                attackData.AttackDelay + attackElement.AttackDelay,
                wellDistance.Value - attackElement.AttackRange.StartPointParameter.Value,
                attackElement.MaxTargetCount,
                attackElement.PowerParameter,
                attackElement.AttackRange.StartPointType,
                attackElement.AttackRange.StartPointParameter,
                attackElement.AttackRange.EndPointType,
                attackElement.AttackRange.EndPointParameter,
                attackElement.AttackTarget,
                attackElement.TargetColors,
                attackElement.TargetRoles,
                //以下special表示向け
                attackElement.StateEffect.Type,
                attackElement.AttackHitData.HitType,
                attackElement.AttackHitData.HitParameter1,
                attackElement.AttackHitData.HitParameter2,
                attackElement.StateEffect.Parameter,
                attackElement.StateEffect.Duration,
                attackElement.StateEffect.EffectiveProbability
            );
        }

        DebugMstUnitStatusAtSpecialAttack CreateDebugMstUnitStatusAtSpecialAttack(
            MstCharacterModel mstCharacterModel)
        {
            var specialAttackDesc = SpecialAttackDescriptionEncoder.DescriptionEncode(
                mstCharacterModel.SpecialAttacks.FirstOrDefault()?.Description,
                mstCharacterModel.SpecialAttacks.FirstOrDefault()?.AttackData.AttackElements
            );
            return new DebugMstUnitStatusAtSpecialAttack(
                new SpecialAttackCoolTime(mstCharacterModel.SpecialAttackInitialCoolTime),
                new SpecialAttackCoolTime(mstCharacterModel.SpecialAttackCoolTime),
                mstCharacterModel.AttackRangeType,
                mstCharacterModel.SpecialAttacks.FirstOrDefault()?.Name ?? SpecialAttackName.Empty,
                specialAttackDesc,
                mstCharacterModel.SpecialAttacks
                    .Select(s => CreateDebugMstUnitStatusAtSpecialAttackElement(mstCharacterModel.WellDistance, s))
                    .ToList()
            );
        }

        DebugMstUnitStatusAtSpecialAttackElement CreateDebugMstUnitStatusAtSpecialAttackElement(
            WellDistance wellDistance,
            MstSpecialAttackModel mstSpecialAttackModel)
        {
            var onomatopoeias = mstSpecialAttackModel.AttackData.AttackElements
                .SelectMany(e => e.AttackHitData.OnomatopoeiaAssetKeys)
                .Distinct()
                .ToList();
            var b = mstSpecialAttackModel.AttackData.AttackElements
                .Select(e => CreateDebugMstUnitStatusAtAttackElementStatus(
                    wellDistance,
                    mstSpecialAttackModel.AttackData,
                    e))
                .ToList();
            return new DebugMstUnitStatusAtSpecialAttackElement(
                onomatopoeias,
                b,
                mstSpecialAttackModel.AttackData.BaseData.KillerColors,
                mstSpecialAttackModel.AttackData.BaseData.KillerPercentage,
                mstSpecialAttackModel.UnitGrade,
                SpecialAttackDescriptionEncoder.DescriptionEncode(
                    mstSpecialAttackModel.Description,
                    mstSpecialAttackModel.AttackData.AttackElements),
                mstSpecialAttackModel.GradeDescription
            );
        }

        DebugMstUnitStatusAtSpecialUnitRushUp CreateDebugMstUnitStatusAtSpecialUnitRushUp(MstCharacterModel mstCharacterModel)
        {
            if (mstCharacterModel.RoleType != CharacterUnitRoleType.Special)
            {
                return new DebugMstUnitStatusAtSpecialUnitRushUp(new List<DebugMstUnitStatusAtSpecialUnitRushUpElement>());
            }

            var maxLevel = MstUnitLevelUpRepository.GetUnitLevelUpList(mstCharacterModel.UnitLabel).Max(level => level.Level);
            var ranks = new List<UnitRank>()
            {
                UnitRank.Min
            };

            //Rank取得
            if (mstCharacterModel.HasSpecificRankUp)
            {
                ranks.AddRange(
                    MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstCharacterModel.Id)
                        .Select(m => m.Rank));
                //計算
                var elements = ranks.Select(r =>
                    {
                        var attackPower = UnitStatusCalculator.CalculateAttackPower(
                            UnitLevel.One,
                            mstCharacterModel.MinAttackPower,
                            mstCharacterModel.MaxAttackPower,
                            maxLevel,
                            GetRequiredRankLevel(mstCharacterModel, r),
                            GetStatusExponent(mstCharacterModel),
                            GetUnitGradeCoefficient(mstCharacterModel, UnitGrade.Minimum),
                            GetUnitRankCoefficient(mstCharacterModel, r));
                        return new DebugMstUnitStatusAtSpecialUnitRushUpElement(r, attackPower);
                    })
                    .ToList();

                return new DebugMstUnitStatusAtSpecialUnitRushUp(elements);
            }
            else
            {
                ranks.AddRange(
                    MstUnitRankUpRepository.GetUnitRankUpList(mstCharacterModel.UnitLabel)
                        .Select(m => m.Rank));
                //計算
                var elements = ranks.Select(r =>
                    {
                        var attackPower = UnitStatusCalculator.CalculateAttackPower(
                            UnitLevel.One,
                            mstCharacterModel.MinAttackPower,
                            mstCharacterModel.MaxAttackPower,
                            maxLevel,
                            GetRequiredRankLevel(mstCharacterModel, r),
                            GetStatusExponent(mstCharacterModel),
                            GetUnitGradeCoefficient(mstCharacterModel, UnitGrade.Minimum),
                            GetUnitRankCoefficient(mstCharacterModel, r));
                        return new DebugMstUnitStatusAtSpecialUnitRushUpElement(r, attackPower);
                    })
                    .ToList();

                return new DebugMstUnitStatusAtSpecialUnitRushUp(elements);
            }
        }

        UnitGradeCoefficient GetUnitGradeCoefficient(MstCharacterModel mstUnit, UnitGrade unitGrade)
        {
            var mstUnitGradeCoefs = MstUnitGradeCoefficientRepository.GetUnitGradeCoefficientList();
            var targetMstUnitGradeCoef = mstUnitGradeCoefs.Find(coefficient => coefficient.GradeLevel == unitGrade && coefficient.UnitLabel == mstUnit.UnitLabel);

            var gradeCoefficient = UnitGradeCoefficient.Empty;
            if (targetMstUnitGradeCoef != null && !mstUnit.IsSpecialUnit)
            {
                gradeCoefficient = targetMstUnitGradeCoef.Coefficient;
            }

            return gradeCoefficient;
        }

        UnitLevel GetRequiredRankLevel(MstCharacterModel mstCharacterModel, UnitRank unitRank)
        {
            var requiredRankLevel = UnitLevel.One;
            if (mstCharacterModel.HasSpecificRankUp)
            {
                var mstSpecificRankUp = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstCharacterModel.Id)
                    .FirstOrDefault(mst => mst.Rank == unitRank, MstUnitSpecificRankUpModel.Empty);
                if (!mstSpecificRankUp.IsEmpty()) requiredRankLevel = mstSpecificRankUp.RequireLevel;
            }
            else
            {
                var mstRankUp = MstUnitRankUpRepository.GetUnitRankUpList(mstCharacterModel.UnitLabel)
                    .FirstOrDefault(mst => mst.Rank == unitRank, MstUnitRankUpModel.Empty);
                if (!mstRankUp.IsEmpty()) requiredRankLevel = mstRankUp.RequireLevel;
            }

            return requiredRankLevel;
        }

        UnitStatusExponent GetStatusExponent(MstCharacterModel mstCharacterModel)
        {
            return mstCharacterModel.IsSpecialUnit
                ? MstConfigRepository.GetConfig(MstConfigKey.SpecialUnitStatusExponent).Value.ToUnitStatusExponent()
                : MstConfigRepository.GetConfig(MstConfigKey.UnitStatusExponent).Value.ToUnitStatusExponent();
        }

        UnitRankCoefficient GetUnitRankCoefficient(MstCharacterModel mstCharacterModel, UnitRank unitRank)
        {
            var rankCoefficient = UnitRankCoefficient.Empty;

            var mstUnitRankCoefs = MstUnitRankCoefficientRepository.GetUnitRankCoefficientList();
            var targetMstUnitRankCoefs = mstUnitRankCoefs.Find(coefficient => coefficient.Rank == unitRank);

            if (targetMstUnitRankCoefs != null)
            {
                rankCoefficient = mstCharacterModel.IsSpecialUnit
                    ? targetMstUnitRankCoefs.SpecialUnitCoefficient
                    : targetMstUnitRankCoefs.Coefficient;
            }

            return rankCoefficient;
        }
    }
}
