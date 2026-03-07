#if GLOW_INGAME_DEBUG
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    // デバッグ：保存された必殺技設定をAttackDataに反映するUseCase
    public class DebugApplySpecialAttackTemplateUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        // AttackDataを受け取って上書きしたAttackDataを返す
        public AttackData Execute(
            MasterDataId characterId,
            BattleSide battleSide,
            AttackData originalAttackData)
        {
            var key = ZString.Format("{0}_{1}", characterId.Value, battleSide);

            if (!InGameScene.Debug.SpecialAttackSettings.TryGetValue(key, out var debugElements))
            {
                return originalAttackData;
            }

            if (debugElements == null || debugElements.Count == 0)
            {
                return originalAttackData;
            }

            return originalAttackData with { AttackElements = debugElements };
        }
    }
}
#endif

