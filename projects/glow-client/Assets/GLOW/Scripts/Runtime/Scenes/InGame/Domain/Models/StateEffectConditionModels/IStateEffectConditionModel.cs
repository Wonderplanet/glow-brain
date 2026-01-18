namespace GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels
{
    public interface IStateEffectConditionModel
    {
        bool MeetsCondition(IStateEffectConditionContext context);
    }
}