using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class CancelSpecialUnitSummonUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        public void CancelSpecialUnitSummon()
        {
            // スペシャルキャラ召喚中を解除
            InGameScene.SpecialUnitSummonInfoModel = InGameScene.SpecialUnitSummonInfoModel with
            {
                IsSummonPositionSelecting = SpecialUnitSummonPositionSelectingFlag.False
            };
        }
    }
}