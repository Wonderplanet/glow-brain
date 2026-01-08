namespace GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels
{
    public record StateEffectEmptyConditionModel() : IStateEffectConditionModel
    {
        public static StateEffectEmptyConditionModel Instance { get; } = new();

        public bool MeetsCondition(IStateEffectConditionContext context)
        {
            return false;
        }
    }
}