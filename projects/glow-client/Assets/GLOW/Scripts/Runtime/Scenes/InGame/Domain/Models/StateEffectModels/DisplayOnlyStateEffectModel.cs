using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    /// <summary>
    /// アイコン表示のみを行い、実際の効果を持たないStateEffectModel
    /// 主に原画効果など、ステータスには既に反映済みでアイコン表示のみ必要な場合に使用
    /// </summary>
    public record DisplayOnlyStateEffectModel(
        StateEffectId Id,
        StateEffectSourceId SourceId,
        StateEffectType Type) : IStateEffectModel
    {
        public EffectiveCount EffectiveCount => EffectiveCount.Infinity;
        public EffectiveProbability EffectiveProbability => EffectiveProbability.Empty;
        public TickCount Duration => TickCount.Infinity;
        public StateEffectParameter Parameter => StateEffectParameter.Empty;
        public IStateEffectConditionModel Condition => StateEffectAlwaysConditionModel.Instance;
        public bool NeedsDisplay => true;

        public bool IsEmpty()
        {
            return false;
        }

        public IStateEffectModel WithDecreasedEffectiveCount()
        {
            return this;
        }

        public IStateEffectModel WithDecreasedDuration(TickCount tickCount)
        {
            return this;
        }

        public AttackData GenerateAttack()
        {
            return AttackData.Empty;
        }

        /// <summary>
        /// 表示専用モデルかどうかを判定
        /// </summary>
        public static bool IsDisplayOnly(IStateEffectModel model)
        {
            return model is DisplayOnlyStateEffectModel;
        }
    }
}

