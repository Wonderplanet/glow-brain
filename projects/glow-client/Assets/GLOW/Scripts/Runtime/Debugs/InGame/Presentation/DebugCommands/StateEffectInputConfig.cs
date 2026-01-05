#if GLOW_INGAME_DEBUG
namespace GLOW.Debugs.InGame.Presentation.DebugCommands
{
    public record StateEffectInputConfig(
        string EffectTypeLabel = "状態変化",
        string ParameterLabel = "パラメータ",
        string ParameterDefault = "0",
        string ConditionValue1Label = "条件値1",
        string ConditionValue1Default = "0",
        string ConditionValue2Label = "条件値2",
        string ConditionValue2Default = "0"
    )
    {
        public static readonly StateEffectInputConfig Empty = new(
            string.Empty,
            string.Empty,
            string.Empty,
            string.Empty,
            string.Empty,
            string.Empty,
            string.Empty
        );
    }
}
#endif
