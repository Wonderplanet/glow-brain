using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record DeckUnitModel(
        BattleSide BattleSide,
        IsDeckComponentLock IsDeckComponentLock,
        UserDataId UserUnitId,
        MasterDataId CharacterId,
        UnitAssetKey AssetKey,
        CharacterUnitRoleType RoleType,
        CharacterColor CharacterColor,
        Rarity Rarity,
        UnitGrade Grade,
        BattlePoint SummonCost,
        TickCount SummonCoolTime,
        TickCount RemainingSummonCoolTime,
        bool IsSummoned,
        AttackData SpecialAttackData,
        TickCount SpecialAttackInitialCoolTime,
        TickCount SpecialAttackCoolTime,
        TickCount CurrentSpecialAttackCoolTime,
        TickCount RemainingSpecialAttackCoolTime,
        bool IsSpecialAttackReady)
    {
        public static DeckUnitModel Empty { get; } = new (
            BattleSide.Player,
            IsDeckComponentLock.False,
            UserDataId.Empty,
            MasterDataId.Empty,
            UnitAssetKey.Empty,
            CharacterUnitRoleType.Attack,
            CharacterColor.None,
            Rarity.R,
            UnitGrade.Empty,
            BattlePoint.Empty,
            TickCount.Empty,
            TickCount.Empty,
            false,
            AttackData.Empty,
            TickCount.Empty,
            TickCount.Empty,
            TickCount.Empty,
            TickCount.Empty,
            false
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsEmptyUnit()
        {
            return CharacterId.IsEmpty();
        }

        public bool IsSummonableOnField()
        {
            return !IsEmptyUnit() && RoleType.IsSummonableOnField();
        }
    }
}
