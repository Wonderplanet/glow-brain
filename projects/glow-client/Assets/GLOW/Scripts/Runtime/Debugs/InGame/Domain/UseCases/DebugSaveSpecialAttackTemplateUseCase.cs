#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.Translators;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    // デバッグ：必殺技テンプレートを保存するUseCase
    public class DebugSaveSpecialAttackTemplateUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        public void Execute(
            MasterDataId characterId,
            BattleSide battleSide,
            IReadOnlyList<DebugAttackElementData> attackElementDataList)
        {
            // 変換
            var attackElements = DebugAttackElementDataTranslator.ToAttackElements(attackElementDataList);

            // InGameDebugModelに保存
            var key = ZString.Format("{0}_{1}", characterId.Value, battleSide);
            var dic = InGameScene.Debug.SpecialAttackSettings;
            var newSettings = new Dictionary<string, IReadOnlyList<AttackElement>>((IDictionary<string, IReadOnlyList<AttackElement>>)dic)
            {
                [key] = attackElements
            };

            InGameScene.Debug = InGameScene.Debug with
            {
                SpecialAttackSettings = newSettings
            };
        }
    }
}
#endif



