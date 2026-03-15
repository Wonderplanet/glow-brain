namespace GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels
{
    public record StateEffectAlwaysConditionModel() : IStateEffectConditionModel
    {
        public static StateEffectAlwaysConditionModel Instance { get; } = new();
        
        public bool MeetsCondition(IStateEffectConditionContext context)
        {
            return true;
        }
    }
}