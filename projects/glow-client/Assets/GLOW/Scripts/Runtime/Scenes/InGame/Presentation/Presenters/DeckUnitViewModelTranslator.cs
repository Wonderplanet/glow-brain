using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ViewModels;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    public static class DeckUnitViewModelTranslator
    {
        public static DeckUnitViewModel ToViewModel(
            this DeckUnitModel model,
            BattlePoint currentBp,
            CanSummonAnySpecialUnitFlag canSummonAnySpecialUnit)
        {
            if (model.IsEmptyUnit()) return DeckUnitViewModel.Empty with { IsLock = model.IsDeckComponentLock };

            return new DeckUnitViewModel(
                model.IsDeckComponentLock,
                model.UserUnitId,
                model.CharacterId,
                CharacterIconAssetPath.FromAssetKey(model.AssetKey),
                CharacterSpecialAttackIconAssetPath.FromAssetKey(model.AssetKey),
                model.RoleType,
                model.CharacterColor,
                model.Rarity,
                model.SummonCost,
                model.SummonCoolTime,
                model.RemainingSummonCoolTime,
                model.SummonCost > currentBp,
                model.IsSummoned,
                canSummonAnySpecialUnit,
                model.CurrentSpecialAttackCoolTime,
                model.RemainingSpecialAttackCoolTime,
                model.IsSpecialAttackReady
            );
        }
    }
}
