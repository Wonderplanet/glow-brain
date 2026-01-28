using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.ManualGenerated;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public static class CharacterDataTranslator
    {
        public static MstCharacterModel ToCharacterModel(
            MstUnitData mstCharacterData,
            MstUnitI18nData mstCharacterI18nData,
            MstSeriesData mstSeriesData,
            List<MstAbilityDataModel> mstAbilityDataList,
            MstUnitRoleBonusData mstUnitRoleBonusData,
            MstAttackModel normalAttack,
            IReadOnlyList<MstSpecialAttackModel> specialAttacks,
            IReadOnlyList<MstSpeechBalloonI18nData> speechBalloonI18nDataList)
        {
            // キャラ特性
            var abilities = mstAbilityDataList
                .Select(data => MstUnitAbilityModelTranslator.Translate(
                    data.UnitAbility,
                    data.Ability,
                    data.AbilityI18n,
                    data.UnlockUnitRank))
                .ToList();

            var speechBalloons = speechBalloonI18nDataList
                .Select(data => new SpeechBalloonModel(
                    data.ConditionType,
                    new SpeechBalloonText(
                        data.BalloonType,
                        data.Side,
                        new SpeechBalloonAnimationTime(data.Duration),
                        data.Text)))
                .ToList();

            return new MstCharacterModel(
                new MasterDataId(mstCharacterData.Id),
                new CharacterName(mstCharacterI18nData.Name),
                new UnitDescription(mstCharacterI18nData.Description),
                new UnitInfoDetail(mstCharacterI18nData.Detail),
                mstCharacterData.RoleType,
                mstCharacterData.Color,
                mstCharacterData.AttackRangeType,
                mstCharacterData.UnitLabel,
                new MasterDataId(mstCharacterData.FragmentMstItemId),
                new MasterDataId(mstCharacterData.MstSeriesId),
                new SeriesAssetKey(mstSeriesData.AssetKey),
                new UnitAssetKey(mstCharacterData.AssetKey),
                mstCharacterData.Rarity,
                new SortOrder(mstCharacterData.SortOrder),
                new IsEncyclopediaSpecialAttackPositionRight(mstCharacterData.IsEncyclopediaSpecialAttackPositionRight),
                new HasSpecificRankUpFlag(mstCharacterData.HasSpecificRankUp),
                new BattlePoint(mstCharacterData.SummonCost),
                new TickCount(mstCharacterData.SummonCoolTime),
                new HP(mstCharacterData.MinHp),
                new HP(mstCharacterData.MaxHp),
                new KnockBackCount(mstCharacterData.DamageKnockBackCount),
                new UnitMoveSpeed(mstCharacterData.MoveSpeed),
                new WellDistance(mstCharacterData.WellDistance),
                new AttackPower(mstCharacterData.MinAttackPower),
                new AttackPower(mstCharacterData.MaxAttackPower),
                new CharacterColorAdvantageAttackBonus(mstUnitRoleBonusData.ColorAdvantageAttackBonus),
                new CharacterColorAdvantageDefenseBonus(mstUnitRoleBonusData.ColorAdvantageDefenseBonus),
                normalAttack,
                specialAttacks,
                new TickCount(mstCharacterData.SpecialAttackInitialCoolTime),
                new TickCount(mstCharacterData.SpecialAttackCoolTime),
                abilities,
                speechBalloons);
        }
    }
}
