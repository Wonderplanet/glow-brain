using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class TutorialChargeRushGaugeUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }

        public void ChargeRushGauge()
        {
            var rushModel = InGameScene.RushModel;
            var newRushModel = rushModel with
            {
                ChargeCount = rushModel.MaxChargeCount,
            };
            InGameScene.RushModel = newRushModel;
            BattlePresenter.OnUpdateRushGauge(newRushModel);
        }
    }
}