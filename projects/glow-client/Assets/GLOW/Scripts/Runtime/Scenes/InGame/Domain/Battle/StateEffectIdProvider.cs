using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class StateEffectIdProvider : IStateEffectIdProvider
    {
        StateEffectId _nextStateEffectId = new(1);

        public StateEffectIdProvider()
        {
        }

        public StateEffectIdProvider(StateEffectId initialStateEffectId)
        {
            _nextStateEffectId = initialStateEffectId;
        }

        public StateEffectId GenerateNewId()
        {
            var id = _nextStateEffectId;
            _nextStateEffectId = new StateEffectId(_nextStateEffectId.Value + 1);
            return id;
        }
    }
}
