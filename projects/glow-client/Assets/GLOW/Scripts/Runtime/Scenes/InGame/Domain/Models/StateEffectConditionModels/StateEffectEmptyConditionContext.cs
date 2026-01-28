namespace GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels
{
    public record StateEffectEmptyConditionContext() : IStateEffectConditionContext
    {
        public static StateEffectEmptyConditionContext Instance { get; } = new();
    }
}