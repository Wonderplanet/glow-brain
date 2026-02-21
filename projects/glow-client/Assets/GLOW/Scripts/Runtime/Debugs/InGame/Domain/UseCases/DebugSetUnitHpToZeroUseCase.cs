#if GLOW_INGAME_DEBUG
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    /// <summary>
    /// デバッグ用：指定した陣営のキャラのHPを0にするUseCase
    /// </summary>
    public sealed class DebugSetUnitHpToZeroUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        /// <summary>
        /// 指定した陣営の全キャラのHPを0にする
        /// </summary>
        /// <param name="battleSide">対象の陣営</param>
        public void SetUnitHpToZero(BattleSide battleSide)
        {
            var unitsToUpdate = InGameScene.CharacterUnits
                .Where(unit => unit.BattleSide == battleSide)
                .ToList();

            var updatedUnits = InGameScene.CharacterUnits.ToList();

            foreach (var unit in unitsToUpdate)
            {
                var updatedUnit = unit with { Hp = HP.Zero };
                var index = updatedUnits.FindIndex(x => x.Id == unit.Id);
                if (index >= 0)
                {
                    updatedUnits[index] = updatedUnit;
                }
            }

            InGameScene.CharacterUnits = updatedUnits;
        }
    }
}
#endif