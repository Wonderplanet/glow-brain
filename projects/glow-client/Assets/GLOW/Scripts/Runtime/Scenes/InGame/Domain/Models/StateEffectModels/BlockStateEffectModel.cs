using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    /// <summary>
    /// IBlockStateEffectModelを実装したStateEffectの汎用モデル(毒無効化、弱体化無効化など。UnitActionの変更が伴わないもの)
    /// 攻撃力downコマ無効化などコマにいる間だけ有効な効果の無効化はStateEffectModelとして扱っている
    /// LastBlockedEffectSourceIdを保持し、コマ効果の重複ブロック防止を実現する。
    /// ブロック対象（必殺技/コマ効果）はConditionで制御される。
    /// </summary>
    public record BlockStateEffectModel(
        StateEffectId Id,
        StateEffectSourceId SourceId,
        StateEffectType Type,
        EffectiveCount EffectiveCount,
        EffectiveProbability EffectiveProbability,
        TickCount Duration,
        StateEffectParameter Parameter,
        IStateEffectConditionModel Condition,
        bool NeedsDisplay,
        StateEffectSourceId LastBlockedEffectSourceId) : IStateEffectModel, IBlockStateEffectModel
    {
        /// <summary>
        /// BlockStateEffectModelで扱うStateEffectTypeの一覧
        /// </summary>
        public static readonly StateEffectType[] BlockStateEffectTypes =
        {
            StateEffectType.PoisonBlock,
            StateEffectType.WeakeningBlock,
            // 将来追加予定: StateEffectType.BurnBlock
        };

        /// <summary>
        /// 指定されたStateEffectTypeがBlockStateEffectModelで扱うものかを判定
        /// </summary>
        public static bool IsBlockStateEffect(StateEffectType effectType)
        {
            return BlockStateEffectTypes.Contains(effectType);
        }

        bool IStateEffectModel.IsEmpty()
        {
            return false;
        }

        IStateEffectModel IStateEffectModel.WithDecreasedEffectiveCount()
        {
            return this with { EffectiveCount = EffectiveCount - 1 };
        }

        IStateEffectModel IStateEffectModel.WithDecreasedDuration(TickCount tickCount)
        {
            if (Duration.IsEmpty() || Duration.IsInfinity())
            {
                return this;
            }
            return this with { Duration = Duration - tickCount };
        }

        AttackData IStateEffectModel.GenerateAttack()
        {
            return AttackData.Empty;
        }

        StateEffectSourceId IBlockStateEffectModel.GetLastBlockedEffectSourceId()
        {
            return LastBlockedEffectSourceId;
        }

        IStateEffectModel IBlockStateEffectModel.WithUpdatedLastBlockedEffectSourceId(StateEffectSourceId stateEffectSourceId)
        {
            return this with { LastBlockedEffectSourceId = stateEffectSourceId };
        }
    }
}

