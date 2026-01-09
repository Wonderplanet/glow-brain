using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;

namespace GLOW.Core.Domain.Models
{
    public record MstCharacterModel(
        MasterDataId Id,
        CharacterName Name,
        UnitDescription Description,
        UnitInfoDetail Detail,
        CharacterUnitRoleType RoleType,
        CharacterColor Color,
        CharacterAttackRangeType AttackRangeType,
        UnitLabel UnitLabel,
        MasterDataId FragmentMstItemId,
        MasterDataId MstSeriesId,
        SeriesAssetKey SeriesAssetKey,
        UnitAssetKey AssetKey,
        Rarity Rarity,
        SortOrder SortOrder,
        IsEncyclopediaSpecialAttackPositionRight IsEncyclopediaSpecialAttackPositionRight,
        HasSpecificRankUpFlag HasSpecificRankUp,
        BattlePoint SummonCost,
        TickCount SummonCoolTime,
        HP MinHp,
        HP MaxHp,
        KnockBackCount DamageKnockBackCount,
        UnitMoveSpeed UnitMoveSpeed,
        WellDistance WellDistance,
        AttackPower MinAttackPower,
        AttackPower MaxAttackPower,
        CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus,
        CharacterColorAdvantageDefenseBonus ColorAdvantageDefenseBonus,
        MstAttackModel NormalMstAttackModel,
        IReadOnlyList<MstSpecialAttackModel> SpecialAttacks,
        TickCount SpecialAttackInitialCoolTime,
        TickCount SpecialAttackCoolTime,
        List<MstUnitAbilityModel> MstUnitAbilityModels,
        IReadOnlyList<SpeechBalloonModel> SpeechBalloons)
    {
        public static MstCharacterModel Empty { get; } = new (
            MasterDataId.Empty,
            CharacterName.Empty,
            UnitDescription.Empty,
            UnitInfoDetail.Empty,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            CharacterAttackRangeType.Short,
            UnitLabel.DropR,
            MasterDataId.Empty,
            MasterDataId.Empty,
            SeriesAssetKey.Empty,
            UnitAssetKey.Empty,
            Rarity.R,
            SortOrder.MaxValue,
            IsEncyclopediaSpecialAttackPositionRight.False,
            HasSpecificRankUpFlag.False,
            BattlePoint.Empty,
            TickCount.Empty,
            HP.Empty,
            HP.Empty,
            KnockBackCount.Empty,
            UnitMoveSpeed.Empty,
            WellDistance.Empty,
            AttackPower.Empty,
            AttackPower.Empty,
            CharacterColorAdvantageAttackBonus.Empty,
            CharacterColorAdvantageDefenseBonus.Empty,
            MstAttackModel.Empty,
            new List<MstSpecialAttackModel>(),
            TickCount.Empty,
            TickCount.Empty,
            new List<MstUnitAbilityModel>(),
            new List<SpeechBalloonModel>());

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public bool IsSpecialUnit => RoleType == CharacterUnitRoleType.Special;

        public AttackCountPerMinute AttackCountPerMinute
        {
            get
            {
                var normalAttackFrames = NormalMstAttackModel.AttackData.BaseData.ActionDuration + NormalMstAttackModel.AttackData.BaseData.AttackInterval;
                var attackCountPerMin = Mathf.FloorToInt(TickCount.FromSeconds(60).Value / (float)normalAttackFrames.Value);

                return new AttackCountPerMinute(attackCountPerMin);
            }
        }

        public MstSpecialAttackModel GetSpecialAttack(UnitGrade grade)
        {
            var specialAttack = SpecialAttacks.FirstOrDefault(
                specialAttack => specialAttack.UnitGrade == grade,
                MstSpecialAttackModel.Empty);

            return specialAttack;
        }

        public List<UnitAbility> GetNewlyUnlockedUnitAbilities(UnitRank rank)
        {
            return MstUnitAbilityModels
                .Where(model => !model.IsEmpty && rank == model.UnitAbility.UnlockUnitRank)
                .Select(model => model.UnitAbility)
                .ToList();
        }

        public List<MstUnitAbilityModel> GetUnlockedMstUnitAbilityModels(UnitRank rank)
        {
            return MstUnitAbilityModels
                .Where(model => !model.IsEmpty && rank >= model.UnitAbility.UnlockUnitRank)
                .ToList();
        }
    }
}
