using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// キャラや拠点にかかる効果
    /// </summary>
    /// <param name="Type"></param>
    /// <param name="EffectiveCount"></param>
    /// <param name="Duration"></param>
    public record StateEffect(
        StateEffectType Type,
        EffectiveCount EffectiveCount,
        EffectiveProbability EffectiveProbability,
        TickCount Duration,
        StateEffectParameter Parameter,
        StateEffectConditionValue ConditionValue1,
        StateEffectConditionValue ConditionValue2)
    {
        public static StateEffect Empty { get; } = new(
            StateEffectType.None,
            EffectiveCount.Empty,
            EffectiveProbability.Empty,
            TickCount.Empty,
            StateEffectParameter.Empty,
            StateEffectConditionValue.Empty,
            StateEffectConditionValue.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
