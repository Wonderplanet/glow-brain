using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.ViewModels
{
    public record DeckUnitViewModel(
        IsDeckComponentLock IsLock,
        UserDataId UserUnitId,
        MasterDataId CharacterId,
        CharacterIconAssetPath IconAssetPath,
        CharacterSpecialAttackIconAssetPath SpecialAttackIconAssetPath,
        CharacterUnitRoleType RoleType,
        CharacterColor CharacterColor,
        Rarity Rarity,
        BattlePoint SummonCost,
        TickCount SummonCoolTime,
        TickCount RemainingSummonCoolTime,
        bool IsLackOfBattlePoint,
        bool IsSummoned,
        CanSummonAnySpecialUnitFlag CanSummonAnySpecialUnit,
        TickCount SpecialAttackCoolTime,
        TickCount RemainingSpecialAttackCoolTime,
        bool IsSpecialAttackReady)
    {
        public static DeckUnitViewModel Empty { get; } = new DeckUnitViewModel(
            IsDeckComponentLock.False,
            UserDataId.Empty,
            MasterDataId.Empty,
            CharacterIconAssetPath.Empty,
            CharacterSpecialAttackIconAssetPath.Empty,
            CharacterUnitRoleType.Attack,
            CharacterColor.None,
            Rarity.R,
            BattlePoint.Empty,
            TickCount.Empty,
            TickCount.Empty,
            false,
            false,
            CanSummonAnySpecialUnitFlag.False,
            TickCount.Empty,
            TickCount.Empty,
            false
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty) || UserUnitId.IsEmpty();
        }
    }
}
