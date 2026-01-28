#if GLOW_INGAME_DEBUG
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;
using Cysharp.Text;
using WPFramework.Modules.Log;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    /// <summary>
    /// デバッグ用：Outpostのダメージ無効化を切り替えるUseCase
    /// </summary>
    public sealed class DebugChangeOutpostDamageInvalidationUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        /// <summary>
        /// Outpostのダメージ無効化フラグを切り替える
        /// </summary>
        /// <param name="battleSide">BattleSide.Player or BattleSide.Enemy</param>
        /// <param name="isDamageInvalidation">OutpostDamageInvalidationFlag</param>
        public void SetOutpostDamageInvalidation(BattleSide battleSide, OutpostDamageInvalidationFlag isDamageInvalidation)
        {
            if (battleSide == BattleSide.Player)
            {
                var outpost = InGameScene.PlayerOutpost;
                if (outpost.IsEmpty())
                {
                    ApplicationLog.LogWarning(
                        nameof(DebugChangeOutpostDamageInvalidationUseCase),
                        "PlayerOutpostが見つかりません");
                    return;
                }
                var newOutpost = outpost with { DamageInvalidationFlag = isDamageInvalidation };
                InGameScene.PlayerOutpost = newOutpost;
                ApplicationLog.Log(
                    nameof(DebugChangeOutpostDamageInvalidationUseCase),
                    ZString.Format(
                        "PlayerOutpostのダメージ無効化を{0}に設定", 
                        isDamageInvalidation == OutpostDamageInvalidationFlag.True ? "有効" : "無効"));
            }
            else if (battleSide == BattleSide.Enemy)
            {
                var outpost = InGameScene.EnemyOutpost;
                if (outpost.IsEmpty())
                {
                    ApplicationLog.LogWarning(
                        nameof(DebugChangeOutpostDamageInvalidationUseCase),
                        "EnemyOutpostが見つかりません");
                    return;
                }
                var newOutpost = outpost with { DamageInvalidationFlag = isDamageInvalidation };
                InGameScene.EnemyOutpost = newOutpost;
                ApplicationLog.Log(
                    nameof(DebugChangeOutpostDamageInvalidationUseCase),
                    ZString.Format(
                        "EnemyOutpostのダメージ無効化を{0}に設定", 
                        isDamageInvalidation == OutpostDamageInvalidationFlag.True ? "有効" : "無効"));
            }
        }
    }
}
#endif

