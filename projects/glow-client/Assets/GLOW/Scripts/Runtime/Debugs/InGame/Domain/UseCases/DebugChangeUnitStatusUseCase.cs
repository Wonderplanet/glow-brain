#if GLOW_INGAME_DEBUG
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;
using Cysharp.Text;
using GLOW.Debugs.InGame.Domain.Models;
using WPFramework.Modules.Log;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    /// <summary>
    /// デバッグ用：指定ユニットのステータス（HP・攻撃力）を変更するUseCase
    /// </summary>
    public sealed class DebugChangeUnitStatusUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        /// <summary>
        /// 指定したFieldObjectIdのユニットのステータス（最大HP・HP・攻撃力）を変更する
        /// </summary>
        public void ChangeStatus(FieldObjectId fieldObjectId, DebugUnitStatusModel status)
        {
            var unit = InGameScene.CharacterUnits.FirstOrDefault(
                x => x.Id == fieldObjectId,
                CharacterUnitModel.Empty);

            if (unit.IsEmpty())
            {
                ApplicationLog.LogWarning(
                    nameof(DebugChangeUnitStatusUseCase),
                    ZString.Format("ユニットが見つかりません fieldObjectId={0}", fieldObjectId));
                return;
            }

            var newUnit = unit with { MaxHp = status.MaxHp, Hp = status.Hp, AttackPower = status.AttackPower };

            InGameScene.CharacterUnits = InGameScene.CharacterUnits.Replace(unit, newUnit);
        }
    }
}
#endif

