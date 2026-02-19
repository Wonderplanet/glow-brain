#if GLOW_INGAME_DEBUG
using System.Linq;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    /// <summary>
    /// 必殺技クールタイムを0にする
    /// </summary>
    public class DebugChangeSpecialAttackCoolTimeToZeroUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }

        public void SetSpecialAttackCoolTimeToZero()
        {
            InGameScene.Debug = InGameScene.Debug with { IsZeroSpecialAttackCoolTime = true };

            InGameScene.DeckUnits = InGameScene.DeckUnits
                .Select(deckUnit => deckUnit with
                {
                    SpecialAttackInitialCoolTime = TickCount.Zero,
                    SpecialAttackCoolTime = TickCount.Zero
                })
                .ToList();

            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
        }
    }
}
#endif //GLOW_INGAME_DEBUG
