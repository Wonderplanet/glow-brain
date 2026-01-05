#if GLOW_INGAME_DEBUG
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    /// <summary>
    /// デバッグ用：Rushを常時発動可能にするUseCase
    /// </summary>
    public sealed class DebugAlwaysEnableRushUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IBattlePresenter BattlePresenter { get; }

        /// <summary>
        /// Rushを常時発動可能にする
        /// </summary>
        public void EnableAlwaysRush(RushChargeCount chargeCount)
        {
            var rush = InGameScene.RushModel;
            
            // チャージ時間を0にすることで常時発動可能にする
            var newRushModel = rush with
            {
                ChargeCount = chargeCount,
                MaxChargeCount = chargeCount,
                RemainingChargeTime = TickCount.Zero,
                ChargeTime = TickCount.Zero
            };
            
            InGameScene.RushModel = newRushModel;
            
            BattlePresenter.OnUpdateRushGauge(newRushModel);
        }
    }
}
#endif

