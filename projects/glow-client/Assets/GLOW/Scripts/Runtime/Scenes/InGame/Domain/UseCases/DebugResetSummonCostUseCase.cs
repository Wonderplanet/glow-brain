#if GLOW_INGAME_DEBUG
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    /// <summary>
    /// 召喚コストを本来の値に戻す
    /// </summary>
    public class DebugResetSummonCostUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public void ResetSummonCoolTime()
        {
            InGameScene.Debug = InGameScene.Debug with { IsZeroSummonCost = false };

            InGameScene.DeckUnits = InGameScene.DeckUnits
                .Select(deckUnit =>
                {
                    MstCharacterModel mstCharacter = MstCharacterDataRepository.GetCharacter(deckUnit.CharacterId);
                    return deckUnit with { SummonCost = mstCharacter.SummonCost };
                })
                .ToList();

            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
        }
    }
}
#endif //GLOW_INGAME_DEBUG
