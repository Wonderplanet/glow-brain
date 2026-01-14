using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class DebugChangeCharacterUnitDamageInvalidationUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        public void ChangeCharacterUnitDamageInvalidation(BattleSide battleSide ,DamageInvalidationFlag isDamageInvalidation)
        {
#if GLOW_INGAME_DEBUG
            if (battleSide == BattleSide.Player)
            {
                InGameScene.Debug = InGameScene.Debug with { IsPlayerUnitDamageInvalidation = isDamageInvalidation };
            }
            else
            {
                InGameScene.Debug = InGameScene.Debug with { IsEnemyUnitDamageInvalidation = isDamageInvalidation };
            }
#endif
        }
    }
}
